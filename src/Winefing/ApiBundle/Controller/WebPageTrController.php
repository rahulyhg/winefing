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
use Winefing\ApiBundle\Entity\WebPage;
use Winefing\ApiBundle\Entity\WebPageTr;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class WebPageTrController extends Controller implements ClassResourceInterface
{
    /**
     * Create a webPage from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $webPageTr = new WebPageTr();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get("language"));
        if(empty($language)) {
            throw new HttpException(400, "The language is mandatory");
        }
        $webPageTr->setLanguage($language);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPage');
        $webPage = $repository->findOneById($request->request->get("webPage"));
        if(empty($webPage)) {
            throw new HttpException(400, "The webPageId is mandatory");
        }
        $webPageTr->setWebPage($webPage);
        $webPageTr->setActivated($request->request->get("activated"));
        $webPageTr->setTitle($request->request->get("title"));
        $webPageTr->setContent($request->request->get("content"));
        $validator = $this->get('validator');
        $errors = $validator->validate($webPageTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($webPageTr);
        $em->flush();
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($webPageTr, 'json');
        return new Response($json);
    }

    /**
     * Update a webPage from the submitted data.<br/>
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPageTr');
        $webPageTr = $repository->findOneById($request->request->get("id"));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get("language"));
        if(empty($language)) {
            throw new HttpException(400, "The language is mandatory");
        }
        $webPageTr->setLanguage($language);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPage');
        $webPage = $repository->findOneById($request->request->get("webPage"));
        if(empty($webPage)) {
            throw new HttpException(400, "The webPageId is mandatory");
        }
        $webPageTr->setWebPage($webPage);
        $webPageTr->setActivated($request->request->get("activated"));
        $webPageTr->setTitle($request->request->get("title"));
        $webPageTr->setContent($request->request->get("content"));
        $validator = $this->get('validator');
        $errors = $validator->validate($webPageTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($webPageTr);
        $em->flush();
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($webPageTr, 'json');
        return new Response($json);
    }

    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPageTr');
        $webPageTr = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(count($webPageTr->getWebPage()->getWebPageTrs()) == 1) {
            $em->remove($webPageTr);
            $em->remove($webPageTr->getWebPage());
        } else {
            $em->remove($webPageTr);
        }
        $em->flush();
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($webPageTr, 'json');
        return new Response($json);
    }

    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPageTr');
        $webPageTr = $repository->findOneById($request->request->get("id"));
        $webPageTr->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}