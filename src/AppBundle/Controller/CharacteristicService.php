<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 18/10/2016
 * Time: 15:41
 */

namespace AppBundle\Controller;


use Symfony\Component\HttpKernel\Bundle\BundleInterface;
use Winefing\ApiBundle\Entity\ScopeEnum;
use Winefing\ApiBundle\Controller\ApiController as Api;
use Symfony\Component\Routing\Router as Router;
use JMS\Serializer\Serializer;

class CharacteristicService
{
    protected $api;
    protected $rooter;
    protected $serializer;

    public function __construct(Api $api, Router $router, Serializer $serializer)
    {
        $this->api = $api;
        $this->rooter = $router;
        $this->serializer = $serializer;
    }

    /**
     * Get path
     *
     * @return string Resolved path
     */
    public function getByCharacteristicCategory($characteristicValues)
    {
        //get all the characteristic Value by characteristic category
        $list = array();
        foreach($characteristicValues as $characteristicValue) {
            $list[$characteristicValue->getCharacteristic()->getCharacteristicCategory()->getName()][] = $characteristicValue;
        };
        return $list;
    }
    /**
     * @param $characteristicValueForm
     */
    public function submitCharacteristicValues($characteristicValueForm, $scope) {
        $object = $characteristicValueForm[strtolower($scope)];
        foreach($characteristicValueForm["characteristicValue"] as $characteristicValue) {
            if (empty($characteristicValue["id"])) {

                //save in data base the value 0 for boolean, but not empty value if the characteristic is not a boolean.
                if(($characteristicValue["value"] == "1") || ($characteristicValue["value"] == "0") || $characteristicValue["value"]) {

                    //post characteristic value
                    $response = $this->api->post($this->rooter->generate('api_post_characteristic_value'), $characteristicValue);
                    $characteristicValue = $this->serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\CharacteristicValue', 'json');

                    //patch between the characteristic and the object
                    $characteristicValueProperty[strtolower($scope)] = $object;
                    $characteristicValueProperty["characteristicValue"] = $characteristicValue->getId();
                    switch($scope) {
                        case ScopeEnum::Domain:
                            $this->api->put($this->rooter->generate('api_put_characteristic_value_domain'), $characteristicValueProperty);
                            break;
                        case ScopeEnum::Property:
                            $this->api->put($this->rooter->generate('api_put_characteristic_value_property'), $characteristicValueProperty);
                            break;
                        case ScopeEnum::Rental:
                            $this->api->put($this->rooter->generate('api_put_characteristic_value_rental'), $characteristicValueProperty);
                            break;
                    }
                }
            } elseif(empty($characteristicValue["value"])) {
                //if the user decide to dele an no boolean characteristic value, we delete this characteristic.
                $this->api->delete($this->rooter->generate('api_delete_characteristic_value', array('id'=>$characteristicValue["id"])));
            } else {
                // edit the characteristic value.
                $this->api->put($this->rooter->generate('api_put_characteristic_value'), $characteristicValue);
            }

        }
    }
}