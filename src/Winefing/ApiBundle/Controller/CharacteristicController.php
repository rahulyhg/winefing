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
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\ScopeEnum;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class CharacteristicController extends Controller implements ClassResourceInterface
{

    public function cgetAction($scopeName)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristics = $repository->findByScopeName($scopeName);
        $json = $serializer->serialize($characteristics);
        return new Response($json);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $characteristic = new Characteristic();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristic->setChacarteristicCategory($repository->findOneById($request->request->get('characteristicCategory')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $characteristic->setFormat($repository->findOneById($request->request->get('format')));
        $characteristic->setActivated($request->request->get('activated'));
        $characteristic->setCode($request->request->get('code'));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristic);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristic);
        $em->flush();
        $json = $serializer->serialize($characteristic, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }

    public function putAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $characteristic->setFormat($repository->findOneById($request->request->get('format')));
        $characteristic->setActivated($request->request->get('activated'));
        $characteristic->setCode($request->request->get('code'));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristic);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristic);
        $em->flush();
        $json = $serializer->serialize($characteristic, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }

    public function postFileAction(Request $request)
    {
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $uploadedFile = $request->files->get('media');
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
    }
    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristicCategory = $repository->findOneById($request->request->get("id"));
        $characteristicCategory->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
    public function cgetMissingAction($id, $scope) {
        $serializer = $this->container->get('jms_serializer');

        switch ($scope) {
            case(ScopeEnum::Domain) :
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
                break;
            case(ScopeEnum::Property) :
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
                break;
            case(ScopeEnum::Rental) :
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
                break;
        }
        $object = $repository->findOneById($id);

        $ids = array();
        foreach($object->getCharacteristicValues() as $characteristicValue) {
            $ids[] = $characteristicValue->getCharacteristic()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristics = $repository->findMissingCharacteristics($ids, $scope);
        return new Response($serializer->serialize($characteristics, 'json', SerializationContext::create()->setGroups(array('default', 'format', 'trs'))));
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","characteristic", "webPath"},
     *  description="Return the web path for the entity image",
     *  statusCodes={
     *         200="Returned when successful",
     *     }
     *
     * )
     */
    public function getMediaPathAction() {
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('characteristic_directory'));
        return new Response(json_encode($picturePath));
    }
}