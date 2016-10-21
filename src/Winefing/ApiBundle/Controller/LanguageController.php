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
use Winefing\ApiBundle\Entity\Language;
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;


class LanguageController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $languages = $repository->findAll();
        return new Response($serializer->serialize($languages));
    }
    public function cgetPicturePathAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('language_directory'));
        return new Response($serializer->serialize($picturePath));
    }
    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $language = new Language();
        $language->setCode($request->request->get('code'));
        $language->setName($request->request->get('name'));

        $validator = $this->get('validator');
        $errors = $validator->validate($language);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($language);
        $em->flush();
        return new Response($serializer->serialize($language));
    }

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('id'));
        $serializer = $this->container->get('winefing.serializer_controller');
        $language->setCode($request->request->get('code'));
        $language->setName($request->request->get('name'));

        $validator = $this->get('validator');
        $errors = $validator->validate($language);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($language);
        $em->flush();
        return new Response($serializer->serialize($language));
    }
    public function postFileAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $uploadedFile = $request->files->get('picture');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Icon);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('id'));
        if(empty($language)) {
            throw new BadRequestHttpException('The languageId is mandatory');
        }
        if (!empty($language->getPicture()) && !empty($uploadedFile)) {
            unlink($this->getParameter('language_directory_upload') . $language->getPicture());
        }
        $uploadedFile->move(
            $this->getParameter('language_directory_upload'),
            $fileName
        );
        $language->setPicture($fileName);
        $em->persist($language);
        $em->flush();
        return new Response($serializer->serialize($language));
    }
    public function uploadPicture(UploadedFile $uploadedFile){
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $uploadedFile->move(
            $this->getParameter('language_directory_upload'),
            $fileName
        );
        return new Response(["name" => $fileName]);
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($language->getPicture())) {
            if(!unlink($this->getParameter('language_directory_upload') . $language->getPicture())) {
                throw new HttpException("Problem on server to delete the language's picture.");
            }
        }
        $em->remove($language);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

}