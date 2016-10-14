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
use Winefing\ApiBundle\Entity\Country;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class CountryController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les formats possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Country');
        $countries = $repository->findAll();
        $json = $serializer->serialize($countries);
        return new Response($json);
    }
    /**
     * Create or update a country from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $em = $this->getDoctrine()->getManager();
        $country = new Country;
        $country->setCode($request->request->get('code'));
        $country->setName($request->request->get('name'));
        $validator = $this->get('validator');
        $errors = $validator->validate($country);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($country);
        $em->flush();

        return new Response($serializer->serialize($country));
    }

    /**
     * Edit a format
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Country');
        $country = $repository->findOneById($request->request->get("id"));
        $country->setCode($request->request->get('code'));
        $country->setName($request->request->get('name'));
        $validator = $this->get('validator');
        $errors = $validator->validate($country);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->flush();

        return new Response($serializer->serialize($country));
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Country');
        $country = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($country);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}