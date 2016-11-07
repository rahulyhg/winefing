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
use Winefing\ApiBundle\Entity\Characteristic;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\MediaFormatEnum;


class CharacteristicController extends Controller implements ClassResourceInterface
{

    /**
     * Create or update a characteristicCategory from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $characteristic = new Characteristic();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristic->setChacarteristicCategory($repository->findOneById($request->request->get('characteristicCategory')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $characteristic->setFormat($repository->findOneById($request->request->get('format')));
        $characteristic->setActivated($request->request->get('activated'));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristic);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristic);
        $em->flush();
        $json = $serializer->serialize($characteristic);
        return new Response($json);
    }

    public function putAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $characteristic->setFormat($repository->findOneById($request->request->get('format')));
        $characteristic->setActivated($request->request->get('activated'));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristic);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristic);
        $em->flush();
        $json = $serializer->serialize($characteristic);
        return new Response($json);
    }

    public function getPicturePathAction() {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('characteristic_directory'));
        return new Response($serializer->serialize($picturePath));
    }

    public function postFileAction(Request $request)
    {
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $uploadedFile = $request->files->get('picture');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Icon);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }

        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('id'));

        if(empty($characteristic)) {
            throw new BadRequestHttpException('The CharacteristicId is mandatory');
        }

        if (!empty($characteristic->getPicture()) && !empty($uploadedFile)) {
            unlink($this->getParameter('characteristic_directory_upload') . $characteristic->getPicture());
        }

        $uploadedFile->move(
            $this->getParameter('characteristic_directory_upload'),
            $fileName
        );

        $characteristic->setPicture($fileName);
        $em->persist($characteristic);
        $em->flush();
        return new Response($serializer->serialize($characteristic));
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($id);
        if (!empty($characteristic->getPicture())) {
            if(!unlink($this->getParameter('characteristic_directory_upload') . $characteristic->getPicture())) {
                throw new HttpException("Problem on server to delete the picture.");
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($characteristic);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristicCategory = $repository->findOneById($request->request->get("id"));
        $characteristicCategory->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}