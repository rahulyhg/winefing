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
use Winefing\ApiBundle\Entity\Company;
use Winefing\ApiBundle\Entity\DayPrice;
use Winefing\ApiBundle\Entity\Invoice;
use Winefing\ApiBundle\Entity\LemonWay;
use Winefing\ApiBundle\Entity\RentalOrder;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;
use FOS\RestBundle\Controller\Annotations\Patch;
use Winefing\ApiBundle\Entity\RentalOrderGift;
use Winefing\ApiBundle\Entity\StatusOrderEnum;


class RentalOrderController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","box"},
     *  description="Return the rental order by user",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     *
     * )
     */
    public function cgetByUserAction($user, $language) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrders = $repository->findWithUser($user);
        foreach($rentalOrders as $rentalOrder) {
            $rentalOrder->setDomainId();
        }
        $json = $serializer->serialize($rentalOrders, 'json', SerializationContext::create()->setGroups(array('id', 'default')));
        return new Response($json);
    }
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
    public function getBeforePostAction($rental, $rentalOrderGift, $start, $end) {
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

        //set the startDate of the journey
        $dt->setTimestamp($start);
        $rentalOrder->setStartDate($dt);

        //set the endDate of the journey
        $dt = new \DateTime();
        $dt->setTimestamp($end);
        $rentalOrder->setEndDate($dt);

        //set the number of day of the journey
        $rentalOrder->setDayNumber($i);
        $rentalOrder->setAveragePrice(round(($total/$i), 2));

        //set the price of the total night : sum of each price per night during the journey period
        $rentalOrder->setAmount($total);

//        $comissionTotal = $clientComission + $hostComission;
//
        //rental gift order
        $rentalGiftOrder = 0.00;
        if($rentalOrderGift or $rentalOrderGift == '1') {
            $rentalGiftOrder = $this->container->getParameter('rental_order_gift_price');
        }

        //set the invoice host
        $hostInvoice = round($total * ($this->container->getParameter('host_comission')/100), 2);
        $rentalOrder->setHostComission($this->container->getParameter('host_comission'));
        $invoiceHost = new Invoice($hostInvoice, $this->getParameter('tax'));
        $rentalOrder->setInvoiceHost($invoiceHost);

        //set the invoice client
        $clientInvoice = $this->container->getParameter('client_comission');
        $invoiceClient = new Invoice($clientInvoice, $this->getParameter('tax'));
        $rentalOrder->setInvoiceClient($invoiceClient);


        //set lemon way information
        $lemonWay = new LemonWay();
        $amountTot = round($total + $clientInvoice + $rentalGiftOrder, 2);
        //the total that the client should normally pay
        $rentalOrder->setTotal($amountTot);
        if($amountTot < 2500) {
            if($amountTot < 250) {
                $rentalOrder->setLeftToPay(0.00);
                $lemonWay->setAmountTot($amountTot);
                $lemonWay->setAmountCom($amountTot);
            } else {
                $lemonWay->setAmountTot(250.00);
                $lemonWay->setAmountCom(250.00);
                //correspond to the rental amount pay by the user
                $valuePayed= 250-$rentalGiftOrder-$clientInvoice;
                $rentalOrder->setLeftToPay(($total-$invoiceHost->getTotalTTC())-$valuePayed);
            }
        } else {
            $rentalOrder->setLeftToPay($amountTot);
        }
        $rentalOrder->setLemonWay($lemonWay);
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
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($id);
        $json = $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    public function getDetailAction($id, $language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($id);
        $rentalOrder->getRental()->setMediaPresentation();
        $rentalOrder->getRental()->getProperty()->setMediaPresentation();
        $rentalOrder->getRental()->getProperty()->getDomain()->setMediaPresentation();
        $json = $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'property', 'domain', 'characteristic', 'dayPrices', 'address')));
        return new Response($json);
    }
    /**
     * @Get("rental-orders")
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrders = $repository->findAll();
        foreach($rentalOrders as $rentalOrder) {
            $rentalOrder->setDomainId();
        }
        return new Response($serializer->serialize($rentalOrders, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = new RentalOrder();

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('rental'));

        $rentalOrder->setRental($rental);
        $rentalOrder->setRentalName($rental->getName());
        $domain = $rental->getProperty()->getDomain();
        $rentalOrder->setDomainName($domain->getName());
        $rentalOrder->setPropertyName($rental->getProperty()->getName());

        // A CHANGER !!! !
        $company = $domain->getUser()->getCompany();
        if($company instanceof Company) {
            $rentalOrder->setHostCompanyName($company->getName());
            $address = clone $company->getAddress();
        } else {
            $address = clone $domain->getAddress();
            $rentalOrder->setHostCompanyName($domain->getName());
        }
        $rentalOrder->setHostCompanyAddress(clone $address);

        $dt = new \DateTime();
        $dt->setTimestamp($request->request->get('startDate'));
        $rentalOrder->setStartDate($dt);
        $dt->setTimestamp($request->request->get('endDate'));
        $rentalOrder->setEndDate($dt);
        $rentalOrder->setAveragePrice($request->request->get('averagePrice'));
        $rentalOrder->setLeftToPay($request->request->get('leftToPay'));
        $rentalOrder->setDayNumber($request->request->get('dayNumber'));
        $rentalOrder->setTotal($request->request->get('total'));
        $rentalOrder->setClientComission($this->getParameter('client_comission'));
        $rentalOrder->setHostComission($this->getParameter('host_comission'));
        $rentalOrder->setAmount($request->request->get('amount'));

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Invoice');
        $rentalOrder->setInvoiceClient($repository->findOneById($request->request->get('invoiceClient')));
        $rentalOrder->setInvoiceHost($repository->findOneById($request->request->get('invoiceHost')));

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:InvoiceInformation');
        $rentalOrder->setInvoiceInformation($repository->findOneById($request->request->get('invoiceInformation')));

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:LemonWay');
        $lemonWay = $repository->findOneById($request->request->get('lemonWay'));
        if($lemonWay instanceof  LemonWay) {
            $rentalOrder->setLemonWay($lemonWay);
        }
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

    /**
     * @param Request $request
     * @Patch("status/rental/order")
     */
    public function patchStatusAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($request->request->get('id'));
        $status = $request->request->get('status');
        $rentalOrder->getInvoiceInformation()->setStatus();
        if($status == StatusOrderEnum::cancel) {
            $rentalOrder->getInvoiceInformation()->setCancelDate(new \DateTime());
        }
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