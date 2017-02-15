<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 09/02/2017
 * Time: 17:37
 */

namespace Winefing\ApiBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\Annotation\Groups;
use JMS\Serializer\Annotation\Type;
/**
 * This class allows to display three picture of a domain : one picture of the domain, one of the domain'properties, and one of the domain'properties'rentals.
 * Class DomainMediaPresentation
 * @package Winefing\ApiBundle\Entity
 */
class DomainMediasPresentation
{
    /**
     * @Groups({"default"})
     * @Type("string")
     */
    private $domainMediaPresentation;

    /**
     * @Groups({"default"})
     * @Type("string")
     */
    private $propertyMediaPresentation;

    /**
     * @Groups({"default"})
     * @Type("string")
     */
    private $rentalMediaPresentation;

    public function __construct(Domain $domain)
    {
        //get the presentation image of the domain
        $domain->setMediaPresentation();
        $this->domainMediaPresentation = $domain->getMediaPresentation();
        $property = $domain->getProperties()[0];

        //get the presentation image of the property
        $property->setMediaPresentation();
        $this->propertyMediaPresentation = $property->getMediaPresentation();

        //get the presentation image of a location
        $rental = $property->getRentals()[0];
        $rental->setMediaPresentation();
        $this->rentalMediaPresentation = $rental->getMediaPresentation();
    }

    /**
     * @return mixed
     */
    public function getDomainMediaPresentation()
    {
        return $this->domainMediaPresentation;
    }

    /**
     * @param mixed $domainMediaPresentation
     */
    public function setDomainMediaPresentation($domainMediaPresentation)
    {
        $this->domainMediaPresentation = $domainMediaPresentation;
    }

    /**
     * @return mixed
     */
    public function getPropertyMediaPresentation()
    {
        return $this->propertyMediaPresentation;
    }

    /**
     * @param mixed $propertyMediaPresentation
     */
    public function setPropertyMediaPresentation($propertyMediaPresentation)
    {
        $this->propertyMediaPresentation = $propertyMediaPresentation;
    }

    /**
     * @return mixed
     */
    public function getRentalMediaPresentation()
    {
        return $this->rentalMediaPresentation;
    }

    /**
     * @param mixed $rentalMediaPresentation
     */
    public function setRentalMediaPresentation($rentalMediaPresentation)
    {
        $this->rentalMediaPresentation = $rentalMediaPresentation;
    }

}