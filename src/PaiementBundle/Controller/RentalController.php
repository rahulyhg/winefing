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
use Winefing\ApiBundle\Entity\AddressTypeEnum;
use Winefing\ApiBundle\Entity\CharacteristicrentalValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\RentalOrderGift;
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use AppBundle\Form\RentalOrderGiftType;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\StatusOrderEnum;
use Winefing\ApiBundle\Entity\UserGroupEnum;

class RentalController extends Controller
{
    /**
     * @Route("users/rental/{id}/paiement", name="rental_paiement_date")
     *
     */
    public function rentalPaiement($id, Request $request) {
        //start to set in session a new rentalOrder
        $rentalOrder = new RentalOrder();

        //set startDate
        $startDate = new \DateTime();
        $startDate->setDate(substr($request->request->get('start'), 6, 4), substr($request->request->get('start'), 3, 2), substr($request->request->get('start'), 0, 2));
        $rentalOrder->setStartDate($startDate);

        //set enDate
        $endDate = new \DateTime();
        $endDate->setDate(substr($request->request->get('end'), 6, 4), substr($request->request->get('end'), 3, 2), substr($request->request->get('end'), 0, 2));
        $rentalOrder->setEndDate($endDate);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $rental->setMediaPresentation();

        $rentalOrder->setRental($rental);
        $serializer = $this->container->get('jms_serializer');
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default'))));
        return $this->redirectToRoute('rental_paiement_address', array('addressType'=> AddressTypeEnum::address_billing));
    }
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/remove/rental/order/gift", name="remove_rental_paiement_gift")
     */
    public function removeRentalGift() {
        $this->removeRentalOrderGift();
        return $this->redirectToRoute('rental_paiement');
    }
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/rental/gift", name="rental_paiement_gift")
     */
    public function paiementRentalGift(Request $request) {
        $rentalOrderGift = $this->getRentalOrderGift();
        if(!$rentalOrderGift || $rentalOrderGift == null) {
            new RentalOrderGift();
        }
        $rentalOrderGiftForm = $this->createForm(RentalOrderGiftType::class, $rentalOrderGift);
        $rentalOrderGiftForm->get('signature')->setData($this->getUser()->getFirstName());
        $rentalOrderGiftForm->handleRequest($request);
        if($rentalOrderGiftForm->isSubmitted() && $rentalOrderGiftForm->isSubmitted()) {
            $rentalOrderGift = $rentalOrderGiftForm->getData();
            $rentalOrderGift->setPrice($this->getParameter('rental_order_gift_price'));
            $this->setRentalOrderGiftRentalOrder($rentalOrderGift);
            return $this->redirectToRoute('rental_paiement_address', array('addressType'=>AddressTypeEnum::address_delivering));
        }
        return $this->render('user/rental/paiement/gift.html.twig', array('rentalOrderGift'=>$rentalOrderGiftForm->createView()));
    }
    /**
     * @param $order
     * @return mixed
     * @Route("paiement/rental", name="rental_paiement")
     */
    public function paiement(Request $request){
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $rentalOrderSession = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        $host = $this->getHost($api, $serializer, $rentalOrderSession);
        $rentalOrderGift = 0;
        if($rentalOrderSession->getRentalOrderGift() instanceof  RentalOrderGift) {
            $rentalOrderGift = 1;
        }
        $rentalOrder = $this->getRentalOrder($rentalOrderSession->getRental()->getId(), $rentalOrderGift, $rentalOrderSession->getStartDate()->getTimestamp(), $rentalOrderSession->getEndDate()->getTimestamp());
        $this->fusionRentalOrder($rentalOrder, $rentalOrderSession);

        $creditCard = new CreditCard();
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);
        $creditCardForm->handleRequest($request);
        if($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {
            //create the moneyIn on lemon way
            $lemonWay = $this->container->get('winefing.lemonway_controller');
            $lemonWayId = $lemonWay->moneyIn($host, $creditCardForm, $rentalOrder);
            $rentalOrder->getLemonWay()->setTransactionId($lemonWayId);

            if(!$creditCardForm->isValid()) {
                $this->addFlash('error', $this->get('translator')->trans('error.generic_form_error'));
            } else {
                return $this->submitAll($api, $serializer, $rentalOrder, $rentalOrderGift, $creditCardForm);
            }
        } elseif($rentalOrder->getTotal() > 2500 && $request->isMethod('POST')) {
            $this->submitAll($api, $serializer, $rentalOrder, $rentalOrderGift);
            $this->addFlash('success', $this->get('translator')->trans('success.booking_request_sent'));
            return $this->redirectToRoute('home');
        }
        return $this->render('user/rental/paiement/paiement.html.twig', ['creditCardForm'=>$creditCardForm->createView(), 'rentalOrder'=>$rentalOrder]);
    }
    public function submitAll($api, $serializer, $rentalOrder, $rentalOrderGift, $creditCardForm = '') {
        //create the lemon way
        if($rentalOrder->getTotal() < 2500) {
            $lemonWay = $this->submitLemonWay($api, $serializer, $rentalOrder->getLemonWay());
            $rentalOrder->setLemonWay($lemonWay);
        }
        //create address for the bill
        $billingAddress = $this->createAddress($rentalOrder->getInvoiceInformation()->getBillingAddress());
        $rentalOrder->getInvoiceInformation()->setBillingAddress($billingAddress);

        //if rental order gift create delivering address
        if($rentalOrderGift) {
            $deliveringAddress = $this->createAddress($rentalOrder->getInvoiceInformation()->getDeliveringAddress());
            $rentalOrder->getInvoiceInformation()->setDeliveringAddress($deliveringAddress);
        }

        //crete the invoice
        //create the rentalOrder with statut 0
        $rentalOrder->getInvoiceInformation()->setStatus(StatusOrderEnum::initiate);
        $invoiceInformation = $this->submitInvoiceInformation($api, $serializer,$rentalOrder->getInvoiceInformation(), $creditCardForm);
        $rentalOrder->setInvoiceInformation($invoiceInformation);

        //invoice host
        $invoiceHost = $this->submitInvoice($api, $serializer, $rentalOrder->getInvoiceHost());
        $rentalOrder->setInvoiceHost($invoiceHost);

        //invoice client
        $invoiceClient = $this->submitInvoice($api, $serializer, $rentalOrder->getInvoiceClient());
        $rentalOrder->setInvoiceClient($invoiceClient);

        //rentalOrderGift
        $rentalOrderGift = $rentalOrder->getRentalOrderGift();

        //dayPrices
        $dayPrices = $rentalOrder->getDayPrices();


        //create the rentalOrder
        $rentalOrder = $this->createRentalOrder($rentalOrder);

        //submit the rentalOrder gift
        if($rentalOrderGift) {
            $this->createRentalOrderGift($api, $rentalOrder, $rentalOrderGift);
        }

        //create day price
        $newDayPrice['rentalOrder'] = $rentalOrder->getId();
        $api = $this->container->get('winefing.api_controller');
        foreach($dayPrices as $dayPrice) {
            $newDayPrice['price'] = $dayPrice->getPrice();
            $newDayPrice['date'] = $dayPrice->getDate()->getTimestamp();
            $api->post($this->get('_router')->generate('api_post_day_price'), $newDayPrice);
        }
        //clear the rental order cache

        //send a email
        $api->post($this->get('_router')->generate('api_post_email_paiement'), array('user'=>$this->getUser()->getId()));

        $this->addFlash('success', $this->get('translator')->trans('success.paiement_well_done'));
        return $this->redirectToRoute('rental_order_detail', array('id'=>$rentalOrder->getId()));
    }
    public function submitInvoice($api, $serializer, $invoice) {
        $body['tax'] = $invoice->getTax();
        $body['totalTax'] = $invoice->getTotalTax();
        $body['totalHT'] = $invoice->getTotalHT();
        $body['totalTTC'] = $invoice->getTotalTTC();
        $response = $api->post($this->get('_router')->generate('api_post_invoice'),  $body);
        $invoice = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Invoice', 'json');
        return $invoice;
    }
    /**
     * Edit the rental order with the lemon way transaction id.
     * This id will allows to validate the transaction when the host will accept the reservation
     * @param $rentalOrder
     * @param $transactionId
     */
    public function setRentalOrderTransactionId($api, $rentalOrder, $transactionId) {
        $rt["rentalOrder"] =  $rentalOrder->getId();
        $rt["lemonWayTransactionId"] =  $transactionId;
        $api->patch($this->get('_router')->generate('api_patch_rental_order_lemon_way_transaction_id'),  $rt);
    }

