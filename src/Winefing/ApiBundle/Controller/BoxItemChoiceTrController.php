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
use Winefing\ApiBundle\Entity\BoxItemChoiceTr;
use Winefing\ApiBundle\Entity\BoxItemTr;


class BoxItemChoiceTrController extends Controller implements ClassResourceInterface
{

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $boxItemChoiceTr = new BoxItemChoiceTr();
        $boxItemChoiceTr->setName($request->request->get('name'));
        $boxItemChoiceTr->setDescription($request->request->get('description'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('language'));
        $boxItemChoiceTr->setLanguage($language);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemChoice');
        $boxItemChoice = $repository->findOneById($request->request->get('boxItemChoice'));
        $boxItemChoiceTr->setBoxItemChoice($boxItemChoice);
        $validator = $this->get('validator');
        $errors = $validator->validate($boxItemChoiceTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxItemChoiceTr);
        $em->flush();
        return new Response();
    }

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemTr');
        $boxItemChoiceTr = $repository->findOneById($request->request->get('id'));
        $boxItemChoiceTr->setName($request->request->get('name'));
        $boxItemChoiceTr->setDescription($request->request->get('description'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('language'));
        $boxItemChoiceTr->setLanguage($language);
        $validator = $this->get('validator');
        $errors = $validator->validate($boxItemChoiceTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxItemChoiceTr);
        $em->flush();
        return new Response();
    }

}