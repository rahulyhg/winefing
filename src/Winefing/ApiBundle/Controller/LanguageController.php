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
     * Create a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        //var_dump("in");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        //$language = $repository->findOneByCode($request->request->get('code'));
        if(empty($language)) {
            var_dump("before");
            var_dump($request->request->all());
/*            $language = new Language();
            $language->setCode($paramFetcher->get('code'));
            $language->setName($paramFetcher->get('name'));
            //var_dump("lol");
            var_dump($paramFetcher->get('picture'));
            //var_dump("after");
            $language->setPicture($paramFetcher->get('picture'));

            $encoders = array(new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizers, $encoders);
            $json = $serializer->serialize($language, 'json');
            $em->merge($language);
            $em->flush();
            return new Response($json);*/
            return new Response(json_encode($request->request->all()));
        } else {
            var_dump("lol");
            //var_dump($paramFetcher->get('picture'));
            var_dump("after");
            return new Response(json_encode(['lol' => 200]));
        }

    }
/*    public function test($image) {
        $target_dir = "uploads/";
        $target_file = $target_dir . basename($_FILES["fileToUpload"]["name"]);
        $uploadOk = 1;
        $imageFileType = pathinfo($target_file,PATHINFO_EXTENSION);
// Check if image file is a actual image or fake image
        if(isset($_POST["submit"])) {
            $check = getimagesize($_FILES["fileToUpload"]["tmp_name"]);
            if($check !== false) {
                echo "File is an image - " . $check["mime"] . ".";
                $uploadOk = 1;
            } else {
                echo "File is not an image.";
                $uploadOk = 0;
            }
        }
    }*/
    /**
     * Edit a language from the submitted data.<br/>
     *
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="id")
     * @RequestParam(name="code", nullable=false, strict=true, description="code")
     * @RequestParam(name="name", nullable=false, strict=true, description="name")
     * @FileParam(name="picture", nullable=true, strict=true, description="picture", image=true)
     *
     */
    public function putAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($paramFetcher->get('id'));
        if(!empty($language)) {
            $language->setCode($paramFetcher->get('code'));
            $language->setName($paramFetcher->get('name'));
            $language->setPicture($paramFetcher->get('picture'));
            $encoders = array(new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizers, $encoders);
            $json = $serializer->serialize($language, 'json');
            $em->flush();
            return new Response($json);
        } else {
            return new Response(json_encode(['lol' => 200]));
        }
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($language);
        $em->flush();
        return new Response(json_encode(['lol' => 'lol']));
    }

}