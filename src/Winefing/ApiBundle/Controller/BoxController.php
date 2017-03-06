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
use FOS\RestBundle\Controller\Annotations\Get;


class BoxController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function getByLanguageAction($id, $language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($id);
        $box->setTr($language);
        $box->setMediaPresentation();
        return new Response($serializer->serialize($box, 'json', SerializationContext::create()->setGroups(array('default', 'box', 'boxItems', 'boxItemChoices'))));
    }
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function cgetByLanguageAction($language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $boxes = $repository->findAll();
        foreach ($boxes as $box) {
            $box->setTr($language);
            foreach ($box->getBoxItems() as $boxItem) {
                $boxItem->setTr($language);
                foreach($boxItem->getBoxItemChoices() as $boxItemChoice) {
                    $boxItemChoice->setTr($language);
                }
            }
            $box->setMediaPresentation();
        }
        return new Response($serializer->serialize($boxes, 'json', SerializationContext::create()->setGroups(array('default', 'boxItems', 'boxItemChoices'))));
    }

    /**
     * @Get("/box/{id}/language/{language}")
     * @param $language
     * @param $id
     * @return Response
     */
    public function getAction($language, $id)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($id);
        $box->setTr($language);
        foreach ($box->getBoxItems() as $boxItem) {
            $boxItem->setTr($language);
            foreach($boxItem->getBoxItemChoices() as $boxItemChoice) {
                $boxItemChoice->setTr($language);
            }
        }
        $box->setMediaPresentation();
        return new Response($serializer->serialize($box, 'json', SerializationContext::create()->setGroups(array('default', 'medias', 'boxItems', 'boxItemChoices'))));
    }
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $boxes = $repository->findAll();
        foreach ($boxes as $box) {
            $box->setMediaPresentation();
        }
        return new Response($serializer->serialize($boxes, 'json', SerializationContext::create()->setGroups(array('default', 'boxTrs'))));
    }
    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $box = new Box();
        $box->setPrice($request->request->get('price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($box);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($box);
        $em->flush();
        return new Response($serializer->serialize($box, 'json'));
    }

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function putAction(Request $request)
    {
        $serializer = $this->container->get('jms_serializer');
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($request->request->get('id'));
        $box->setPrice($request->request->get('price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($box);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($box);
        $em->flush();
        return new Response($serializer->serialize($box, 'json'));
    }

    public function putBoxTrAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($request->request->get('box'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxTr');
        $box->addBoxTr($repository->findOneById($request->request->get('boxTr')));
        $validator = $this->get('validator');
        $errors = $validator->validate($box);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($box);
        $em->flush();
        return new Response();
    }

    public function deleteAction($id){
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($id);
        $em->remove($box);
        $em->flush();
        return new Response();
    }
    public function cgetBoxListAction($userId, $language) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $serializer = $this->container->get("jms_serializer");
        $user = $repository->findOneById($userId);
        $boxes = $user->getBoxList();
        foreach($boxes as $box) {
            $box->setTr($language);
        }
        return new Response($serializer->serialize($boxes, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

}