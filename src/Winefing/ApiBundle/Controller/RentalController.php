<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Winefing\ApiBundle\Entity\Media;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Rental;
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\ScopeEnum;
use FOS\RestBundle\Controller\Annotations\Get;


class RentalController extends Controller implements ClassResourceInterface
{
    /**
     * GET Route annotation.
     * @Get("api/property/{id}")
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $json = $serializer->serialize($rental, 'json');
        return new Response($json);
    }

    public function getMissingCharacteristicsAction($rentalId) {
        $serializer = $this->container->get('winefing.serializer_controller');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($rentalId);

        $ids = array();
        foreach($rental->getCharacteristicValues() as $characteristicValue) {
            $ids[] = $characteristicValue->getCharacteristic()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristics = $repository->findMissingCharacteristics($ids, ScopeEnum::Rental);
        return new Response($serializer->serialize($characteristics));

    }

    public function getMediaPathAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $mediaPath = $webPath->getPath($this->getParameter('rental_directory'));
        return new Response($serializer->serialize($mediaPath));
    }

    public function cgetByUserAction($userId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findByUser($userId);
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($rentals, 'json');
        return new Response($json);
    }
    public function postPictureAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $uploadedFile = $request->files->get('picture');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Image);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('rental'));
        $media = new Media();
        $media->setName($fileName);
        $media->setFormat(MediaFormatEnum::Image);
        $media->setPresentation(false);
        $rental->addMedia($media);
        $uploadedFile->move(
            $this->getParameter('rental_directory_upload'),
            $fileName
        );
        $em->persist($rental);
        $em->flush();
        return new Response($serializer->serialize($rental));
    }
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rental = new Rental();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $rental->setProperty($repository->findOneById($request->request->get('property')));
        $rental->setName($request->request->get('name'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental, 'json');
        return new Response($json);
    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $rental->setProperty($repository->findOneById($request->request->get('property')));
        $rental->setName($request->request->get('name'));
        $rental->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental, 'json');
        return new Response($json);
    }
    public function putAddressAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $rental->setAddress($repository->findOneById($request->request->get('address')));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental);
        return new Response($json);
    }

    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $webPage = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($webPage->getWebPageTrs())) {
            throw new BadRequestHttpException("You can't delete this webPage because some translation are related.");
        } else {
            $em->remove($webPage);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

}