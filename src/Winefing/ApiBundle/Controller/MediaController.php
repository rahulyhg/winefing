<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Winefing\ApiBundle\Entity\Domain;
use Winefing\ApiBundle\Entity\Media;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\ScopeEnum;
use FOS\RestBundle\Controller\Annotations\Delete;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Patch;


class MediaController extends Controller implements ClassResourceInterface
{
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $uploadedFile = $request->files->get('media');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $media = new Media();
        $media->setName($fileName);
        $media->setPresentation(false);
        $uploadedFile->move(
            $request->request->get('upload_directory'),
            $fileName
        );
        $em->persist($media);
        $em->flush();
        return new Response($serializer->serialize($media, 'json', SerializationContext::create()->setGroups(array('id', 'default'))));
    }
    public function putPropertyAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Media');
        $media = $repository->findOneById($request->request->get('media'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $property = $repository->findOneById($request->request->get('property'));
        $media->addProperty($property);
        $validator = $this->get('validator');
        $errors = $validator->validate($media);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($media);
        $em->flush();
        return new Response();
    }
    public function putDomainAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Media');
        $media = $repository->findOneById($request->request->get('media'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        $media->addDomain($domain);
        $validator = $this->get('validator');
        $errors = $validator->validate($media);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($media);
        $em->flush();
        return new Response();
    }
    public function putRentalAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Media');
        $media = $repository->findOneById($request->request->get('media'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('rental'));
        $media->addRental($rental);
        $validator = $this->get('validator');
        $errors = $validator->validate($media);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($media);
        $em->flush();
        return new Response();
    }
    public function putBoxAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Media');
        $media = $repository->findOneById($request->request->get('media'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($request->request->get('box'));
        $media->addBox($box);
        $validator = $this->get('validator');
        $errors = $validator->validate($media);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($media);
        $em->flush();
    }

    /**
     * Delete a media
     * @param $id
     * @return Response
     * @Delete("api/media/{id}/{directoryUpload}")
     *
     */
    public function deleteAction($id, $directoryUpload)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Media');
        $media = $repository->findOneById($id);
        unlink($this->container->getParameter($directoryUpload). $media->getName());
        $em = $this->getDoctrine()->getManager();
        $em->remove($media);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index", "media" },
     *  description="Set the presentation media of an object (rental property or domain)",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Property",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="mediaId", "dataType"="integer", "required"=true, "description"="media id",
     *          "name"="id", "dataType"="integer", "required"=true, "description"="object (domain, rental or property id)",
     *          "name"="scope", "dataType"="string", "required"=true, "description"="domain, property, rental"
     *      }
     *     }
     * )
     * @Patch("media/{id}/presentation")
     */
    public function patchPresentationByScopeAction($id, Request $request) {
        $em = $this->getDoctrine()->getManager();
        $mediaRepository = $this->getDoctrine()->getRepository('WinefingApiBundle:Media');
        $currentMedia = $mediaRepository->findOneById($id);
        if($request->request->get('presentation') == 'true') {
            switch ($request->request->get('scope')) {
                case ScopeEnum::Domain:
                    $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
                    break;
                case ScopeEnum::Property:
                    $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
                    break;
                case ScopeEnum::Rental:
                    $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
                    break;
            }
            //get the object correspond to the media (rental domain or property)
            $object = $repository->findOneWithMediaId($id);

            //check if the object has already a presentation media.
            foreach ($object->getMedias() as $media) {
                if ($media->isPresentation()) {
                    $media->setPresentation(0);
                    $em->persist($media);
                    $em->flush();
                    break;
                }
            }
        }
        $currentMedia->setPresentation($request->request->get('presentation') == 'true' ? 1 : 0);
        $em->persist($currentMedia);
        $em->flush();
    }

}