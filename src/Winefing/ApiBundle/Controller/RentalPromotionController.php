<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 09/12/2016
 * Time: 17:35
 */

namespace Winefing\ApiBundle\Controller;


use Winefing\ApiBundle\Entity\RentalPromotion;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;

class RentalPromotionController extends Controller implements ClassResourceInterface
{
    public function cgetByUserAction($userId){
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $rentalPromotions = $repository->findByUser($userId);
        return new Response($serializer->serialize($rentalPromotions, 'json', SerializationContext::create()->setGroups(array('default', 'rentals'))));

    }
    public function cgetByRentalAction($rentalId){
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $rentalPromotions = $repository->findCurrentPromotionForRental($rentalId);
        return new Response($serializer->serialize($rentalPromotions, 'json', SerializationContext::create()->setGroups(array('default'))));

    }

    public function postAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rentalPromotion = new RentalPromotion();

        $startDate = new \DateTime();
        $startDate->setTimestamp($request->request->get('startDate'));
        $endDate = new \DateTime();
        $endDate->setTimestamp($request->request->get('endDate'));
        $rentalPromotion->setStartDate($startDate);
        $rentalPromotion->setEndDate($endDate);
        $rentalPromotion->setReduction($request->request->get('reduction'));

        $validator = $this->get('validator');
        $errors = $validator->validate($rentalPromotion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalPromotion);
        $em->flush();
        return new Response($serializer->serialize($rentalPromotion, 'json', SerializationContext::create()->setGroups(array('id'))));
    }

    public function putAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $rentalPromotion = $repository->findOneById($request->request->get('rentalPromotion'));
        $startDate = new \DateTime();
        $startDate->setTimestamp($request->request->get('startDate'));
        $endDate = new \DateTime();
        $endDate->setTimestamp($request->request->get('endDate'));
        $rentalPromotion->setStartDate($startDate);
        $rentalPromotion->setEndDate($endDate);
        $rentalPromotion->setReduction($request->request->get('reduction'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rentalPromotion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalPromotion);
        $em->flush();

    }
    public function putRentalAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $rentalPromotion = $repository->findOneById($request->request->get('rentalPromotion'));
        $rentalPromotion->resetRentals();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        foreach($request->request->get('rentals') as $rental) {
            $rental = $repository->findOneById($rental);
            $rentalPromotion->addRental($rental);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($rentalPromotion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalPromotion);
        $em->flush();
    }
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $rentalPromotion = $repository->findOneById($id);
        $em->remove($rentalPromotion);
        $em->flush();
    }

    public function getRentalAction($startDate, $endDate, $rentalId) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $rentalName = $repository->findConflictForRental($startDate, $endDate, $rentalId);
        return new Response($serializer->serialize($rentalName, 'json'));
    }

}