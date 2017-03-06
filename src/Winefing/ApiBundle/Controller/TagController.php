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
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use Winefing\ApiBundle\Entity\Tag;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;




class TagController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tags = $repository->findAll();
        return new Response($serializer->serialize($tags, 'json', SerializationContext::create()->setGroups(array('default', 'language', 'id', 'trs'))));
    }
    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function cgetArticlesAction($language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tags = $repository->findForArticles();
        foreach($tags as $tag) {
            $tag->setTr($language);
        }
        return new Response($serializer->serialize($tags, 'json', SerializationContext::create()->setGroups(array('default', 'id'))));
    }
    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function cgetDomainsAction($language, Request $request)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tags = $repository->findForDomains($request->query->all());
        foreach($tags as $tag) {
            $tag->setTr($language);
        }
        return new Response($serializer->serialize($tags, 'json', SerializationContext::create()->setGroups(array('default', 'id'))));
    }

    /**
     * Liste de toutes les tags possible en base
     * @return Response
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($id);
        return new Response($serializer->serialize($tag, 'json'));
    }
    /**
     * Create or update a tag from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tag = new Tag();
        $validator = $this->get('validator');
        $errors = $validator->validate($tag);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($tag);
        $em->flush();
        $serializer = $this->container->get('winefing.serializer_controller');
        return new Response($serializer->serialize($tag));
    }

    /**
     * Delete a tag
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($id);
        if (!empty($tag->getPicture())) {
            if (!unlink($this->getParameter('tag_directory_upload') . $tag->getPicture())) {
                throw new HttpException("Problem on server to delete the picture.");
            }
        }
        $em = $this->getDoctrine()->getManager();
        $em->remove($tag);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="New object.",
     *  parameters={
     *     {
     *          "name"="media", "dataType"="file", "required"=true, "description"="user's picture"
     *      },
     *      {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid",
     *         409="Returned when a user already exist with the same email",
     *
     *     }
     * )
     */
    public function postPictureAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $uploadedFile = $request->files->get('media');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Image);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($request->request->get('tag'));
        if(!empty($tag->getPicture())) {
            unlink($this->getParameter('tag_directory_upload') . $tag->getPicture());
        }
        $tag->setPicture($fileName);
        $uploadedFile->move(
            $this->getParameter('tag_directory_upload'),
            $fileName
        );
        $em->persist($tag);
        $em->flush();
    }


}