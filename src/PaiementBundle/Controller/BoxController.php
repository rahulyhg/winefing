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
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use JMS\Serializer\SerializationContext;

class BoxController extends Controller
{
    /**
     * @param Request $request
     * @return mixed
     * @Route("box/paiement", name="box_paiement")
     */
    public function paiementAction(Request $request){
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        $creditCard = new CreditCard();
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);
        $creditCardForm->handleRequest($request);
        if($creditCardForm->isSubmitted() && $creditCardForm->isValid()) {
        }
        return $this->render('user/box/paiement.html.twig', ['boxOrder'=>$boxOrder,
            'creditCardForm'=>$creditCardForm->createView()
        ]);
    }
    /**
     * @Route("/box/{id}/order", name="box_order")
     */
    public function selectBoxAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $box = $this->getBox($api, $serializer, $id, $request->getLocale());
        $boxOrder = new BoxOrder($box, $this->getUser());
        $this->get('session')->set('boxOrder', $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default', 'box', 'user', 'boxItems', 'boxItemChoices', 'boxOrderItemChoices'))));
        if($box->gethasChoice()) {
            return  $this->redirectToRoute('box_select_item_choices', array('id'=>$id));
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
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemChoice');
        if($request->isMethod('POST')) {
            foreach($request->request->get('boxItemChoice') as $boxItem) {
                $boxItem = $repository->findOneById($boxItem["boxItemChoice"]);
                $boxOrder->addBoxItemChoice($boxItem);
            }
            $json = $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default', 'box', 'user', 'boxItems', 'boxOrderItemChoices')));
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

        //get the box order stored in session
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');


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
            $response = $api->post($this->get('_router')->generate('api_post_address'),  $request->request->get('address_user'));
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
     * @Route("/paiement/box/select/{id}/{addressType}", name="box_paiement_select_address")
     */
    public function setAddressSession($id, $addressType) {
        //get the box order stored in session
        $serializer = $this->container->get('jms_serializer');
        $boxOrder = $serializer->deserialize($this->get('session')->get('boxOrder'), 'Winefing\ApiBundle\Entity\BoxOrder', 'json');
        //get the address
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($id);
        if($addressType == AddressTypeEnum::address_billing) {
            $boxOrder->setBillingAddress($address);
            $route = $this->get('_router')->generate('box_paiement_address', array('addressType'=>AddressTypeEnum::address_delivering));
        } else {
            $boxOrder->setDeliveringAddress($address);
            $route = $this->get('_router')->generate('box_paiement');
        }
        $json = $serializer->serialize($boxOrder, 'json', SerializationContext::create()->setGroups(array('default', 'box', 'user', 'boxItems', 'boxOrderItemChoices', 'billingAddress', 'deliveringAddress')));
        $this->get('session')->set('boxOrder', $json);
        return $this->redirect($route);

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