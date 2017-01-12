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
use Winefing\ApiBundle\Entity\StatusCodeEnum;

class BoxController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @Route("box/paiement", name="box_paiement")
     */
    public function paiementAction(Request $request){
        $box = $this->getBox($this->get('session')->get('box'), $request->getLocale());
        $creditCard = new CreditCard();
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);
        $webPath = $this->container->get('winefing.webPath_controller');
        $picturePath = $webPath->getPath($this->getParameter('credit_card_directory'));
        $creditCardForm->handleRequest($request);
        if($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {
        }
        return $this->render('user/box/paiement.html.twig', ['box'=>$box,
            'creditCardForm'=>$creditCardForm->createView(),
            'picturePath'=>$picturePath
        ]);
    }
    /**
     * @Route("/box/{id}/order", name="box_order")
     */
    public function selectBoxAction($id, Request $request) {
        $box = $this->getBox($id, $request->getLocale());
        $this->get('session')->set('box', $id);
        foreach($box['boxItems'] as $boxItem) {
            if(count($boxItem['boxItemChoices']) > 0) {
                return  $this->redirectToRoute('box_select_item_choices', array('id'=>$id));
            }
        }
        return  $this->render('user/box/paiement.html.twig', array("box" => $box));
    }

    /**
     * @param $id
     * @param Request $request
     * @return mixed
     * @Route("box/{id}/select/item/choices", name="box_select_item_choices")
     */
    public function selectItemChoicesAction($id, Request $request){
        $box = $this->getBox($id, $request->getLocale());
        if($request->isMethod('POST')) {
            $this->get('session')->set('boxItemChoices', $request->request->get('boxItemChoice'));
            return $this->redirectToRoute('box_paiement_address', array('addressType'=> AddressTypeEnum::address_billing));
        }
        return $this->render('user/box/select.html.twig', array("box" => $box));
    }
    public function getBox($id, $language) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_box_by_language', array('id'=> $id, 'language' => $language)));
        $box = $serializer->decode($response->getBody()->getContents());
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
     * @return mixed
     * @Route("/paiement/box/address/{addressType}", name="box_paiement_address")
     */
    public function billingAddress($addressType, Request $request) {
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
            return $this->redirectToRoute('box_paiement_select_address', array('id'=>$address->getId(), 'addressType'=>$addressType));
        }
        return $this->render('user/box/address.html.twig', ['addressForm'=>$addressForm->createView(), 'addresses'=> $addresses]);
    }

    /**
     * @return mixed
     * @Route("/paiement/box/select/{id}/{addressType}", name="box_paiement_select_address")
     */
    public function setAddressSession($id, $addressType) {
        if($addressType == AddressTypeEnum::address_billing) {
            $this->get('session')->set('billingAddress', $id);
            return $this->redirectToRoute('box_paiement_address', array('addressType'=>AddressTypeEnum::address_delivering));
        } else {
            $this->get('session')->set('deliveryAddress', $id);
            return $this->redirectToRoute('box_paiement');
        }
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

}