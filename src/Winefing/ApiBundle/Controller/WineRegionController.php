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
use Winefing\ApiBundle\Entity\WineRegion;
use Winefing\ApiBundle\Entity\WineRegionTr;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\LanguageEnum;



class WineRegionController extends Controller implements ClassResourceInterface
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
        return new Response($serializer->serialize($wineRegions));
    }

//    public function getAction($id)
//    {
//        $encoders = array(new JsonEncoder());
//        $normalizers = array(new ObjectNormalizer());
//        $serializer = new Serializer($normalizers, $encoders);
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
//        $webPage = $repository->findOneById($id);
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
//        $missingLanguages = $repository->findMissingLanguagesForWineRegion($webPage);
//        $webPage->setMissingLanguages(new ArrayCollection($missingLanguages));
//        $json = $serializer->serialize($webPage, 'json');
//        return new Response($json);
//    }
    /**
 * Create or update a webPage from the submitted data.<br/>
 */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $wineRegion = new WineRegion();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Country');
        $wineRegion->setCountry($repository->findOneById($request->request->get("country")));
        $validator = $this->get('validator');
        $errors = $validator->validate($wineRegion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($wineRegion);
        $em->flush();
        $serializer = $this->container->get('winefing.serializer_controller');
        return new Response($serializer->serialize($wineRegion));
    }

    /**
     * Create or update a webPage from the submitted data.<br/>
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $wineRegion = $repository->findOneById($request->request->get("id"));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Country');
        $wineRegion->setCountry($repository->findOneById($request->request->get("country")));
        $validator = $this->get('validator');
        $errors = $validator->validate($wineRegion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($wineRegion);
        $em->flush();
        $serializer = $this->container->get('winefing.serializer_controller');
        return new Response($serializer->serialize($wineRegion));
    }

    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $webPage = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($webPage);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

}