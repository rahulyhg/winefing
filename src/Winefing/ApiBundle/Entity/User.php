<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 30/06/2016
 * Time: 19:03
 */

namespace Winefing\ApiBundle\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Type;

/**
 * @ORM\Entity
 * @ORM\Entity(repositoryClass="Winefing\ApiBundle\Repository\UserRepository")
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{
    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;
    /**
     * @ORM\Column(name="first_name", type="string", length=255, nullable=true)
     */
    protected $firstName;

    /**
     * @ORM\Column(name="last_name", type="string", length=255, nullable=true)
     */
    protected $lastName;

    /**
     * @ORM\Column(name="phone_number", type="string", length=50, nullable=true)
     */
    protected $phoneNumber;

    /**
     * @ORM\Column(name="verify", type="string", length=255, nullable=true)
     */
    protected $verify;

    /**
     * @ORM\Column(name="birth_date", type="date", length=255, nullable=true)
     */
    protected $birthDate;

    /**
     * @ORM\Column(name="sex", type="string", length=1, nullable=true)
     */
    protected $sex;

    /**
     * @ORM\Column(name="description", type="string", length=500, nullable=true)
     */
    protected $description;

    /**
     * @ORM\Column(name="picture", type="string", length=500, nullable=true)
     */
    protected $picture;

    /**
     * @var domains
     * @ORM\OneToMany(targetEntity="Winefing\ApiBundle\Entity\Domain", mappedBy="address")
     */
    private $domains;

    /**
     * @Type("boolean")
     */
    protected $deleted = false;
    /**
     * @var string
     * @Type("string")
     */
    protected $fullName;

    /**
     * @ORM\ManyToMany(targetEntity="Winefing\ApiBundle\Entity\Subscription", inversedBy="users", cascade={"persist", "merge", "detach"})
     */
    private $subscriptions;

    public function __construct()
    {
        parent::__construct();
        $this->subscriptions = new ArrayCollection();
        $this->domains = new ArrayCollection();
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param mixed $id
     */
    public function setId($id)
    {
        $this->id = $id;
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
     * @return mixed
     */
    public function getPicture()
    {
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
}