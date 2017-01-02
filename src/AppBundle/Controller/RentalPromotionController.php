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
use AppBundle\Form\TestType;
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
use Winefing\ApiBundle\Entity\RentalPromotion;
use Symfony\Component\Translation\Translator;


class RentalPromotionController extends Controller
{

    /**
     * Create a new Rental Promotion
     * @param Request $request
     * @return ''
     * @Route("/rentals-promotion/new", name="rentals_promotion_new")
     *
     */
    public function newAction(Request $request) {
        var_dump($request->getLocale());
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById(8);
        $domainForm = $this->createForm(TestType::class, $domain);
        $rentalPromotion = new RentalPromotion();
        $options['user'] = $this->getUser()->getId();
        $rentalPromotionForm = $this->createForm(RentalPromotionType::class, $rentalPromotion, $options);
        $rentalPromotionForm->handleRequest($request);
        if($rentalPromotionForm->isSubmitted() && $rentalPromotionForm->isValid()) {
            $body = $request->request->get('rental_promotion');
            $rentals = $request->request->get('rental_promotion')['rentals'];
            $rentalsWithoutConflict = $this->findConflicts($body, $rentals, $request);
            if(!empty($rentalsWithoutConflict)) {
                $rentalPromotion = $this->submit($body);
                $this->submitRentalsPromotion($rentalsWithoutConflict, $rentalPromotion);
            }
        }
        return $this->render('host/rentalPromotion/form.html.twig', array('rentalPromotionForm' => $rentalPromotionForm->createView(), 'domainForm'=>$domainForm->createView()));

    }

    /**
     * Create or update a rental promotion
     * @param $promotion
     * @return Promotion $promotion (with just the id serialized)
     */
    public function submit($promotion) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("jms_serializer");
        $promotion['startDate'] = strtotime($promotion['startDate']);
        $promotion['endDate'] = strtotime($promotion['endDate']);
        if(empty($promotion['id'])) {
            $response = $api->post($this->get('_router')->generate('api_post_rental_promotion'), $promotion);
        } else {
            $response = $api->put($this->get('_router')->generate('api_put_rental_promotion'), $promotion);
        }
        $promotion = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalPromotion', 'json');
        return $promotion;
    }

    /**
     * Add all the rentals to a rentalPromotion
     * @param $rentals
     * @param $rentalPromotion
     */
    public function submitRentalsPromotion($rentals, $rentalPromotion) {
        $api = $this->container->get('winefing.api_controller');
        $body['rentalPromotion'] = $rentalPromotion->getId();
        $body['rentals'] = $rentals;
        $api->put($this->get('_router')->generate('api_put_rental_promotion_rental'), $body);
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
        $userId = $this->getUser()->getId();
        $serializer = $this->container->get("jms_serializer");
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_promotions_by_user', ['userId'=>$userId]));
        $rentalPromotions = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\RentalPromotion>', 'json');
        return $this->render('host/rentalPromotion/index.html.twig', array(
            'rentalsPromotions' => $rentalPromotions,
        ));
    }

    /**
     * Allows to find for a rental if the promotion's date are not in conflict with the date of a promotion which already exist.
     * @param $rentalPromotion
     * @param $rentals
     * @return array of rentals without conflicts
     */
    public function findConflicts($rentalPromotion, $rentals, $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("winefing.serializer_controller");
        $rentalsConflictName = array();
        foreach ($rentals as $key=>$rental) {
            $response = $api->get($this->get('_router')->generate('api_get_rental_promotion_rental', array('startDate'=>$rentalPromotion['startDate'],
                'endDate'=>$rentalPromotion['endDate'], 'rentalId'=> $rental)));
            $rentalName = $serializer->decode($response->getBody()->getContents());
            if(!empty($rentalName)) {
                array_push($rentalsConflictName, $rentalName[0]['name']);
                unset($rentals[$key]);
            }
        }
        if(!empty($rentalsConflictName)) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "You can't add this promotion for the following location : " . implode(", ", $rentalsConflictName).'.');
        }
        return $rentals;
    }

}