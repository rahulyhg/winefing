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
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class LanguageController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les languages possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $languages = $repository->findAll();

        $json = $serializer->serialize($languages, 'json');

        return new Response($json);
    }
    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('id'));
        if (!empty($language)) {
            $language->setCode($request->request->get('code'));
            $language->setName($request->request->get('name'));
            $uploadedFile = $request->files->get('picture');
            if (!empty($language->getPicture()) && !empty($uploadedFile)) {
                unlink($this->getParameter('language_directory') . $language->getPicture());
                $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
                $language->setPicture($fileName);
                $uploadedFile->move(
                    $this->getParameter('language_directory'),
                    $fileName
                );
            } elseif(!empty($uploadedFile)) {
                $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
                $language->setPicture($fileName);
                $uploadedFile->move(
                    $this->getParameter('language_directory'),
                    $fileName
                );
            }
            $validator = $this->get('validator');
            $errors = $validator->validate($language);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                throw new HttpException(400, $errorsString);
            } else {
                $encoders = array(new JsonEncoder());
                $normalizers = array(new ObjectNormalizer());
                $serializer = new Serializer($normalizers, $encoders);
                $json = $serializer->serialize($language, 'json');
                $em->flush();
                return new Response(200, "The language is well modified.");
            }
        } else {
            $language = $repository->findOneByCode($request->request->get('code'));
            if (empty($language)) {
                $language = new Language();
                $uploadedFile = $request->files->get('picture');
                if (!empty($uploadedFile)) {
                    $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
                    $uploadedFile->move(
                        $this->getParameter('language_directory'),
                        $fileName
                    );
                    $language->setPicture($fileName);
                }
                $language->setCode($request->request->get('code'));
                $language->setName($request->request->get('name'));
                $validator = $this->get('validator');
                $errors = $validator->validate($language);
                if (count($errors) > 0) {
                    $errorsString = (string) $errors;
                    return new Response(400, $errorsString);
                } else {
                    $encoders = array(new JsonEncoder());
                    $normalizers = array(new ObjectNormalizer());
                    $serializer = new Serializer($normalizers, $encoders);
                    $json = $serializer->serialize($language, 'json');
                    $em->merge($language);
                    $em->flush();
                }
                return new Response(200, "The language is well modified.");
            } else {
                throw new BadRequestHttpException("A language with this code already exist.");
            }
        }
    }
    public function uploadPicture(UploadedFile $uploadedFile){
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $uploadedFile->move(
            $this->getParameter('language_directory'),
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
            if(!unlink($this->getParameter('language_directory') . $language->getPicture())) {
                throw new HttpException("Problem on server to delete the language's picture.");
            }
        }
        $em->remove($language);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

}