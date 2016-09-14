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

class LanguageController extends Controller implements ClassResourceInterface
{
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
            if ((!empty($language->getPicture()) && !empty($uploadedFile))) {
                unlink($this->getParameter('language_directory') . $language->getPicture());
                $language->setPicture(uploadPicture($uploadedFile)->getBody()->getContents()["name"]);
            } elseif(!empty($uploadedFile)) {
                $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
                $uploadedFile->move(
                    $this->getParameter('language_directory'),
                    $fileName
                );
                $language->setPicture($fileName);
            }
            $encoders = array(new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizers, $encoders);
            $json = $serializer->serialize($language, 'json');
            $em->flush();
            return new Response($json);
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
                $encoders = array(new JsonEncoder());
                $normalizers = array(new ObjectNormalizer());
                $serializer = new Serializer($normalizers, $encoders);
                $json = $serializer->serialize($language, 'json');
                $em->merge($language);
                $em->flush();
                return new Response($json);
            } else {
                var_dump("lol");
                //var_dump($paramFetcher->get('picture'));
                var_dump("after");
                return new Response(json_encode(['lol' => 200]));
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
            unlink($this->getParameter('language_directory') . $language->getPicture());
        }
        $em->remove($language);
        $em->flush();
        return new Response(json_encode(['lol' => 'lol']));
    }

}