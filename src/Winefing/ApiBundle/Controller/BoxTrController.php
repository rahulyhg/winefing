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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Winefing\ApiBundle\Entity\Box;
use Winefing\ApiBundle\Entity\BoxTr;
use Winefing\ApiBundle\Entity\Language;
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;


class BoxTrController extends Controller implements ClassResourceInterface
{
    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $boxTr = new BoxTr();
        $boxTr->setName($request->request->get('name'));
        $boxTr->setDescription($request->request->get('description'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('language'));
        $boxTr->setLanguage($language);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($request->request->get('box'));
        $boxTr->setBox($box);
        $validator = $this->get('validator');
        $errors = $validator->validate($boxTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxTr);
        $em->flush();
        return new Response($serializer->serialize($boxTr, 'json'));
    }

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxTr');
        $boxTr = $repository->findOneById($request->request->get('id'));
        $boxTr->setName($request->request->get('name'));
        $boxTr->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($boxTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxTr);
        $em->flush();
    }
}