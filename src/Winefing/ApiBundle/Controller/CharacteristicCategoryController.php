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
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\MediaFormatEnum;


class CharacteristicCategoryController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toute les formats possible en base
     * @return Response
     */
    public function cgetAction($scopeName)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Scope');
        $scope = $repository->findOneByName($scopeName);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategories = $repository->findByScope($scope);
        $json = $serializer->serialize($characteristicCategories);
        return new Response($json);
    }

    public function getPicturePath() {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('characteristic_category_directory'));
        return new Response($serializer->serialize($picturePath));
    }
    /**
     * Create or update a characteristicCategory from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $characteristicCategory = new CharacteristicCategory();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Scope');
        $scope = $repository->findOneById($request->request->get('scope'));
        $characteristicCategory->setDescription($request->request->get('description'));
        $characteristicCategory->setActivated($request->request->get('activated'));
        $characteristicCategory->setScope($scope);

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristicCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristicCategory);
        $em->flush();
        $json = $serializer->serialize($characteristicCategory);
        return new Response($json);
    }

    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($request->request->get('id'));
        $characteristicCategory->setDescription($request->request->get('description'));
        $characteristicCategory->setActivated($request->request->get('activated'));
        $validator = $this->get('validator');
        $errors = $validator->validate($characteristicCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristicCategory);
        $em->flush();
        $json = $serializer->serialize($characteristicCategory, 'json');
        return new Response($json);
    }

    public function postFileAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCatrgory = $repository->findOneById($request->request->get('id'));

        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $uploadedFile = $request->files->get('picture');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Icon);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }

        if(empty($characteristicCatrgory)) {
            throw new BadRequestHttpException('The CharacteristicCategoryId is mandatory');
        }
        if (!empty($characteristicCatrgory->getPicture()) && !empty($uploadedFile)) {
            unlink($this->getParameter('characteristic_category_directory_upload') . $characteristicCatrgory->getPicture());
        }
        $uploadedFile->move(
            $this->getParameter('characteristic_category_directory_upload'),
            $fileName
        );
        $characteristicCatrgory->setPicture($fileName);
        $em->persist($characteristicCatrgory);
        $em->flush();
        return new Response($serializer->serialize($characteristicCatrgory));
    }
    public function cgetPicturePathAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('characteristic_category_directory'));
        return new Response($serializer->serialize($picturePath));
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($id);
        if(count($characteristicCategory->getCharacteristics()) > 0) {
            throw new BadRequestHttpException("You can't delete this category because some characteristics are present.");
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($characteristicCategory);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($request->request->get("id"));
        $characteristicCategory->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}