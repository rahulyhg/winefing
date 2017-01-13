<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 10/01/2017
 * Time: 13:37
 */

namespace PaiementBundle\Controller;

use AppBundle\Form\AddressType;
use AppBundle\Form\AddressUserType;
use AppBundle\Form\RentalType;
use PaiementBundle\Entity\CreditCard;
use PaiementBundle\Form\CreditCardType;
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
use Winefing\ApiBundle\Entity\Address;
use Winefing\ApiBundle\Entity\CharacteristicrentalValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\RentalOrderGift;
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use AppBundle\Form\RentalOrderGiftType;

class RentalController extends Controller
{
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/rental/gift", name="rental_paiement_gift")
     */
    public function paiementRentalGift() {
        $rentalOrderGift = new RentalOrderGift();
        $rentalOrderGiftForm = $this->createForm(RentalOrderGiftType::class, $rentalOrderGift);
        return $this->render('user/rental/paiement/gift.html.twig', array('rentalOrderGift'=>$rentalOrderGiftForm->createView()));
    }
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/rental", name="rental_paiement")
     */
    public function paiement(Request $request){
        $user = $this->getUser();
        $webPath = $this->container->get('winefing.webPath_controller');
        $picturePath = $this->getParameter('credit_card_directory');
        $rental = $this->getRental($this->get('session')->get('rental'));
        $bill = $this->getPricesRentalAndPeriod($rental->getId(), $this->get('session')->get('startDate'), $this->get('session')->get('endDate'));
        $creditCard = new CreditCard();
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);
        $creditCardForm->handleRequest($request);
        if($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {
            var_dump(str_replace(" ", "", $creditCardForm['cardNumber']->getData()));

            //create address for the bill
            $bill['clientAddress'] = $this->createBillingAddress($this->get('session')->get('address'));


            //create order
            //create day price
            //link order with day price
            if($creditCardForm['save']->getData()) {
                //create the credit card on lemon way
                var_dump($this->registerCard($user, $creditCardForm));

                //save information in winefing data base
            }
            if(!$user->getWallet()) {
                $this->createWallet($user);
            }
        }
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address);
        return $this->render('user/rental/paiement/paiement.html.twig', ['creditCardForm'=>$creditCardForm->createView(),
            'addressForm'=>$addressForm->createView(),'bill'=>$bill, 'picturePath'=>$picturePath]);
    }
    public function moneyInWithCardId($user) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        if(!$user->getWallet()) {
            $error = $lemonWay->addWallet($user);
            if (!empty($error)) {
                return $error;
            }
        }
    }
    public function registerCard($user, $creditCard) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        $error = $lemonWay->registerCard($user, $creditCard);
//        if (!empty($error)) {
//            return $error;
//        }
    }
    public function createWallet($user) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        if(!$user->getWallet()) {
            $error = $lemonWay->addWallet($user);
            if (!empty($error)) {
                return $error;
            }
        }
    }
    /**
     * @param $order
     * @return mixed
     * @Route("/paiement/billing/address/{addressId}", name="rental_paiement_billing_address")
     */
    public function billingAddress($addressId = '', Request $request) {
        $user = $this->getUser();
        $serializer = $this->container->get('jms_serializer');
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_addresses_by_user', array('userId'=> $user->getId())));
        $addresses = new ArrayCollection();
        if($response->getStatusCode() != StatusCodeEnum::empty_response) {
            $addresses = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Address>', 'json');
        }
        $address = new Address();
        $options['labelSubmit'] = 'label.select_address';
        $addressForm = $this->createForm(AddressUserType::class, $address, $options);
        $addressForm->handleRequest($request);
        $body['user'] = $user->getId();

        if($addressForm->isSubmitted() && $addressForm->isValid()) {
            $response = $api->post($this->get('_router')->generate('api_post_address'),  $request->request->get('address_user'));
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
            $body['address'] = $address->getId();
            $api->patch($this->get('_router')->generate('api_patch_address_user'), $body);
            $this->get('session')->set('address', $address->getId());
            return $this->redirectToRoute('rental_paiement');
        }elseif (!empty($addressId)) {
            $this->get('session')->set('address', $addressId);
            return $this->redirectToRoute('rental_paiement');
        }
        return $this->render('user/rental/paiement/address.html.twig', ['addressForm'=>$addressForm->createView(), 'addresses'=> $addresses]);
    }

    public function createBillingAddress($address) {
        $body['id'] = $address;
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->post($this->get('_router')->generate('api_post_address_copy'),  $body);
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        return $address;
    }

    /**
     * For each of the period of location, this function return an array with the date and the price associated.
     * @param $rental
     * @param $start
     * @param $end
     * @return array[date] = $price
     */
    public function getPricesRentalAndPeriod($rental, $start, $end) {
        $serializer = $this->container->get('winefing.serializer_controller');
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_prices_by_date', array('rental'=>$rental, 'start'=>strtotime($start), 'end'=>strtotime($end))));
        $prices = $serializer->decode($response->getBody()->getContents(),'json');
        return $prices;

    }

    /**
     * @param $order
     * @return mixed
     * @Route("/rental/paiement/delete/address/{id}", name="rental_paiement_delete_address")
     */
    public function deleteAddressAction($id){
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('_router')->generate('api_delete_address', array('id'=>$id)));
        return $this->redirectToRoute('rental_paiement_billing_address');
    }
    public function getRental($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental', array('id' => $id)));
        $rental = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');
        return $rental;
    }

}