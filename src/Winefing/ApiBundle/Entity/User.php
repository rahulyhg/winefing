<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 30/06/2016
 * Time: 19:03
 */

namespace Winefing\ApiBundle\Entity;

use Symfony\Component\Security\Core\User\UserInterface;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;
use JMS\Serializer\Annotation\Groups;


/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User implements UserInterface, \Serializable
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Groups({"id", "default"})
     */
    protected $id;

    /**
     * @var CreditCards
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\CreditCard", mappedBy="user", fetch="EXTRA_LAZY", cascade="ALL")
     * @Groups({"creditCards"})
     */
    private $creditCards;


    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(name="is_active", type="boolean")
     */
    private $isActive;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"default"})
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"default"})
     */
    private $roles;

    /**
     * @ORM\Column(type="string", length=255, nullable=false)
     * @Groups({"default"})
     */
    private $email;

    /**
     * @ORM\Column(type="boolean", nullable=false)
     * @Groups({"default"})
     */
    private $enabled = 1;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     * @Groups({"default"})
     */
    private $lastLogin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"token"})
     */
    private $token;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     * @Groups({"default"})
     */
    protected $lastName;

    /**
     * @ORM\Column(name="phone_number", type="string", length=50, nullable=true)
     * @Groups({"default"})
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(name="verify", type="boolean")
     * @Groups({"default"})
     */
    protected $verify = 0;

    /**
     * @ORM\Column(name="birth_date", type="date", length=255, nullable=true)
     * @Groups({"default"})
     */
    protected $birthDate;

    /**
     * @ORM\Column(name="sex", type="string", length=1, nullable=true)
     * @Groups({"default"})
     */
    protected $sex;

    /**
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     * @Groups({"default"})
     */
    protected $description;

    /**
     * @ORM\Column(name="picture", type="string", length=500, nullable=true)
     * @Groups({"default"})
     */
    protected $picture;

    /**
     * @var domains
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="user", fetch="EXTRA_LAZY")
     * @Groups({"domains"})
     */
    private $domains;

    /**
     * @Type("boolean")
     * @Groups({"default"})
     */
    protected $deleted = false;
    /**
     * @var string
     * @Type("string")
     * @Groups({"default"})
     */
    protected $fullName;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Subscription", inversedBy="users", cascade={"persist", "merge", "detach"})
     * @Groups({"default"})
     */
    private $subscriptions;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", fetch="EXTRA_LAZY")
     * @Groups({"wineList"})
     */
    private $wineList;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Address", fetch="LAZY", cascade={"persist", "merge", "detach"})
     * @Groups({"addresses"})
     */
    private $addresses;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Box", fetch="EXTRA_LAZY")
     * @Groups({"boxList"})
     */
    private $boxList;

    /**
     * @ORM\Column(name="wallet", type="boolean")
     * @Groups({"default"})
     */
    protected $wallet = 0;

    /**
     * @ORM\Column(name="emailVerify", type="boolean")
     * @Groups({"default"})
     */
    protected $emailVerify = 0;

    /**
     * @ORM\Column(name="phoneNumberVerify", type="boolean")
     * @Groups({"default"})
     */
    protected $phoneNumberVerify = 0;

    public function __construct()
    {
        $this->subscriptions = new ArrayCollection();
        $this->domains = new ArrayCollection();
        $this->wineList = new ArrayCollection();
        $this->boxList = new ArrayCollection();
        $this->addresses = new ArrayCollection();
        $this->isActive = true;
        $this->creditCards = new ArrayCollection();

        return $this;
    }

    public function getUsername()
    {
        return $this->username;
    }

    /**
     * @param mixed $username
     */
    public function setUsername($username)
    {
        $this->username = $username;
    }

    public function eraseCredentials()
    {
    }

    public function getSalt()
    {
        // you *may* need a real salt depending on your encoder
        // see section on salt below
        return null;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * @param mixed $password
     */
    public function setPassword($password)
    {
        $this->password = $password;
    }

    /**
     * @return mixed
     */
    public function getRoles()
    {
        return explode(',', $this->roles);
    }

    /**
     * @param mixed $roles
     */
    public function setRoles($roles)
    {
        $this->roles = $roles;
    }

    /**
     * @return mixed
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * @param mixed $email
     */
    public function setEmail($email)
    {
        $this->email = $email;
    }

    /**
     * @return mixed
     */
    public function getEnabled()
    {
        return $this->enabled;
    }

    /**
     * @param mixed $enabled
     */
    public function setEnabled($enabled)
    {
        $this->enabled = $enabled;
    }

    /**
     * @return mixed
     */
    public function getLastLogin()
    {
        return $this->lastLogin;
    }

    /**
     * @param mixed $lastLogin
     */
    public function setLastLogin(\DateTime $lastLogin)
    {
        $this->lastLogin = $lastLogin;
    }

    /**
     * @return mixed
     */
    public function getToken()
    {
        return $this->token;
    }

    /**
     * @param mixed $token
     */
    public function setToken($token)
    {
        $this->token = $token;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getBirthDate()
    {
        return $this->birthDate;
    }

    /**
     * @param mixed $birthDate
     */
    public function setBirthDate($birthDate)
    {
        $this->birthDate = $birthDate;
    }

    /**
     * @return mixed
     */
    public function getVerify()
    {
        return $this->verify;
    }

    /**
     * @param mixed $verify
     */
    public function setVerify($verify)
    {
        $this->verify = $verify;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumber()
    {
        return $this->phoneNumber;
    }

    /**
     * @param mixed $phoneNumber
     */
    public function setPhoneNumber($phoneNumber)
    {
        $this->phoneNumber = $phoneNumber;
    }

    /**
     * @return mixed
     */
    public function getLastName()
    {
        return $this->lastName;
    }

    /**
     * @param mixed $lastName
     */
    public function setLastName($lastName)
    {
        $this->lastName = $lastName;
    }

    /**
     * @return mixed
     */
    public function getFirstName()
    {
        return $this->firstName;
    }

    /**
     * @param mixed $firstName
     */
    public function setFirstName($firstName)
    {
        $this->firstName = $firstName;
    }

    /**
     * @return mixed
     */
    public function getFullName()
    {
        return $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * @param null
     */
    public function setFullName()
    {
        $this->fullName = $this->getFirstName().' '.$this->getLastName();
    }

    /**
     * @return boolean
     */
    public function isDeleted()
    {
        return $this->deleted;
    }

    /**
     * @param boolean $delete
     */
    public function setDeleted($deleted)
    {
        $this->deleted = $deleted;
    }

    /**
     * @return mixed
     */
    public function getSubscriptions()
    {
        return $this->subscriptions;
    }

    /**
     * @param mixed $subscriptions
     */
    public function setSubscriptions($subscriptions)
    {
        $this->subscriptions = $subscriptions;
    }

    /**
     * @return mixed
     */
    public function getPicture()
    {
        if(empty($this->picture)) {
            $this->picture = 'default.png';
        }
        return $this->picture;
    }

    /**
     * @param mixed $picture
     */
    public function setPicture($picture)
    {
        $this->picture = $picture;
    }

    /**
     * @return domains
     */
    public function getDomains()
    {
        return $this->domains;
    }

    /**
     * @return mixed
     */
    public function getSex()
    {
        return $this->sex;
    }

    /**
     * @param mixed $sex
     */
    public function setSex($sex)
    {
        $this->sex = $sex;
    }

    public function resetSubscriptions() {
        $this->subscriptions = new ArrayCollection();
        return $this;
    }
    public function addSubscription(Subscription $subscription) {
        $this->subscriptions[] = $subscription;
        return $this;
    }
    public function isHost() {
        $success = false;
//        if(in_array(UserGroupEnum::Host, $this->roles)) {
//            $success = true;
//        }
        return $success;
    }
    public function isAdmin() {
        $success = false;
        if((UserGroupEnum::Blog == $this->roles) || (UserGroupEnum::Managment == $this->roles) || (UserGroupEnum::Technical == $this->roles)) {
            $success = true;
        }
        return $success;
    }

    /**
     * @return mixed
     */
    public function getWineList()
    {
        return $this->wineList;
    }

    /**
     * @return mixed
     */
    public function getBoxList()
    {
        return $this->boxList;
    }

    public function addElementInWineList(Domain $domain) {
        $this->wineList[] = $domain;
    }

    public function addElementInBoxList(Box $box) {
        $this->boxList[] = $box;
    }
    /** @see \Serializable::serialize() */
    public function serialize()
    {
        return serialize(array(
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt,
        ));
    }

    /** @see \Serializable::unserialize() */
    public function unserialize($serialized)
    {
        list (
            $this->id,
            $this->email,
            $this->password,
            // see section on salt below
            // $this->salt
            ) = unserialize($serialized);
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->isActive;
    }

    /**
     * @param mixed $isActive
     */
    public function setIsActive($isActive)
    {
        $this->isActive = $isActive;
    }

    /**
     * @return mixed
     */
    public function getWallet()
    {
        return $this->wallet;
    }

    /**
     * @param mixed $wallet
     */
    public function setWallet($wallet)
    {
        $this->wallet = $wallet;
    }

    /**
     * @return mixed
     */
    public function getAddresses()
    {
        return $this->addresses;
    }

    /**
     * @param mixed $addresses
     */
    public function setAddresses($addresses)
    {
        $this->addresses = $addresses;
    }

    /**
     * @return mixed
     */
    public function getCreditCards()
    {
        return $this->creditCards;
    }

    /**
     * @param mixed $creditCards
     */
    public function setCreditCards($creditCards)
    {
        $this->creditCards = $creditCards;
    }

    public function addCreditCard(CreditCard $creditCard){
        $this->creditCards[] = $creditCard;
    }

    /**
     * @return mixed
     */
    public function getEmailVerify()
    {
        return $this->emailVerify;
    }

    /**
     * @param mixed $emailVerify
     */
    public function setEmailVerify($emailVerify)
    {
        $this->emailVerify = $emailVerify;
    }

    /**
     * @return mixed
     */
    public function getPhoneNumberVerify()
    {
        return $this->phoneNumberVerify;
    }

    /**
     * @param mixed $phoneNumberVerify
     */
    public function setPhoneNumberVerify($phoneNumberVerify)
    {
        $this->phoneNumberVerify = $phoneNumberVerify;
    }

}