    public function fusionRentalOrder(&$rentalOrder, $rentalOrderSession) {
        if($rentalOrderSession->getRentalOrderGift()) {
            $rentalOrder->setRentalOrderGift($rentalOrderSession->getRentalOrderGift());
        }
        $rentalOrder->setRental($rentalOrderSession->getRental());
        $rentalOrder->setInvoiceInformation($rentalOrderSession->getInvoiceInformation());
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
     * route for a new address
     * @return mixed
     * @Route("/paiement/rental/address/{addressType}", name="rental_paiement_address")
     */
    public function rentalOrderAddress($addressType, Request $request) {
        $user = $this->getUser();
        $serializer = $this->container->get('jms_serializer');

        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_addresses_by_user', array('userId'=> $user->getId())));

        $addresses = new ArrayCollection();
        if($response->getStatusCode() != StatusCodeEnum::empty_response) {
            $addresses = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Address>', 'json');
        }
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address);
        $addressForm->add('name', null, array('required'=>true, 'label'=>'label.name', 'attr'=>array('maxlength'=>"255",'class'=>'form-control','placeholder'=>'example.address_home')));

        $addressForm->handleRequest($request);
        $body['user'] = $user->getId();

        if($addressForm->isSubmitted() && $addressForm->isValid()) {
            $response = $api->post($this->get('_router')->generate('api_post_address'),  $request->request->get('address'));
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
            $body['address'] = $address->getId();
            $api->patch($this->get('_router')->generate('api_patch_address_user'), $body);
            return $this->redirectToRoute('rental_paiement_select_address', array('id'=>$address->getId(), 'addressType'=>$addressType));
        }
        return $this->render('user/rental/paiement/address.html.twig', ['addressForm'=>$addressForm->createView(), 'addresses'=> $addresses]);
    }

