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
use Winefing\ApiBundle\Entity\BoxItemChoice;


class BoxItemChoiceController extends Controller implements ClassResourceInterface
{
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $boxItemChoice = new BoxItemChoice();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItem');
        $boxItem = $repository->findOneById($request->request->get('boxItem'));
        $boxItemChoice->setBoxItem($boxItem);
        $validator = $this->get('validator');
        $errors = $validator->validate($boxItemChoice);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxItemChoice);
        $em->flush();
        return new Response($serializer->serialize($boxItemChoice, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemChoice');
        $boxItemChoice = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($boxItemChoice);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}