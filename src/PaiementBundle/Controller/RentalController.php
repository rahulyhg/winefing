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
use JMS\Serializer\SerializationContext;

class RentalController extends Controller
{
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/rental/gift", name="rental_paiement_gift")
     */
    public function paiementRentalGift(Request $request) {
        $rentalOrderGift = new RentalOrderGift();
        $rentalOrderGiftForm = $this->createForm(RentalOrderGiftType::class, $rentalOrderGift);
        $rentalOrderGiftForm->get('signature')->setData($this->getUser()->getFirstName());
        $rentalOrderGiftForm->handleRequest($request);
        if($rentalOrderGiftForm->isSubmitted() && $rentalOrderGiftForm->isSubmitted()) {
            $rentalOrderGift = $rentalOrderGiftForm->getData();
            $this->setRentalOrderGiftRentalOrder($rentalOrderGift);
            return $this->redirectToRoute('rental_paiement');
        }
        return $this->render('user/rental/paiement/gift.html.twig', array('rentalOrderGift'=>$rentalOrderGiftForm->createView()));
    }
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/rental", name="rental_paiement")
     */
    public function paiement(Request $request){
        $user = $this->getUser();
        $serializer = $this->container->get('jms_serializer');
        $rentalOrderSession = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        $rentalOrder = $this->getRentalOrder($rentalOrderSession->getRental()->getId(), $rentalOrderSession->getStartDate()->getTimestamp(), $rentalOrderSession->getEndDate()->getTimestamp());
        $this->fusionRentalOrder($rentalOrder, $rentalOrderSession);

        $creditCard = new CreditCard();
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);
        $creditCardForm->handleRequest($request);
        if($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {
            var_dump(str_replace(" ", "", $creditCardForm['cardNumber']->getData()));

            //create address for the bill
            $clientAddress = $this->createBillingAddress($rentalOrder->getClientAddress());
            $rentalOrder->setClientAddress($clientAddress);

            //rentalOrderGift
            $rentalOrderGift = $rentalOrder->getRentalOrderGift();
            $rentalOrderGiftAddress = $clientAddress;

            //set rentalOrder user
            $rentalOrder->setUser($this->getUser());

            //create the rentalOrder with statut 0
            $rentalOrder = $this->createRentalOrder($rentalOrder);
//            //create the gift
            if($rentalOrder->getRentalOrderGift()) {
                $rentalOrderGift->setAddress($rentalOrderGiftAddress);
                $this->createRentalOrderGift($rentalOrder, $rentalOrderGift);
            }
            return $this->redirectToRoute('home');

//
//            //pay
//
//
//            //edit the order status
//
//            //create order
//            //create day price
//            //link order with day price
//            if($creditCardForm['save']->getData()) {
//                //create the credit card on lemon way
//                var_dump($this->registerCard($user, $creditCardForm));
//                //save information in winefing data base
//            }
//            //create the user Wallet if it's not existing
//            if(!$user->getWallet()) {
//                $this->createWallet($user);
//            }
        }
        return $this->render('user/rental/paiement/paiement.html.twig', ['creditCardForm'=>$creditCardForm->createView(), 'rentalOrder'=>$rentalOrder]);
    }
    public function fusionRentalOrder(&$rentalOrder, $rentalOrderSession) {
        $rentalOrder->setRentalOrderGift($rentalOrderSession->getRentalOrderGift());
        $rentalOrder->setClientAddress($rentalOrderSession->getClientAddress());
        $rentalOrder->setRental($rentalOrderSession->getRental());
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
        //if the address form is submitted, add the address and data base
        // and then add the address to the rentalOrder save in session
        if($addressForm->isSubmitted() && $addressForm->isValid()) {
            $response = $api->post($this->get('_router')->generate('api_post_address'),  $request->request->get('address_user'));
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
            $body['address'] = $address->getId();
            $api->patch($this->get('_router')->generate('api_patch_address_user'), $body);
            $this->setAddressRentalOrder($address->getId());
            return $this->redirectToRoute('rental_paiement_gift');
        }
        //check if the user chooses an address already existing
        if (!empty($addressId)) {
            $this->setAddressRentalOrder($addressId);
            return $this->redirectToRoute('rental_paiement_gift');
        }
        return $this->render('user/rental/paiement/address.html.twig', ['addressForm'=>$addressForm->createView(), 'addresses'=> $addresses]);
    }

    /**
     * set the client address on the rental order store in session until the paiement.
     * @param $addressId
     */
    public function setAddressRentalOrder($addressId) {
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($addressId);
        $rentalOrder->setClientAddress($address);
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'rental', 'clientAddress'))));
    }
    /**
     * set the rental order gift on the rental order store in session until the paiement.
     * @param $addressId
     */
    public function setRentalOrderGiftRentalOrder($rentalOrderGift) {
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        $rentalOrder->setRentalOrderGift($rentalOrderGift);
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'rental', 'clientAddress', 'rentalOrderGift'))));
    }

    public function createBillingAddress($address) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->post($this->get('_router')->generate('api_post_address_copy'),  json_decode($serializer->serialize($address, 'json',SerializationContext::create()->setGroups(array('default'))), true));
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        return $address;
    }
    public function createRentalOrder($rentalOrder) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $rentalOrderNew['clientAddress'] = $rentalOrder->getClientAddress()->getId();
        $rentalOrderNew['user'] = $this->getUser()->getId();
        $rentalOrderNew['rental'] = $rentalOrder->getRental()->getId();
        $rentalOrderNew['startDate'] = $rentalOrder->getStartDate()->getTimestamp();
        $rentalOrderNew['endDate'] = $rentalOrder->getEndDate()->getTimestamp();
        $rentalOrderNew['averagePrice'] = $rentalOrder->getAveragePrice();
        $rentalOrderNew['dayNumber'] = $rentalOrder->getDayNumber();
        $rentalOrderNew['totalTax'] = $rentalOrder->getTotalTax();
        $rentalOrderNew['totalHT'] = $rentalOrder->getTotalHT();
        $rentalOrderNew['totalTTC'] = $rentalOrder->getTotalTTC();
        $rentalOrderNew['comission'] = $rentalOrder->getComission();
        $rentalOrderNew['amount'] = $rentalOrder->getAmount();
        $response = $api->post($this->get('_router')->generate('api_post_rental_order'),  $rentalOrderNew);
        $rentalOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        return $rentalOrder;
    }
    public function createRentalOrderGift($rentalOrder, $rentalOrderGift) {
        $api = $this->container->get('winefing.api_controller');
        $newRentalOrderGift['rentalOrder'] = $rentalOrder->getId();
        $newRentalOrderGift['address'] = $rentalOrderGift->getAddress()->getId();
        $newRentalOrderGift['message'] = $rentalOrderGift->getMessage();
        $newRentalOrderGift['signature'] = $rentalOrderGift->getSignature();
        $api->post($this->get('_router')->generate('api_post_rental_order_gift'),  $newRentalOrderGift);
    }

    /**
     * For each of the period of location, this function return an array with the date and the price associated.
     * @param $rental
     * @param $start
     * @param $end
     * @return array[date] = $price
     */
    public function getRentalOrder($rental, $start, $end) {
        $serializer = $this->container->get('jms_serializer');
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_order_before_post', array('rental'=>$rental, 'start'=>$start, 'end'=>$end)));
        $rentalOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        return $rentalOrder;

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