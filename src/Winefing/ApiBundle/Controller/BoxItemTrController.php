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
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\BoxItem;
use Winefing\ApiBundle\Entity\BoxItemTr;


class BoxItemTrController extends Controller implements ClassResourceInterface
{

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $boxItemTr = new BoxItemTr();
        $boxItemTr->setName($request->request->get('name'));
        $boxItemTr->setDescription($request->request->get('description'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('language'));
        $boxItemTr->setLanguage($language);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItem');
        $boxItem = $repository->findOneById($request->request->get('boxItem'));
        $boxItemTr->setBoxItem($boxItem);
        $validator = $this->get('validator');
        $errors = $validator->validate($boxItemTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxItemTr);
        $em->flush();
        return new Response();
    }

    /**
     * Create or update a language from the submitted data.<br/>
     *
     *
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemTr');
        $boxItemTr = $repository->findOneById($request->request->get('id'));
        $boxItemTr->setName($request->request->get('name'));
        $boxItemTr->setDescription($request->request->get('description'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get('language'));
        $boxItemTr->setLanguage($language);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItem');
        $boxItem = $repository->findOneById($request->request->get('boxItem'));
        $boxItemTr->setBoxItem($boxItem);
        $validator = $this->get('validator');
        $errors = $validator->validate($boxItemTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($boxItemTr);
        $em->flush();
        return new Response();
    }

}