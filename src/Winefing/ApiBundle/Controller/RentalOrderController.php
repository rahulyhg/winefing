<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\DayPrice;
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\StatusOrderEnum;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;


class RentalOrderController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get bill for a rental, with all the field calculated.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\RentalOrder",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="rental", "dataType"="date", "required"=true, "description"="rental id",
     *          "name"="start", "dataType"="date", "required"=true, "description"="start date of the reservation. Format timestamp.",
     *          "name"="end", "dataType"="string", "required"=true, "description"="end date of the reservation. Format timestamp."
     *      }
     *     }
     * )
     */
    public function getBeforePostAction($rental, $start, $end, Request $request) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($rental);
        $rentalOrder = new RentalOrder();
        $date = $start;
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $total = 0.0;
        $i = 0;
        while($date < $end){
            $rentalPromotion = $repository->findPromotionByDate($date, $rental->getId());
            if(empty($rentalPromotion) || $rentalPromotion == NULL) {
                $price = (float) $rental->getPrice();
            } else {
                $price =  round($rental->getPrice() * ((100-$rentalPromotion[0]->getReduction())/100), 2);
            }
            $total += $price;
            $dayPrice = new DayPrice();
            $dt = new \DateTime();
            $dt->setTimestamp($date);
            $dayPrice->setDate($dt);
            $dayPrice->setPrice($price);
            $rentalOrder->addDayPrice($dayPrice);
            $date = strtotime('+1 days', $date);
            $i++;
        }
        $dt = new \DateTime();
        $dt->setTimestamp($start);
        $rentalOrder->setStartDate($dt);

        $dt->setTimestamp($end);
        $rentalOrder->setEndDate($dt);

        $rentalOrder->setDayNumber($i);
        $rentalOrder->setAveragePrice(round(($total/$i), 2));

        //price of only the rentals
        $rentalOrder->setAmount($total);

        //amount of the client comission
        $clientComission = $this->container->getParameter('client_comission');
        $rentalOrder->setClientComission($clientComission);

        //price ttc * total pr
        $hostComission = round($rentalOrder->getAmount() * ($this->container->getParameter('host_comission')/100), 2);
        $rentalOrder->setHostComission($hostComission);
        $rentalOrder->setHostComissionPercentage($this->container->getParameter('host_comission'));

        $comissionTotal = $clientComission + $hostComission;

        //rental gift order
        $rentalGiftOrder = 0.00;
//        if($request->query->get('rentalGiftOrder')) {
//            $rentalGiftOrder = $this->container->getParameter('rental_order_gift_price');
//        }
        //set the comission percentage to take during lemon way transaction
        $rentalOrder->setLemonWayComission(round(((($comissionTotal + $rentalGiftOrder)*100)/$total), 2));

        //set the total which have to pay the user
        $rentalOrder->setTotalTTC(round($total + $clientComission + $rentalGiftOrder, 2));

        //set the information HT for the bill (legal information).
        $rentalOrder->setTotalTax(round($total * ($this->container->getParameter('tax')/100), 2));
        $rentalOrder->setTotalHT($rentalOrder->getTotalTTC() - $rentalOrder->getTotalTax());
        $json = $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'dayPrices')));
        return new Response($json);
    }
    /**
     * not working 500 error. why ?
     * @return Response
     * @Get("rental-order/{id}")
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($id);
        $json = $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'user', 'rental', 'property')));
        return new Response($json);
//        return new Response();
    }
    /**
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findAll();
        return new Response($serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = new RentalOrder();

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $rentalOrder->setUser($repository->findOneById($request->request->get('user')));

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('billingAddress'));
        $rentalOrder->setBillingAddress($address);

        // A CHANGER !!! !
        $rentalOrder->setHostCompanyName('test');
        $rentalOrder->setHostCompanyAddress($address);

        $rentalOrder->setBillingName($request->request->get('billingName'));

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('rental'));
        $rentalOrder->setRental($rental);
        $rentalOrder->setRentalName($rental->getName());
        $rentalOrder->setDomainName($rental->getProperty()->getDomain()->getName());
        $rentalOrder->setPropertyName($rental->getProperty()->getName());

        $dt = new \DateTime();
        $dt->setTimestamp($request->request->get('startDate'));
        $rentalOrder->setStartDate($dt);
        $dt->setTimestamp($request->request->get('endDate'));
        $rentalOrder->setEndDate($dt);
        $rentalOrder->setBillDate(new \DateTime());

        $rentalOrder->setAveragePrice($request->request->get('averagePrice'));
        $rentalOrder->setDayNumber($request->request->get('dayNumber'));
        $rentalOrder->setTotalTax($request->request->get('totalTax'));
        $rentalOrder->setTotalHT($request->request->get('totalHT'));
        $rentalOrder->setTotalTTC($request->request->get('totalTTC'));
        $rentalOrder->setClientComission($request->request->get('clientComission'));
        $rentalOrder->setHostComission($request->request->get('hostComission'));
        $rentalOrder->setHostComissionPercentage($request->request->get('hostComissionPercentage'));
        $rentalOrder->setAmount($request->request->get('amount'));

        $rentalOrder->setStatus(StatusOrderEnum::initiate);

        $validator = $this->get('validator');
        $errors = $validator->validate($rentalOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalOrder);
        $em->flush();
        return new Response($serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('id','default', 'rental', 'billingAddress', 'rentalOrderGift'))));
    }
    public function patchStatusAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($request->request->get('id'));
        $rentalOrder->setStatus($request->request->get('status'));

        $validator = $this->get('validator');
        $errors = $validator->validate($rentalOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalOrder);
        $em->flush();
    }
    public function patchLemonWayTransactionIdAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($request->request->get('rentalOrder'));
        $rentalOrder->setLemonWayTransactionId($request->request->get('lemonWayTransactionId'));

        $validator = $this->get('validator');
        $errors = $validator->validate($rentalOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalOrder);
        $em->flush();
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CreditCard');
        $creditCard = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($creditCard);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental"},
     *  description="Return the rental order by user",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     *
     * )
     */
    public function getByUserAction($user) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrders = $repository->findWithUser($user);
        $json = $serializer->serialize($rentalOrders, 'json', SerializationContext::create()->setGroups(array('id', 'default')));
        return new Response($json);
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental", "default", "user"},
     *  description="Return the rental order by user",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     *
     * )
     */
    public function getByDomainAction($domain) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrders = $repository->findWithDomain($domain);
        $json = $serializer->serialize($rentalOrders, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'user', 'rental', 'property')));
        return new Response($json);
    }
}