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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Winefing\ApiBundle\Entity\Box;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\BoxItem;
use Winefing\ApiBundle\Entity\CreditCard;
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\StatusOrderEnum;


class RentalOrderController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les languages possible en base
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
        $rentalOrder->setClientAddress($repository->findOneById($request->request->get('clientAddress')));

        $rentalOrder->setStartDate($request->request->get('startDate'));
        $rentalOrder->setEndDate($request->request->get('endDate'));
        $rentalOrder->setBillDate(new \DateTime());

        $rentalOrder->setAveragePrice($request->request->get('averagePrice'));
        $rentalOrder->setDayNumber($request->request->get('dayNumber'));
        $rentalOrder->setTotalTax($request->request->get('totalTax'));
        $rentalOrder->setTotalHT($request->request->get('totalHT'));
        $rentalOrder->setTotalTTC($request->request->get('totalTTC'));
        $rentalOrder->setComission($request->request->get('comission'));
        $rentalOrder->set($request->request->get('comission'));

        $rentalOrder->setStatus(StatusOrderEnum::initiate);

        $validator = $this->get('validator');
        $errors = $validator->validate($rentalOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalOrder);
        $em->flush();
        return new Response($serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('id'))));
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
        $rentalOrder = $repository->findOneById($request->request->get('id'));
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
}