<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 10/01/2017
 * Time: 13:37
 */

namespace PaiementBundle\Controller;

use AppBundle\Form\AddressType;
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
use Winefing\ApiBundle\Entity\BoxOrder;
use Winefing\ApiBundle\Entity\CharacteristicrentalValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Invoice;
use Winefing\ApiBundle\Entity\LemonWay;
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\StatusOrderEnum;

class BoxController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @Route("box/paiement", name="box_paiement")
     */
    public function paiementAction(Request $request){
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        $creditCard = new CreditCard();
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);
        $creditCardForm->handleRequest($request);
        if($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {

            //make the transaction
            $lemonWayId = $lemonWay->moneyIn(null, $creditCardForm, $boxOrder);
            if(!$creditCardForm->isValid()) {
                $this->addFlash('error', $this->get('translator')->trans('error.generic_form_error'));
            } else {
                //create the lemon way in database
                $boxOrder->getLemonWay()->setTransactionId($lemonWayId);
                $lemonWay = $this->submitLemonWay($api, $serializer, $boxOrder->getLemonWay());
                $boxOrder->setLemonWay($lemonWay);

                //create address for the invoice information
                $billingAddress = $this->createAddress($boxOrder->getInvoiceInformation()->getBillingAddress());
                $boxOrder->getInvoiceInformation()->setBillingAddress($billingAddress);

                //create the delivering address for the invoice information
                $deliveringAddress = $this->createAddress($boxOrder->getInvoiceInformation()->getDeliveringAddress());
                $boxOrder->getInvoiceInformation()->setDeliveringAddress($deliveringAddress);

                //create the invoice information
                $boxOrder->getInvoiceInformation()->setStatus(StatusOrderEnum::pay);
                $invoiceInformation = $this->submitInvoiceInformation($api, $serializer, $boxOrder->getInvoiceInformation(), $creditCardForm);
                $boxOrder->setInvoiceInformation($invoiceInformation);



                //create the invoice
                $boxOrder->getLemonWay()->setTransactionId($lemonWayId);
                $invoice = $this->submitInvoice($api, $serializer, $boxOrder->getInvoice());
                $boxOrder->setInvoice($invoice);

                //create the boxOrder
                $boxOrder = $this->createBoxOrder($api, $serializer, $boxOrder);

                var_dump($boxOrder->getInvoiceInformation()->getBillingAddress()->getName());

                $this->addFlash('success', $this->get('translator')->trans('success.paiement_well_done'));

                return $this->redirectToRoute('home');

            }

//            clear the rental order cache

//            send a email
            $api->post($this->get('_router')->generate('api_post_email_paiement'), array('user'=>$this->getUser()->getId()));


            $lemonWay->moneyIn(null, $creditCardForm, $boxOrder);

        }
        return $this->render('user/box/paiement.html.twig', ['boxOrder'=>$boxOrder,
            'creditCardForm'=>$creditCardForm->createView()
        ]);
    }
    public function submitInvoiceInformation($api, $serializer, $invoiceInformation, $creditCardForm) {
        $body['deliveringAddress'] = $invoiceInformation->getDeliveringAddress()->getId();
        $body['billingAddress'] = $invoiceInformation->getBillingAddress()->getId();
        $body['user'] = $this->getUser()->getId();
        $body['billingName'] = $creditCardForm->get('cardName')->getData();
        $body['status'] = $invoiceInformation->getStatus();
        $response = $api->post($this->get('_router')->generate('api_post_invoice_information'),  $body);
        $invoiceInformation = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\InvoiceInformation', 'json');
        return $invoiceInformation;
    }

    public function submitLemonWay($api, $serializer, $lemonWay) {
        $body['amountCom'] = $lemonWay->getAmountCom();
        $body['amountTot'] = $lemonWay->getAmountTot();
        $body['transactionId'] = $lemonWay->getTransactionId();
        var_dump($body);
        $response = $api->post($this->get('_router')->generate('api_post_lemon_way'),  $body);
        $lemonWay = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\LemonWay', 'json');
        return $lemonWay;
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


    public function setBoxOrderTransactionId($api, $boxOrder, $transactionId) {
        $rt["boxOrder"] =  $boxOrder->getId();
        $rt["lemonWayTransactionId"] =  $transactionId;
        $api->patch($this->get('_router')->generate('api_patch_box_order_lemon_way_transaction_id'),  $rt);
    }

    public function createBoxOrder($api, $serializer, $boxOrder) {
        $boxOrderNew['invoiceInformation'] = $boxOrder->getInvoiceInformation()->getId();
        $boxOrderNew['invoice'] = $boxOrder->getInvoice()->getId();
        $boxOrderNew['lemonWay'] = $boxOrder->getLemonWay()->getId();
        $boxOrderNew['box'] = $boxOrder->getBox()->getId();
        foreach($boxOrder->getBoxItemChoices() as $boxItemChoice) {
            $boxOrderNew['boxItemChoices'][] = $boxItemChoice->getId();
        }
        $response = $api->post($this->get('_router')->generate('api_post_box_order'),  $boxOrderNew);
        $boxOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        return $boxOrder;
    }

    /**
     * @Route("/box/{id}/order", name="box_order")
     */
    public function selectBoxAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $this->getNewBoxOrder($api, $serializer, $id, $request->getLocale());
        $this->get('session')->set('boxOrder', $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default'))));
        if($boxOrder->getBox()->gethasChoice()) {
            return  $this->redirectToRoute('box_select_item_choices');
        }
        return  $this->redirectToRoute('box_paiement_address', array('addressType'=>AddressTypeEnum::address_billing));
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     * @Route("box/select/item/choices", name="box_select_item_choices")
     */
    public function selectItemChoicesAction(Request $request){
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemChoice');
        if($request->isMethod('POST')) {
            foreach($request->request->get('boxItemChoice') as $boxItem) {
                $boxItemChoice = $repository->findOneById($boxItem["boxItemChoice"]);
                $boxItemChoice->setTr($request->getLocale());
                $boxOrder->addBoxItemChoice($boxItemChoice);
            }
            $json = $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default')));
            $this->get('session')->set('boxOrder', $json);
            return $this->redirectToRoute('box_paiement_address', array('addressType'=>AddressTypeEnum::address_billing));
        }
        return $this->render('user/box/select.html.twig', array("box" => $boxOrder->getBox()));
    }
    public function getBox($api, $serializer, $id, $language) {
        $response = $api->get($this->get('router')->generate('api_get_box_by_language', array('id'=> $id, 'language' => $language)));
        $box = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Box', 'json');
        return $box;
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
     * @Route("/paiement/box/address/{addressType}", name="box_paiement_address")
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
            return $this->redirectToRoute('box_paiement_select_address', array('id'=>$address->getId(), 'addressType'=>$addressType));
        }
        return $this->render('user/box/address.html.twig', ['addressForm'=>$addressForm->createView(), 'addresses'=> $addresses]);
    }

    /**
     * set the billing address or the delivering address
     * @return mixed
     * @Route("/box-order/address/{id}/type/{addressType}", name="box_paiement_select_address")
     */
    public function setAddressSession($id, $addressType) {
        //get the box order stored in session
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        //get the address
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($id);
        if($addressType == AddressTypeEnum::address_billing) {
            $boxOrder->getInvoiceInformation()->setBillingAddress($address);
            $route = $this->get('_router')->generate('box_paiement_address', array('addressType'=>AddressTypeEnum::address_delivering));
        } else {
            $boxOrder->getInvoiceInformation()->setDeliveringAddress($address);
            $route = $this->get('_router')->generate('box_paiement');
        }
        $json = $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default')));
        $this->get('session')->set('boxOrder', $json);
        return $this->redirect($route);

    }


    public function createAddress($address) {
        $body['id'] = $address->getId();
        var_dump($address->getStreetAddress());
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->post($this->get('_router')->generate('api_post_address_copy'),  $body);
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        return $address;
    }

    /**
     * return a new box Order
     * @param $api
     * @param $serializer
     * @param $boxId
     * @return mixed
     */
    public function getNewBoxOrder($api, $serializer, $boxId, $languageId) {
        $response = $api->get($this->get('_router')->generate('api_get_box_order_new', array('boxId'=> $boxId, 'language'=>$languageId)));
        $boxOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        return $boxOrder;
    }

    /**
     * @param $addressType
     * @param $id
     * @return mixed
     * @Route("/box/paiement/delete/address/{addressType}/{id}", name="box_paiement_billing_address")
     */
    public function deleteAddressAction($addressType, $id){
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('_router')->generate('api_delete_address', array('id'=>$id)));
        return $this->redirectToRoute('box_paiement_billing_address', array('addressType'=>$addressType));
    }

    /**
     * @param $addressType
     * @param $id
     * @return mixed
     * @Route("/box/order/{id}/invoice", name="box_order_invoice")
     */
    public function getInvoice($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_box_order', array('id'=>$id, 'language'=>$request->getLocale())));
        $boxOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        if($request->query->get('type') == 'pdf') {
            return new Response(
                $this->get('knp_snappy.pdf')->getOutputFromHtml($this->renderView('user/invoice/pdf.html.twig', ['order' => $boxOrder])),
                200,
                array(
                    'Content-Type'          => 'application/pdf',
                    'Content-Disposition'   => 'attachment; filename="file.pdf"'
                )
            );
        } else {
            return $this->render('user/invoice/web.html.twig', ['order' => $boxOrder]);
        }
    }

    /**
     * @param $addressType
     * @param $id
     * @return mixed
     * @Route("/box/order/{id}", name="box_order_detail")
     */
    public function getRentalOrderDetail($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_box_order', array('id'=>$id, 'language'=>$request->getLocale())));
        $boxOrder = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        return $this->render('boxOrderDetail.html.twig', array('boxOrder'=>$boxOrder));
    }

}