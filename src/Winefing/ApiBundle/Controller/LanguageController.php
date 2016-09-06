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
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="id")
     * @RequestParam(name="code", nullable=false, strict=true, description="code")
     * @RequestParam(name="name", nullable=false, strict=true, description="name")
     * @RequestParam(name="picture", nullable=true, strict=true, description="picture")
     *
     */
    public function postAction(ParamFetcher $paramFetcher)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneByCode($paramFetcher->get('code'));
        var_dump($language);
        if(empty($language)) {
            $language = new Language();
            $language->setCode($paramFetcher->get('code'));
            $language->setName($paramFetcher->get('name'));
            $language->setPicture($paramFetcher->get('picture'));

            $encoders = array(new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizers, $encoders);
            $json = $serializer->serialize($language, 'json');
            $em->merge($language);
            $em->flush();
            return new Response($json);
        } else {
            return new Response(json_encode(['lol' => 200]));
        }

    }
    /**
     * Edit a language from the submitted data.<br/>
     *
     *
     * @param ParamFetcher $paramFetcher Paramfetcher
     *
     * @RequestParam(name="id", nullable=false, strict=true, description="id")
     * @RequestParam(name="code", nullable=false, strict=true, description="code")
     * @RequestParam(name="name", nullable=false, strict=true, description="name")
     * @RequestParam(name="picture", nullable=true, strict=true, description="picture")
     *
     */
    public function putAction($slug, $id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($id);
        return new Response(json_encode(['lol' => 200]));
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