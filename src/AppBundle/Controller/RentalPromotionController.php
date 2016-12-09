<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 09/12/2016
 * Time: 17:35
 */

namespace Winefing\ApiBundle\Controller;
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


class RentalPromotionController extends Controller
{
    /**
     * @Route("/rentals-promotions", name="rentals_promotions")
     *
     */
    public function cgetAction(){
        $userId = 57;
        $serializer = $this->container->get("jms_serializer");
        $api = $this->container->get('winefing.api_controller');
        $response = $api->post($this->get('_router')->generate('api_post_rental_promotion'), $userId);
        $rentalsPromotions = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalPromotuon', 'json');
        return $rentalsPromotions;
    }

    public function newAction() {


    }

    public function editAction() {

    }
    public function deleteAction() {

    }

}