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

class LemonWayController
{
    protected $em;
    protected $serializer;
    const DIRECTKIT_WS = 'https://sandbox-api.lemonway.fr/mb/winefing/dev/directkitxml/service.asmx';
    const INFORMATIONS = ["wlLogin"=> 'adminmb', "wlPass" => "WhWo//2016", "language" => 'fr', "version"=>1.0, "walletIp"=> '212.51.179.194', 'walletUa'=> 'ua'];

    public function __construct(EntityManager $entityManager, Serializer $serializer){
        $this->em = $entityManager;
        $this->serializer = $serializer;
    }
    /**
     *
     */
    public function addWallet($user) {
        $client = new \Soapclient($this::DIRECTKIT_WS."?wsdl");
        $wallet['wallet'] = $user->getId();
        $this->setUserWallet($wallet, $user);
        $this->setCompanyWallet($wallet);
        $this->setCompanyInformationWallet($wallet, $user);
        $response = $client->RegisterWallet(array_merge($this::INFORMATIONS, $wallet));
        if(empty($response->RegisterWalletResult->E)) {
            $this->updateUser($user);
            $response->RegisterWalletResult->WALLET->ID;
        } else {
            switch ($response->RegisterWalletResult->E->Msg){
                case '152' :
                    $this->updateUser($user);
                    return $response->RegisterWalletResult->E->Msg;
                    break;
                default :
                    return $response->RegisterWalletResult->E->Msg;
            }
        }
    }
    public function getUser($id) {
        $user = $this->userManager->findUserBy(array('id'=>$id));
        return $user;
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
    public function setAddressWallet(&$wallet, $user) {
        $wallet->setStreet('');
        $wallet->setPostCode('');
        $wallet->setCity('');
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
}