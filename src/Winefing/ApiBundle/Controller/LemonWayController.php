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
use Winefing\ApiBundle\Entity\Address;
use Winefing\ApiBundle\Entity\LemonWay;
use Winefing\ApiBundle\Entity\StatusOrderEnum;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;


class LemonWayController extends Controller implements ClassResourceInterface
{

    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $lemonWay = new LemonWay();

        $lemonWay->setAmountCom($request->request->get('amountCom'));
        $lemonWay->setAmountTot($request->request->get('amountTot'));
        $lemonWay->setTransactionId($request->request->get('transactionId'));

        $validator = $this->get('validator');
        $errors = $validator->validate($lemonWay);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($lemonWay);
        $em->flush();
        return new Response($serializer->serialize($lemonWay, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
    public function patchStatusAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:LemonWay');
        $lemonWay = $repository->findOneById($request->request->get('id'));
        $lemonWay->setStatus($request->request->get('status'));

        $validator = $this->get('validator');
        $errors = $validator->validate($lemonWay);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($lemonWay);
        $em->flush();
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
    public function patchTransactionId($id, Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:LemonWay');
        $lemonWay = $repository->findOneById($id);
        $lemonWay->setTransactionId($request->request->get('transactionId'));
    }
}