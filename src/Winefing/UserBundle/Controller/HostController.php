<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 19:17
 */

namespace Winefing\UserBundle\Controller;
use AppBundle\Form\DomainRegistrationType;
use AppBundle\Form\UserType;
use AppBundle\Form\DomainNewType;
use AppBundle\Form\HostUserRegistrationType;
use AppBundle\Form\PasswordEditType;
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
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use Winefing\ApiBundle\Entity\UserForm;
use Winefing\ApiBundle\Entity\User;
use Winefing\ApiBundle\Entity\Domain;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class HostController extends Controller
{

    public function submitDomain($domain) {
        $serializer = $this->container->get("jms_serializer");
        $api = $this->container->get('winefing.api_controller');
        $response = $api->post($this->get('_router')->generate('api_post_domain'), $domain);
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $domain;
    }
    public function submitAddress($address) {
        $serializer = $this->container->get("jms_serializer");
        $api = $this->container->get('winefing.api_controller');
        $response = $api->post($this->get('_router')->generate('api_post_address'), $address);
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $address;
    }
    public function emailExist($email) {
        $result = false;
        $api = $this->container->get('winefing.api_controller');
        $response =  $api->get($this->get('router')->generate('api_get_user_by_email', array('email' => $email)));
        if($response->getStatusCode() != StatusCodeEnum::empty_response) {
            $result = true;
        }
        return $result;
    }

    /**
     * New Host User
     * @param $user
     * @return mixed
     */
    public function submit($user)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("jms_serializer");
        if(!empty($user["id"])) {
            $response =  $api->put($this->get('router')->generate('api_put_user'), $user);
        } else {
            $response =  $api->post($this->get('router')->generate('api_post_user'), $user);
        }
        $user = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\User', 'json');
        return $user;
    }

    public function submitPicture($picture, $user)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->file($this->get('router')->generate('api_post_user_picture'), $user, $picture);
    }

    public function submitPassword($password)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->patch($this->get('router')->generate('api_patch_user_password'), $password);
    }

    public function submitSubscriptions($subscription)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->patch($this->get('router')->generate('api_patch_user_subscriptions'), $subscription);
    }

    /**
     * Get the address's lat and lng. Re
     * @param $address
     * @return array|bool
     */
    function geocode($address){

        // url encode the address
        $address = urlencode($address);

        // google map geocode api url
        $url = "http://maps.google.com/maps/api/geocode/json?address={$address}";

        // get the json response
        $resp_json = file_get_contents($url);

        // decode the json
        $resp = json_decode($resp_json, true);

        // response status will be 'OK', if able to geocode given address
        if($resp['status']=='OK'){
            // get the important data
            $lati = $resp['results'][0]['geometry']['location']['lat'];
            $longi = $resp['results'][0]['geometry']['location']['lng'];
            $formatted_address = $resp['results'][0]['formatted_address'];

            // verify if data is complete
            if($lati && $longi && $formatted_address){

                // put the data in the array
                $data_arr = array();

                array_push(
                    $data_arr,
                    $lati,
                    $longi,
                    $formatted_address
                );

                return $data_arr;

            }else{
                return false;
            }

        }else{
            return false;
        }
    }
}
