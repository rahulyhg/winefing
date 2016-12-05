<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 15/09/2016
 * Time: 14:51
 */
namespace Winefing\ApiBundle\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpFoundation\Response;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Winefing\ApiBundle\Entity\ScopeEnum;


class MissingCharacteristicController extends Controller
{

    public function getMissingCharacteristics($property, $scopeName)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        if (!empty($property->getId())) {
            switch($scopeName) {
                case ScopeEnum::Property :
                    $response = $api->get($this->get('_router')->generate('api_get_property_missing_characteristics', array('propertyId' => $property->getId())));
                case ScopeEnum::Domain :
                    $response = $api->get($this->get('_router')->generate('api_get_domain_missing_characteristics', array('domainId' => $property->getId())));
                case ScopeEnum::Rental :
                    $response = $api->get($this->get('_router')->generate('api_get_rental_missing_characteristics', array('propertyId' => $property->getId())));
            }
        } else {
            $response = $api->get($this->get('_router')->generate('api_get_characteristics', array('scopeName' => $scopeName)));
        }
        $missingCharacteristics = $serializer->decode($response->getBody()->getContents());
        $this->addAllMissingCharacteristics($property, $missingCharacteristics);
        return $this->getCharacteristicCategories($property);
    }

    public function addAllMissingCharacteristics($property, $missingCharacteristics) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicValue = new CharacteristicValue();
            $characteristic = $repository->findOneById($characteristic["id"]);
            $characteristicValue->setCharacteristic($characteristic);
            $property->addCharacteristicValue($characteristicValue);
        }
    }

    public function getCharacteristicCategories($property)
    {
        $list = array();
        foreach ($property->getCharacteristicValues() as $characteristicValue) {
            $list[$characteristicValue
                ->getCharacteristic()
                ->getCharacteristicCategory()
                ->getCharacteristicCategoryTrs()[0]
                ->getName()][]
                = $characteristicValue;
        }
        return $list;
    }
}

