<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 18/10/2016
 * Time: 15:41
 */

namespace PaiementBundle\Controller;


use FOS\UserBundle\Doctrine\UserManager;
use PaiementBundle\Entity\CreditCard;
use PaiementBundle\Entity\Wallet;
use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Winefing\ApiBundle\Entity\User;
use Doctrine\ORM\EntityManager;

class LemonWayController
{
    protected $userManager;
    const DIRECTKIT_WS = 'https://sandbox-api.lemonway.fr/mb/winefing/dev/directkitxml/service.asmx';
    const INFORMATIONS = ["wlLogin"=> 'society', "wlPass" => "123456", "language" => 'fr', "version"=>1.0, "walletIp"=> '82.228.78.5', 'walletUa'=> 'ua'];

    public function __construct(UserManager $userManager){
        $this->userManager = $userManager;
    }
    /**
     *
     */
    public function addWallet($userId) {
        $user = $this->getUser($userId);
        $client = new \Soapclient($this::DIRECTKIT_WS."?wsdl", array('exceptions'=>true, 'trace' => true));
        var_dump($user->getId());
        $wallet['wallet'] = rand();
        $wallet['clientMail'] = $user->getEmail();
        $wallet['clientFirstName'] = $user->getFirstName();
        $wallet['clientLastName'] = $user->getLastName().'www';
        $response = $client->RegisterWallet(array_merge($this::INFORMATIONS, $wallet));
        $array = json_decode(json_encode($response), True);
        if(!empty(array_key_exists("WALLET", $array))) {
            $wallet = $array['RegisterWalletResult']['WALLET'];
            $this->updateUser($user, $wallet['ID']);
        } else {
            return $array['RegisterWalletResult']['E'];
        }
    }
    public function getUser($id) {
        $user = $this->userManager->findUserBy(array('id'=>$id));
        return $user;
    }
    public function updateUser(User $user, $wallet) {
        $user->setWallet($wallet);
        $this->userManager->updateUser($user);
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
    public function setUserWallet($wallet, $user) {
        $wallet->setWallet($user->getId());
        $wallet->setClientMail($user->getEmail());
        $wallet->setClientTitle($user->getSex());
        $wallet->setClientFirstName($user->getFirstName());
        $wallet->setClientLastName($user->getLastName());
        $wallet->setPhoneNumber($user->getPhoneNumber());
        $wallet->setMobileNumber($user->getPhoneNumber());
    }

    /**
     * Set the billing address
     * @param Wallet $wallet
     * @param User $user
     */
    public function setAddressWallet($wallet, $user) {
        $wallet->setStreet('');
        $wallet->setPostCode('');
        $wallet->setCity('');
    }

    /**
     *If the user is a Host, we have to indicate other informations (about the company).
     * @param Wallet $wallet
     * @param User $user
     */
    public function setCompanyWallet($wallet, $user){
        $wallet->setIsCompany(1);
        $wallet->setCompanyName($user->getDomains()[0]->getName());
        $wallet->setCompanyDescription($user->getDomains()[0]->getDescription());
        $wallet->setPayerOrBeneficiary(2);
    }
}