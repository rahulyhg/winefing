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
use Winefing\ApiBundle\Entity\Format;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class FormatController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les formats possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $formats = $repository->findAll();

        $json = $serializer->serialize($formats, 'json');

        return new Response($json);
    }
    /**
     * Create or update a format from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $format = $repository->findOneByName($request->request->get('name'));
        if (empty($format)) {
            $format = new Format();
            $format->setDescription($request->request->get('description'));
            $format->setName(strtoupper($request->request->get('name')));
            $validator = $this->get('validator');
            $errors = $validator->validate($format);
            if (count($errors) > 0) {
                $errorsString = (string) $errors;
                return new Response(400, $errorsString);
            } else {
                $em->merge($format);
                $em->flush();
            }
        } else {
            throw new BadRequestHttpException("A format with this name already exist.");
        }
        return new Response(json_encode([200, "The format is well created."]));
    }

    /**
     * Edit a format
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $format = $repository->findOneById($request->request->get("id"));
        $format->setDescription($request->request->get('description'));
        $format->setName(strtoupper($request->request->get('name')));
        $validator = $this->get('validator');
        $errors = $validator->validate($format);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        } else {
            $em->flush();
        }
        return new Response(json_encode([200, "The format is well modified."]));
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $format = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($format);
        $em->flush();
        return new Response(200, "success");
    }
}