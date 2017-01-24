<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 12/07/2016
 * Time: 19:30
 */

namespace Winefing\UserBundle\Controller;

use AppBundle\Form\UserRegistrationType;
use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Form\FormError;
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use AppBundle\Form\DomainRegistrationType;
use AppBundle\Form\DomainNewType;
use Winefing\ApiBundle\Entity\Domain;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class RegistrationController extends Controller
{
    public function testMailIsSentAndContentIsOk()
    {
        $client = static::createClient();

        // Enable the profiler for the next request (it does nothing if the profiler is not available)
        $client->enableProfiler();

        $crawler = $client->request('POST', '/path/to/above/action');

        $mailCollector = $client->getProfile()->getCollector('swiftmailer');

        // Check that an email was sent
        $this->assertEquals(1, $mailCollector->getMessageCount());

        $collectedMessages = $mailCollector->getMessages();
        $message = $collectedMessages[0];

        // Asserting email data
        $this->assertInstanceOf('Swift_Message', $message);
        $this->assertEquals('Hello Email', $message->getSubject());
        $this->assertEquals('send@example.com', key($message->getFrom()));
        $this->assertEquals('recipient@example.com', key($message->getTo()));
        $this->assertEquals(
            'You should see me from the profiler!',
            $message->getBody()
        );
    }
    /**
     * @Route("/registration", name="registration")
     */
    public function userAction(Request $request) {
        $user = new User();
        $form =$this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);
        if($request->isMethod('POST') && !$form->isSubmitted()) {
            $userRegistration = $request->request->get('user');
            $form->get('firstName')->setData($userRegistration['firstName']);
            $form->get('lastName')->setData($userRegistration['lastName']);
            $form->get('email')['first']->setData($userRegistration['email']['first']);
            $form->get('email')['second']->setData($userRegistration['email']['second']);
            $form->get('password')['first']->setData($userRegistration['password']['first']);
            $form->get('password')['second']->setData($userRegistration['password']['second']);
            $form->submit($userRegistration);
        }
        if($form->isSubmitted() && $form->isValid()) {
            $body['email'] = $request->request->get('user_registration')['email']['first'];
            if($this->emailExist($body['email'])) {
                $form->get('email')['first']->addError(new FormError($this->get('translator')->trans('error.email_existing')));
            } else {
                $body['roles'] = UserGroupEnum::User;
                $body['password'] = $request->request->get('user_registration')['password']['first'];
                $this->submitUser($body);
            }

        }
        return $this->render('user/registration.html.twig', array(
            'user' => $form->createView()
        ));
    }

    /**
     * @Route("/registration/host", name="registration_host")
     */
    public function submitAction(Request $request)
    {
        $body['user'] = 90;
        $body['language'] = $request->getLocale();
        $this->sendEmailRegistration($body);

        $domain = new Domain();
        $domainForm =  $this->createForm(DomainRegistrationType::class, $domain);
        $return = array();
        $return['domainForm'] = $domainForm->createView();
        $domainForm->handleRequest($request);
        if($domainForm->isSubmitted() && $domainForm->isValid()) {
            if(!$this->emailExist($domainForm->get('user')['email']->getData())) {
                //submit user
                $user = $request->request->get('domain_registration')['user'];
                $user['roles'] = UserGroupEnum::Host;
                $user['password'] = $user['password']['first'];
                $user['email'] = $user['email']['first'];
                $user = $this->submitUser($user);

                //submit address
                $address = $request->request->get('domain_registration')['address'];
                $coordinate = $this->geocode($address['formattedAddress']);
                //get the address's lat and lng
                if(!($coordinate)) {
                    $this->addFlash('contactError', $this->get('translator')->trans('error.address_not_correct'));
                } else {
                    $address['lat'] = $coordinate[0];
                    $address['lng'] = $coordinate[1];
                }
                $address = $this->submitAddress($address);

                //submit domain
                $domainNew['name'] = $request->request->get('domain_registration')['name'];
                $domainNew['wineRegion'] = $request->request->get('domain_registration')['wineRegion'];
                $domainNew["address"] = $address->getId();
                $domainNew["user"] = $user->getId();
                $this->submitDomain($domainNew);

                //create the wallet on lemon way
                $this->createWallet($user);


                $body['user'] = $user->getId();
                //create the subscription
                var_dump($request->request->get('domain_registration'));
                if(array_key_exists('subscription', $request->request->get('domain_registration'))) {
                    $this->submitAllSubscriptions($body);
                }
                //send email of welcoming
                $this->sendEmailRegistration($body);


                //login user
                $this->logIn($user);
                return $this->redirectToRoute('home');
            } else {
                $this->addFlash('contactError', $this->get('translator')->trans('error.email_already_exist'));
            }
        }
        return $this->render('host/user/new.html.twig', $return);
    }
    public function sendEmailRegistration($body) {
        $api = $this->container->get('winefing.api_controller');
        $api->post($this->get('_router')->generate('api_post_email_registration'), $body);
    }

    /**
     * If the user want to subscribe to the newsletter, all the king of newsletter is associated with the user.
     * @param $body
     */
    public function submitAllSubscriptions($body) {
        $api = $this->container->get('winefing.api_controller');
        $api->patch($this->get('_router')->generate('api_patch_subscription_user'), $body);
    }
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

    /**
     * Check if there is already an account with this email
     * @param $email
     * @return bool
     */
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
    public function submitUser($user)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("jms_serializer");
        $response =  $api->post($this->get('router')->generate('api_post_user'), $user);
        $user = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\User', 'json');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        return $repository->findOneById($user->getId());
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
    public function logIn($user) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('security.token_storage')->setToken($token);
        $this->get('session')->set('_security_main', serialize($token));
    }

    /**
     * Get the address's lat and lng. Return false if nothing find
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
    /**
     * Permet de créer un wallet sur Lemon Way si ce dernier n'a pas déjà été créé.
     * @param $user
     * @throws \Exception
     */
    public function createWallet($user) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        if(!$user->getWallet()) {
            $error = $lemonWay->addWallet($user);
            if (!empty($error)) {
                throw new \Exception('Problem during the creatin of the wallet of the user '.$user->getId().''.$error);
            }
        }
    }
}