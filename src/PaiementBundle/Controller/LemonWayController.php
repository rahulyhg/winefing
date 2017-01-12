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
use Winefing\ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;
use JMS\Serializer\Serializer;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Form\FormError;

class LemonWayController
{
    protected $em;
    protected $serializer;
    protected $directkit_ws;
    protected $informations;

    public function __construct(EntityManager $entityManager, Serializer $serializer){
        $this->em = $entityManager;
        $this->serializer = $serializer;
        $this->directkit_ws = 'https://sandbox-api.lemonway.fr/mb/winefing/dev/directkitxml/service.asmx';
        $this->informations = ["wlLogin"=> 'adminmb', "wlPass" => "WhWo//2016", "language" => 'fr', "version"=>1.0, "walletIp"=> '212.51.179.194', 'walletUa'=> 'ua'];
    }
    /**
     *
     */
    public function addWallet($user) {
        $client = new \Soapclient($this->directkit_ws."?wsdl");
        $wallet['wallet'] = $user->getId();
        $this->setUserWallet($wallet, $user);
        if($user->getRoles() == UserGroupEnum::Host) {
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
            $creditCard = new \Winefing\ApiBundle\Entity\CreditCard();
            $creditCard->setOwner($creditCardForm['name']->getData());
            return $this->newCreditCard($response->RegisterCardResult->CARD, $creditCard);
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
    public function newCreditCard($card, &$creditCard) {
        $creditCard->setExpirationDate($card->EXTRA->EXP);
        $creditCard->setNumber($card->EXTRA->NUM);
        $creditCard->setLemonWayId($card->ID);
    }
    public function setCard(&$card, $creditCardForm) {
        $card['cardType'] = $creditCardForm['cardType']->getData();
        $card['cardNumber'] = str_replace(" ", "", $creditCardForm['cardNumber']->getData());
        $card['cardCode'] = $creditCardForm['cardCode']->getData();
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