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
use Winefing\ApiBundle\Entity\LanguageEnum;

use JMS\Serializer\SerializationContext;


class WebPageController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toutes les webpages possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPage');
        $webPages = $repository->findAll();

        $repositoryWebPageTr = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPageTr');
        $repositoryLanguage = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');

        foreach ($webPages as $webPage) {
            $webPageFr = $repositoryWebPageTr->findOneByWebPageIdAndLanguageCode($webPage->getId(), LanguageEnum::Français);
            if(!empty($webPageFr)) {
                $webPage->setTitle($webPageFr[0]["title"]);
            } else {
                $webPageEn = $repositoryWebPageTr->findOneByWebPageIdAndLanguageCode($webPage->getId(), LanguageEnum::English);
                if(!empty($webPageEn)) {
                    $webPage->setTitle($webPageEn[0]["title"]);
                }
            }
            $languageId = array();
            foreach($webPage->getWebPageTrs() as $webPageTr) {
                $languageId[] = $webPageTr->getLanguage()->getId();
            }
            $missingLanguages = $repositoryLanguage->findMissingLanguages($languageId);
            $webPage->setMissingLanguages(new ArrayCollection($missingLanguages));
        }
        $json = $serializer->serialize($webPages);
        return new Response($json);
    }

    public function getAction($code, $language)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPage');
        $webPage = $repository->findOneByCode($code);
        $webPage->setTr($language);
        $json = $serializer->serialize($webPage, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * Create or update a webPage from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $webPage = new WebPage();
        $validator = $this->get('validator');
        $errors = $validator->validate($webPage);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($webPage);
        $em->flush();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($webPage, 'json');
        return new Response($json);
    }

    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPage');
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