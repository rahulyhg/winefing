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

class RentalPromotionController
{
    public function cgetAction(){

    }

    public function postAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rentalPromotion = new RentalPromotion();
        $rentalPromotion->setEndDate(date_create_from_format('U', $request->request->get('startDate')));
        $rentalPromotion->setStartDate(date_create_from_format('U', $request->request->get('endDate')));
        $rentalPromotion->setReduction($request->request->get('reduction'));

        $validator = $this->get('validator');
        $errors = $validator->validate($rentalPromotion);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalPromotion);
        $em->flush();
        return new Response($serializer->serialize($rentalPromotion, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    public function putAction() {


    }
    public function deleteAction() {

    }

}