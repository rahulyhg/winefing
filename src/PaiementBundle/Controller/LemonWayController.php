<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 18/10/2016
 * Time: 15:41
 */

namespace PaiementBundle\Controller;


use PaiementBundle\Entity\CreditCard;
use PaiementBundle\Entity\Wallet;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Winefing\ApiBundle\Entity\BoxOrder;
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Form\FormError;
use Winefing\ApiBundle\Controller\ApiController as Api;
use Symfony\Component\Routing\Router;

class LemonWayController
{
    protected $em;
    protected $serializer;
    protected $directkit_ws;
    protected $informations;
    protected $api;
    protected $router;
    protected $winefingWalletId = "winefing";

    public function __construct(EntityManager $entityManager, Serializer $serializer, Api $api, Router $router){
        $this->em = $entityManager;
        $this->serializer = $serializer;
        $this->directkit_ws = 'https://sandbox-api.lemonway.fr/mb/winefing/dev/directkitxml/service.asmx';
        $this->informations = ["wlLogin"=> 'adminmb', "wlPass" => "WhWo//2016", "language" => 'fr', "version"=>1.0, "walletIp"=> '212.51.179.194', 'walletUa'=> 'ua'];
        $this->api = $api;
        $this->router = $router;
    }
    public function sendPayment($user, $amount){
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $body = array();
        $body['debitWallet'] = 'SC';
        $body['creditWallet'] = $user->getId();
        $body['amount'] = strval(number_format($amount));
        $body['message'] = '';
        $response = $client->SendPaiement(array_merge($this->informations, $body));
        var_dump($response);
        if(empty($response->SendPaymentResult->E)) {
            var_dump($response->SendPaymentResult->MONEYIN->HPAY->STATUS);
//            $this->newCreditCard($response->RegisterCardResult->CARD, $creditCardForm, $user);
        } else {
            switch ($response->SendPaymentResult->E->Code){
                default :
                    throw new \Exception($response->SendPaymentResult->E->Msg);
            }
        }

    }
    public function refundMoneyIn($transactionId, $amountToRefund) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $response = $client->RefundMoneyIn(array_merge($this->informations, array('transactionId'=>$transactionId, 'amountToRefound'=>$amountToRefund)));
        var_dump($response);
        if(empty($response->RefundMoneyInResult->E)) {
            var_dump($response->RefundMoneyInResult->MONEYIN->HPAY->STATUS);
//            $this->newCreditCard($response->RegisterCardResult->CARD, $creditCardForm, $user);
        } else {
            switch ($response->RefundMoneyInResult->E->Code){
                default :
                    throw new \Exception($response->RefundMoneyInResult->E->Msg);
            }
        }

    }


    public function registerIban(User $user, &$ibanForm) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $iban = $ibanForm->getData();
        $ib = array('wallet'=>$user->getId(), 'holder'=> $iban->getCompany()->getName(), 'bic'=> $iban->getBic(), 'iban'=> $iban->getIban());
        $response = $client->RegisterIBAN(array_merge($this->informations,$ib));
        if(!empty($response->RegisterIBANResult->E)) {
            switch ($response->RegisterIBANResult->E->Code){
                case '221':
                    $ibanForm->get('iban')->addError(new FormError($response->RegisterIBANResult->E->Msg));
                    break;
                default :
                    throw new \Exception($response->RegisterIBANResult->E->Msg);
            }
        }
    }
    public function moneyInValidate($transactionId) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $response = $client->MoneyInValidate(array_merge($this->informations, array('transactionId'=>$transactionId)));
        var_dump($response);
        if(empty($response->MoneyInValidateResult->E)) {
            var_dump($response->MoneyInValidateResult->MONEYIN->HPAY->STATUS);
//            $this->newCreditCard($response->RegisterCardResult->CARD, $creditCardForm, $user);
        } else {
            switch ($response->MoneyInValidateResult->E->Code){
                default :
                    throw new \Exception($response->MoneyInValidateResult->E->Msg);
            }
        }

    }
    public function moneyIn($user, $creditCardForm, $object) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        if($object instanceof RentalOrder) {
            $rentalOrder = $object;
            $card['wallet'] = $user->getId();
            $card['amountCom'] = strval(number_format($rentalOrder->getLemonWay()->getAmountCom(), 2));
            $card['amountTot'] = strval(number_format($rentalOrder->getLemonWay()->getAmountTot(), 2));
            $card['isPreAuth'] = 1;
        } elseif ($object instanceof BoxOrder) {
            $boxOder = $object;
            $card['wallet'] = $this->winefingWalletId;
            $card['amountCom'] = strval(number_format($boxOder->getLemonWay()->getAmountCom(), 2));
            $card['amountTot'] = strval(number_format($boxOder->getLemonWay()->getAmountTot(), 2));
            $card['isPreAuth'] = 0;
        }
        $this->setCard($card, $creditCardForm);
        $response = $client->MoneyIn(array_merge($this->informations, $card));
        var_dump($response);

        if(empty($response->MoneyInResult->E)) {
            return $response->MoneyInResult->TRANS->HPAY->ID;
        } else {
            switch ($response->MoneyInResult->E->Code){
                case '212' :
                    $creditCardForm->get('cardNumber')->addError(new FormError($response->MoneyInResult->E->Msg));
                    break;
                case '266' :
                    $creditCardForm->get('cardDate')->addError(new FormError($response->MoneyInResult->E->Msg));
                    break;
                case '267' :
                    $creditCardForm->get('cardCode')->addError(new FormError($response->MoneyInResult->E->Msg));
                    break;
                case '171' :
                    $creditCardForm->addError(new FormError($response->MoneyInResult->E->Msg));
                    break;
                default :
                    throw new \Exception($response->MoneyInResult->E->Msg);
            }
        }
    }
    /**
     *
     */
    public function addWallet($user) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $wallet['wallet'] = $user->getId();
        $this->setUserWallet($wallet, $user);
        if(implode(",",$user->getRoles()) == UserGroupEnum::Host) {
            $this->setCompanyWallet($wallet);
            $this->setCompanyInformationWallet($wallet, $user);
        }
        $response = $client->RegisterWallet(array_merge($this->informations, $wallet));
        if(empty($response->RegisterWalletResult->E)) {
            $this->updateUser($user);
            $response->RegisterWalletResult->WALLET->ID;
        } else {
            switch ($response->RegisterWalletResult->E->Code){
                case '152' :
                    $this->updateUser($user);
                    return $response->RegisterWalletResult->E->Msg;
                    break;
                default :
                    return $response->RegisterWalletResult->E->Msg;
            }
        }
    }
    public function registerCard($user, &$creditCardForm) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $card['wallet'] = $user->getId();
        $this->setCard($card, $creditCardForm);
        $response = $client->RegisterCard(array_merge($this->informations, $card));
        var_dump($response);
        if(empty($response->RegisterCardResult->E)) {
            $this->newCreditCard($response->RegisterCardResult->CARD, $creditCardForm, $user);
        } else {
            switch ($response->RegisterCardResult->E->Code){
                case '212' :
                    $creditCardForm->get('cardNumber')->addError(new FormError($response->RegisterCardResult->E->Msg));
                    break;
                case '266' :
                    $creditCardForm->get('cardDate')->addError(new FormError($response->RegisterCardResult->E->Msg));
                    break;
                case '267' :
                    $creditCardForm->get('cardCode')->addError(new FormError($response->RegisterCardResult->E->Msg));
                    break;
                default :
                    throw new \Exception($response->RegisterCardResult->E->Msg);
            }
        }
    }

    /**
     * Post credit card
     * @param $card
     * @param $creditCardForm
     */
    public function newCreditCard($card, $creditCardForm, $user) {
        $newCreditCard["lemonWayId"] =  $card->ID;
        $newCreditCard["owner"] =  $creditCardForm->get('cardName')->getData();
        $newCreditCard["user"] =  $user->getId();
        $this->api->post($this->router->generate('api_post_credit_card'),  $newCreditCard);
    }
    public function setCard(&$card, $creditCardForm) {
        $card['cardType'] = $creditCardForm['cardType']->getData();
        $card['cardNumber'] = str_replace(" ", "", $creditCardForm['cardNumber']->getData());
        $card['cardCrypto'] = $creditCardForm['cardCode']->getData();
        $card['cardDate'] = str_replace(" ", "", $creditCardForm['cardDate']->getData());
    }
    public function updateUser(User $user) {
        $user->setWallet(1);
        $this->em->persist($user);
        $this->em->flush();
    }

    public function creditCardMoneyIn($creditCard, $amountTot) {
        $creditCard = new CreditCard();
    }

    public function editWallet($userInformations) {

    }

    /**
     * Set classical informations about the user.
     * @param Wallet $wallet
     * @param User $user
     */
    public function setUserWallet(&$wallet, $user) {
        $wallet["clientMail"] = $user->getEmail();
        if(empty($user->getSex())) {
            $wallet["clientTitle"] = $user->getSex();
        }
        $wallet["clientFirstName"] = $user->getFirstName();
        $wallet["clientLastName"] = $user->getLastName().'WWW';
        $wallet["phoneNumber"] = $user->getPhoneNumber();
        if(!is_null($user->getBirthDate())) {
            $wallet["birthdate"] = date_format($user->getBirthDate(), 'd/m/Y');
        }
    }
    /**
     * Set classical informations about the user.
     * @param Wallet $wallet
     * @param User $user
     */
    public function setUserUpdateWallet(&$wallet, $user) {
        $wallet["clientMail"] = $user->getEmail();
        if(empty($user->getSex())) {
            $wallet["clientTitle"] = $user->getSex();
        }
        $wallet["clientFirstName"] = $user->getFirstName();
        $wallet["clientLastName"] = $user->getLastName().'WWW';
        $wallet["phoneNumber"] = $user->getPhoneNumber();
        if(!is_null($user->getBirthDate())) {
            $wallet["birthdate"] = date_format($user->getBirthDate(), 'd/m/Y');
        }
    }

    /**
     * Set the billing address
     * @param Wallet $wallet
     * @param User $user
     */
    public function setAddressWallet(&$wallet, $address) {
        $wallet['newStreet'] = $address->getStreetAddress();
        $wallet['newPostCode'] = $address->getPostalCode();
        $wallet['newCity'] = $address->getCity();
        $wallet['newCtry'] = $address->getCountry();
    }

    /**
     *If the user is a Host.
     * @param Wallet $wallet
     * @param User $user
     */
    public function setCompanyWallet(&$wallet){
        $wallet["isCompany"] = 1;
        $wallet['payerOrBeneficiary'] = 2;
    }
    /**
     *If the user is a Host, we have to indicate other informations (about the company).
     * @param Wallet $wallet
     * @param User $user
     */
    public function setCompanyInformationWallet(&$wallet, $user){
        $wallet["companyName"] = $user->getDomains()[0]->getName();
        $wallet["companyDescription"] = $user->getDomains()[0]->getDescription();
    }

    /**
     * This allows to update the information about the address. This is the only function allowing to add information about the address.
     * @param $user
     * @param $address
     * @return mixed
     */
    public function updateAddressWallet($user, $address){
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $wallet['wallet'] = $user->getId();
        $this->setAddressWallet($wallet, $address);
        $response = $client->UpdateWalletDetails(array_merge($this->informations, $wallet));

        if(empty($response->RegisterWalletResult->E)) {
            $this->updateUser($user);
            $response->RegisterWalletResult->WALLET->ID;
        } else {
            switch ($response->RegisterWalletResult->E->Msg){
                default :
                    return $response->RegisterWalletResult->E->Msg;
            }
        }

    }
}