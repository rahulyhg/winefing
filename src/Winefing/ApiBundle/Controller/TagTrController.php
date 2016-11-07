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
use Winefing\ApiBundle\Entity\Tag;
use Winefing\ApiBundle\Entity\TagTr;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\LanguageEnum;



class TagTrController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toutes les webpages possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $wineRegions = $repository->findAll();
        return new Response($serializer->serialize($wineRegions, 'json'));
    }

    /**
     * Create or update a webPage from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $tagTr = new TagTr();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($request->request->get('tag'));
        $tagTr->setTag($tag);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $tagTr->setLanguage($repository->findOneById($request->request->get("language")));
        $tagTr->setName(ucfirst($request->request->get("name")));

        $validator = $this->get('validator');
        $errors = $validator->validate($tagTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($tagTr);
        $em->flush();
        $serializer = $this->container->get('winefing.serializer_controller');
        return new Response($serializer->serialize($tagTr));
    }
    /**
     * Create or update a webPage from the submitted data.<br/>
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:TagTr');
        $tagTr = $repository->findOneById($request->request->get("id"));
        $tagTr->setName(ucfirst($request->request->get("name")));

        $validator = $this->get('validator');
        $errors = $validator->validate($tagTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($tagTr);
        $em->flush();
        $serializer = $this->container->get('winefing.serializer_controller');
        return new Response($serializer->serialize($tagTr));
    }
}