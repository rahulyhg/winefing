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
use Winefing\ApiBundle\Entity\User;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use AppBundle\Form\DomainRegistrationType;
use AppBundle\Form\DomainNewType;
use Winefing\ApiBundle\Entity\Domain;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\EventDispatcher\EventDispatcher;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
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
        if($form->isSubmitted() && $form->isValid()) {
            $body = $request->request->get('user_registration');
            $body['roles'] = UserGroupEnum::User;
            $body['email'] = $request->request->get('user_registration')['email']['first'];
            if($this->emailExist($body['email'])) {
                $form->get('email')['first']->addError(new FormError($this->get('translator')->trans('error.email_existing')));
            } else {
                $body['password'] = $request->request->get('user_registration')['password']['first'];
                $user = $this->submitUser($body);

                //create the wallet on lemon way
                try {
                    $this->createWallet($user);
                } catch(\Exception $e) {
                    $logger = $this->get('logger');
                    $logger->critical($e->getMessage());
                }

                //login user
                $this->logIn($user);
                return $this->redirectToRoute('home');
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
        $domain = new Domain();
        $domainForm =  $this->createForm(DomainRegistrationType::class, $domain);
        $return = array();
        $return['domainForm'] = $domainForm->createView();
        $domainForm->handleRequest($request);
        if($domainForm->isSubmitted() && $domainForm->isValid()) {
            if(!$this->emailExist($domainForm->get('user')['email']->getData())) {
                //submit address
                $address = $request->request->get('domain_registration')['address'];
                $geocoder = $this->container->get('winefing.geocoder_controller');
                $coordinate = $geocoder->geocode($address['formattedAddress']);

                //get the address's lat and lng
                if(!($coordinate)) {
                    $this->addFlash('contactError', $this->get('translator')->trans('error.address_not_correct'));
                } else {
                    $address['lat'] = $coordinate[0];
                    $address['lng'] = $coordinate[1];
                }
                $address = $this->submitAddress($address);
                //submit user
                $user = $request->request->get('domain_registration')['user'];
                $user['roles'] = UserGroupEnum::Host;
                $user['password'] = $user['password']['first'];
                $user['email'] = $user['email']['first'];
                $user = $this->submitUser($user);

                //submit domain
                $domainNew['name'] = $request->request->get('domain_registration')['name'];
                $domainNew['wineRegion'] = $request->request->get('domain_registration')['wineRegion'];
                $domainNew["address"] = $address->getId();
                $domainNew["user"] = $user->getId();
                $domain = $this->submitDomain($domainNew);

                //create the wallet on lemon way
                try {
                    $this->createWallet($user);
                } catch(\Exception $e) {
                    $logger = $this->get('logger');
                    $logger->critical($e->getMessage());
                }
                $body['user'] = $user->getId();

                //create the subscription
                if(array_key_exists('subscription', $request->request->get('domain_registration'))) {
                    $this->submitAllSubscriptions($body);
                }
                //send email of welcoming
                $this->sendEmailRegistration($body);

                //login user
                $this->logIn($user, $request);
                $this->addFlash('success', $this->get('translator')->trans('success.account_well_created'));
                return $this->redirectToRoute('domain_edit', array('id'=>$domain->getId()));
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
    public function logIn($user, $request) {
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get("security.token_storage")->setToken($token);

        // Fire the login event
        // Logging the user in above the way we do it doesn't do this automatically
        $event = new InteractiveLoginEvent($request, $token);
        $this->get("event_dispatcher")->dispatch("security.interactive_login", $event);
//        if($user->isHost()) {
            //save the domain id parameter
            $api = $this->container->get('winefing.api_controller');
            $serializer = $this->container->get("jms_serializer");
            $response = $api->get($this->get('router')->generate('api_get_domain_by_user', array('userId' => $user->getId())));
            $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
            $this->get('session')->set('domainId', $domain->getId());
//        }
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