<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 09/12/2016
 * Time: 17:35
 */

namespace AppBundle\Controller;
use AppBundle\Form\RentalPromotionType;
use AppBundle\Form\RentalType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\File;
use Winefing\ApiBundle\Entity\CharacteristicrentalValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Rental;
use Winefing\ApiBundle\Entity\CharacteristicValue;
use Winefing\ApiBundle\Entity\RentalPromotion;


class RentalPromotionController extends Controller
{

    /**
     * @Route("/rentals-promotion/new", name="rentals_promotion_new")
     *
     */
    public function newAction(Request $request) {
        $serializer = $this->container->get("jms_serializer");
        $api = $this->container->get('winefing.api_controller');
        $rentalPromotion = new RentalPromotion();
        $rentalPromotionForm = $this->createForm(RentalPromotionType::class, $rentalPromotion);
        $rentalPromotionForm->handleRequest($request);
        if($rentalPromotionForm->isSubmitted() && $rentalPromotionForm->isValid()) {
            $startDate = $rentalPromotionForm->get('startDate')->getData()->format('U');
            $endDate = $rentalPromotionForm->get('endDate')->getData()->format('U');
            $reduction = $rentalPromotionForm->get('reduction')->getData();
            $body['startDate'] = $startDate;
            $body['endDate'] = $endDate;
            $body['reduction'] = $reduction;
            $response = $api->post($this->get('_router')->generate('api_post_rental_promotion'));
        }
        return $this->render('host/rentalPromotion/form.html.twig', array('rentalPromotionForm' => $rentalPromotionForm->createView()));

    }
    /**
     * @Route("/rentals-promotion/edit/{id}", name="rentals_promotion_edit")
     *
     */
    public function editAction($id) {

    }
    public function deleteAction() {

    }
    /**
     * @Route("/rentals-promotions", name="rentals_promotions")
     *
     */
    public function cgetAction(){
//        $userId = 57;
//        $serializer = $this->container->get("jms_serializer");
//        $api = $this->container->get('winefing.api_controller');
//        $response = $api->post($this->get('_router')->generate('api_post_rental_promotion'), $userId);
//        $rentalsPromotions = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalPromotion', 'json');
        return $this->render('host/rentalPromotion/index.html.twig', array(
            'rentalsPromotions' => $i = array(),
        ));
    }

}