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
use Winefing\ApiBundle\Entity\BoxOrder;
use Winefing\ApiBundle\Entity\Invoice;
use Winefing\ApiBundle\Entity\LemonWay;
use Winefing\ApiBundle\Entity\StatusOrderEnum;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;


class BoxOrderController extends Controller implements ClassResourceInterface
{
    /**
     * not working 500 error. why ?
     * @return Response
     * @Get("box-order/{id}/language/{language}")
     */
    public function getAction($id, $language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
        $boxOrder = $repository->findOneById($id);
        $boxOrder->getBox()->setTr($language);
        $boxOrder->getBox()->setMediaPresentation();
        foreach($boxOrder->getBoxItemChoices() as $boxItemChoice) {
            $boxItemChoice->setTr($language);
        }
        $json = $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * not working 500 error. why ?
     * @return Response
     */
    public function getNewAction($boxId, $language)
    {
        $serializer = $this->container->get('jms_serializer');

        //get the box
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($boxId);
        $box->setTr($language);
        $box->setMediaPresentation();

        //create the box order
        $boxOrder = new BoxOrder($box);

        //set the invoice
        $invoice = new Invoice($box->getPrice(), $this->getParameter('tax'));
        $boxOrder->setInvoice($invoice);

        //set lemon Way
        $lemonWay = new LemonWay();
        $lemonWay->setAmountTot($box->getPrice());
        $lemonWay->setAmountCom($box->getPrice());
        $boxOrder->setLemonWay($lemonWay);

        return new Response($serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('id', 'default'))));
    }
    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');

        //get the box
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($request->request->get('box'));

        //create the box order
        $boxOrder = new BoxOrder($box);

        //set the invoice information
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:InvoiceInformation');
        $invoiceInformation = $repository->findOneById($request->request->get('invoiceInformation'));
        $boxOrder->setInvoiceInformation($invoiceInformation);

        //set the invoice
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Invoice');
        $invoice = $repository->findOneById($request->request->get('invoice'));
        $boxOrder->setInvoice($invoice);

        //set the lemonWay
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:LemonWay');
        $lemonWay = $repository->findOneById($request->request->get('lemonWay'));
        $boxOrder->setLemonWay($lemonWay);

        //set the boxItemChoices
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemChoice');
        foreach($request->request->get('boxItemChoices') as $boxItemChoice) {
            $boxOrder->addBoxItemChoice($repository->findOneById($boxItemChoice));
        }

        $validator = $this->get('validator');
        $errors = $validator->validate($boxOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxOrder);
        $em->flush();
        return new Response($serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('id', 'default'))));
    }
    public function patchStatusAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
        $boxOrder = $repository->findOneById($request->request->get('id'));
        $boxOrder->setStatus($request->request->get('status'));

        $validator = $this->get('validator');
        $errors = $validator->validate($boxOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxOrder);
        $em->flush();
    }
    public function patchLemonWayTransactionIdAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
        $boxOrder = $repository->findOneById($request->request->get('boxOrder'));
        $boxOrder->setLemonWayTransactionId($request->request->get('lemonWayTransactionId'));

        $validator = $this->get('validator');
        $errors = $validator->validate($boxOrder);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxOrder);
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
    public function cgetAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
        $rentalOrder = $repository->findAll();
        return new Response($serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
        $boxOrders = $repository->findWithUser($user);
        foreach($boxOrders as $boxOrder) {
            $boxOrder->getBox()->setTr($language);
        }
        $json = $serializer->serialize($boxOrders, 'json', SerializationContext::create()->setGroups(array('id', 'default')));
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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
        $boxOrders = $repository->findWithDomain($domain);
        $json = $serializer->serialize($boxOrders, 'json', SerializationContext::create()->setGroups(array('id', 'default', 'user', 'rental', 'property')));
        return new Response($json);
    }
}