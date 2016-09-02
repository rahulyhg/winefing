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

    public function newAction(Request $request)
    {
      $em = $this->getDoctrine()->getManager();
      $language = new Language();
/*      $language.setCode($request->request->get('code'));
      $language.setName($request->request->get('name'));
      $language.setPicture($request->request->get('picture'));*/
      $em->flush();
    } // "new_users"     [GET] /users/new
    public function editAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($id);
    }

    public function getAction($slug)
    {

        var_dump('lol');
    } // "get_user"      [GET] /users/{slug}

}