    /**
     * set the billing address or the delivering address
     * @return mixed
     * @Route("/rental-order/address/{id}/type/{addressType}", name="rental_paiement_select_address")
     */
    public function setAddressSession($id, $addressType) {
        //get the box order stored in session
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        //get the address
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($id);
        if($addressType == AddressTypeEnum::address_billing) {
            $rentalOrder->getInvoiceInformation()->setBillingAddress($address);
            $route = $this->get('_router')->generate('rental_paiement_gift');
        } else {
            $rentalOrder->getInvoiceInformation()->setDeliveringAddress($address);
            $route = $this->get('_router')->generate('rental_paiement');
        }
        $json = $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default')));
        $this->get('session')->set('rentalOrder', $json);
        return $this->redirect($route);

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
        $rentalOrder->setBillingAddress($address);
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'rental', 'billingAddress'))));
    }
    /**
     * set the rental order gift on the rental order store in session until the paiement.
     * @param $addressId
     */
    public function setRentalOrderGiftRentalOrder($rentalOrderGift) {
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        $rentalOrder->setRentalOrderGift($rentalOrderGift);
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'rental', 'billingAddress', 'rentalOrderGift'))));
    }
    public function removeRentalOrderGift() {
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        $rentalOrder->setRentalOrderGift(null);
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'rental', 'billingAddress', 'rentalOrderGift'))));
    }
    public function getRentalOrderGift() {
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $serializer->deserialize($this->get('session')->get('rentalOrder'), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        return $rentalOrder->getRentalOrderGift();
    }

    public function getHost($api, $serializer, $rentalOrder) {
        $response = $api->get($this->get('_router')->generate('api_get_user_host_by_rental', ['rental'=>$rentalOrder->getRental()->getId()]));
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        return $address;
    }

    public function createAddress($address) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->post($this->get('_router')->generate('api_post_address_copy'),  json_decode($serializer->serialize($address, 'json',SerializationContext::create()->setGroups(array('default'))), true));
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        return $address;
    }
    public function submitInvoiceInformation($api, $serializer, $invoiceInformation, $creditCardForm = '') {
        if($invoiceInformation->getDeliveringAddress() instanceof Address) {
            $body['deliveringAddress'] = $invoiceInformation->getDeliveringAddress()->getId();
        }
        $body['billingAddress'] = $invoiceInformation->getBillingAddress()->getId();
        $body['user'] = $this->getUser()->getId();
        if($creditCardForm) {
            $body['billingName'] = $creditCardForm->get('cardName')->getData();
        }
        $body['status'] = $invoiceInformation->getStatus();
        $response = $api->post($this->get('_router')->generate('api_post_invoice_information'),  $body);
        $invoiceInformation = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\InvoiceInformation', 'json');
        return $invoiceInformation;
    }

    public function submitLemonWay($api, $serializer, $lemonWay) {
        $body['amountCom'] = $lemonWay->getAmountCom();
        $body['amountTot'] = $lemonWay->getAmountTot();
        $body['transactionId'] = $lemonWay->getTransactionId();
        $response = $api->post($this->get('_router')->generate('api_post_lemon_way'),  $body);
        $lemonWay = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\LemonWay', 'json');
        return $lemonWay;
    }
    public function createRentalOrder($rentalOrder) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $rentalOrderNew['rental'] = $rentalOrder->getRental()->getId();
        $rentalOrderNew['leftToPay'] = $rentalOrder->getLeftToPay();
        $rentalOrderNew['startDate'] = $rentalOrder->getStartDate()->getTimestamp();
        $rentalOrderNew['endDate'] = $rentalOrder->getEndDate()->getTimestamp();
        $rentalOrderNew['averagePrice'] = $rentalOrder->getAveragePrice();
        $rentalOrderNew['dayNumber'] = $rentalOrder->getDayNumber();
        $rentalOrderNew['total'] = $rentalOrder->getTotal();
        $rentalOrderNew['amount'] = $rentalOrder->getAmount();
        $rentalOrderNew['invoiceClient'] = $rentalOrder->getInvoiceClient()->getId();
        $rentalOrderNew['invoiceHost'] = $rentalOrder->getInvoiceHost()->getId();
        $rentalOrderNew['invoiceInformation'] = $rentalOrder->getInvoiceInformation()->getId();
        $rentalOrderNew['lemonWay'] = $rentalOrder->getLemonWay()->getId();
        $response = $api->post($this->get('_router')->generate('api_post_rental_order'),  $rentalOrderNew);
        $rentalOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        return $rentalOrder;
    }
    public function createRentalOrderGift($api, $rentalOrder, $rentalOrderGift) {
        $newRentalOrderGift['rentalOrder'] = $rentalOrder->getId();
        $newRentalOrderGift['message'] = $rentalOrderGift->getMessage();
        $newRentalOrderGift['signature'] = $rentalOrderGift->getSignature();
        $api->post($this->get('_router')->generate('api_post_rentalorder_gift'),  $newRentalOrderGift);
    }

    /**
     * For each of the period of location, this function return an array with the date and the price associated.
     * @param $rental
     * @param $start
     * @param $end
     * @return array[date] = $price
     */
    public function getRentalOrder($rental, $rentalOrderGift, $start, $end) {
        $serializer = $this->container->get('jms_serializer');
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_order_before_post', array('rental'=>$rental, 'rentalOrderGift'=>$rentalOrderGift, 'start'=>$start, 'end'=>$end)));
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

    /**
     * @param $addressType
     * @param $id
     * @return mixed
     * @Route("/rental/order/{id}/{invoiceType}", name="rental_order_invoice")
     */
    public function getInvoice($id, $invoiceType, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental_order', array('id'=>$id, 'language'=>$request->getLocale())));
        $rentalOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        if($request->query->get('type') == 'pdf') {
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($this->renderView('user/invoice/pdf.html.twig', ['order' => $rentalOrder, 'invoiceType'=>$invoiceType])),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="file.pdf"'
                )
            );
        } else {
            return $this->render('user/invoice/web.html.twig', ['order' => $rentalOrder, 'invoiceType'=>$invoiceType]);
        }
    }

    /**
     * @param $addressType
     * @param $id
     * @return mixed
     * @Route("/rental/order/{id}", name="rental_order_detail")
     */
    public function getRentalOrderDetail($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental_order_detail', array('id'=>$id, 'language'=>$request->getLocale())));
        $rentalOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\RentalOrder', 'json');
        return $this->render('rentalOrderDetail.html.twig', array('rentalOrder'=>$rentalOrder));
    }

}