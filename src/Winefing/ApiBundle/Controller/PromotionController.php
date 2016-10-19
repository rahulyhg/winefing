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
use Winefing\ApiBundle\Entity\Promotion;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class PromotionController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les promotions possibles en base
     * @return Response
     */
    public function cgetAction()
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Promotion');
        $promotions = $repository->findAll();

        $json = $serializer->serialize($promotions, 'json');

        return new Response($json);
    }
    /**
     * Create or update a promotion from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $promotion = new Promotion();
        $promotion->setAmount($request->request->get('amount'));
        $promotion->setFormat($request->request->get('format'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Promotion');
        if(!empty($repository->findOneByCode($request->request->get('code')))) {
            throw new BadRequestHttpException("A promotion with this code already exist.");
        }
        $promotion->setCode($request->request->get('code'));
        $promotion->setMinAmount($request->request->get('minAmount'));
        $promotion->setNumberDisponible($request->request->get('numberDisponible'));

        $startDate = new \DateTime();
        $startDate->setDate($request->request->get('startDate')['year'], $request->request->get('startDate')['month'], $request->request->get('startDate')['day']);
        $promotion->setStartDate($startDate);

        if(!empty($request->request->get('endDate')['year'])){
            $endDate = new \DateTime();
            $endDate->setDate($request->request->get('endDate')['year'], $request->request->get('endDate')['month'], $request->request->get('endDate')['day']);
            if($endDate < $startDate) {
                throw new BadRequestHttpException("The end date is inferior to the start date.");
            } else {
                $promotion->setEndDate($endDate);
            }
        }
        $promotion->setFirstOrder($request->request->get('firstOrder'));
        $promotion->setFreeShipping($request->request->get('freeShipping'));
        $validator = $this->get('validator');
        $errors = $validator->validate($promotion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($promotion);
        $em->flush();
        $json = $serializer->serialize($promotion);
        return new Response($json);
    }

    /**
     * Edit a promotion
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Promotion');
        $promotion = $repository->findOneById($request->request->get('id'));
        $promotion->setAmount($request->request->get('amount'));
        $promotion->setFormat($request->request->get('format'));
        $promotion->setCode($request->request->get('code'));
        $promotion->setMinAmount($request->request->get('minAmount'));
        $promotion->setNumberDisponible($request->request->get('numberDisponible'));

        $startDate = new \DateTime();
        $startDate->setDate($request->request->get('startDate')['year'], $request->request->get('startDate')['month'], $request->request->get('startDate')['day']);
        $promotion->setStartDate($startDate);

        if(!empty($request->request->get('endDate')['year'])){
            $endDate = new \DateTime();
            $endDate->setDate($request->request->get('endDate')['year'], $request->request->get('endDate')['month'], $request->request->get('endDate')['day']);
            if($endDate < $startDate) {
                throw new BadRequestHttpException("The end date is inferior to the start date.");
            } else {
                $promotion->setEndDate($endDate);
            }
        }
        $promotion->setFirstOrder($request->request->get('firstOrder'));
        $promotion->setFreeShipping($request->request->get('freeShipping'));
        $validator = $this->get('validator');
        $errors = $validator->validate($promotion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($promotion);
        $em->flush();
        $json = $serializer->serialize($promotion);
        return new Response($json);
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Promotion');
        $promotion = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($promotion);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}