<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 19:17
 */

namespace Winefing\UserBundle\Controller;
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
use Winefing\ApiBundle\Entity\UserForm;
use Winefing\ApiBundle\Entity\User;
use Winefing\ApiBundle\Entity\Domain;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class HostController extends Controller
{
    /**
     * @Route("/user/host/new/{step}/{id}", name="user_host_new")
     */
    public function submitAction($step, $id = '', Request $request)
    {
        $return = array();
        if($step == 'contact') {
            $user = new User();
            $userForm =  $this->createForm(HostUserRegistrationType::class, $user);
            $return["userForm"] = $userForm->createView();
            $userForm->handleRequest($request);
            if($userForm->isSubmitted() && $userForm->isValid()) {
                $userEdit = $request->request->get('host_user_registration');
                $userEdit["email"] = $userEdit["email"]["first"];
                if(!$this->emailExist($userEdit["email"])) {
                    $userEdit["password"] = $userEdit["password"]["first"];
                    $userEdit["id"] = $user->getId();
                    $user = $this->submit($userEdit);
                    return $this->redirect($this->generateUrl('user_host_new', array('step' => 'domain', 'id' => $user->getId())) . '#domain');
                } else {
                    $this->addFlash('contactError', $this->get('translator')->trans('error.email_already_exist'));
                }
            }
        }
        if($step == 'domain') {
            $domainForm = $this->createForm(DomainNewType::class, new Domain());
            $return["domainForm"] = $domainForm->createView();
            $domainForm->handleRequest($request);
            if ($domainForm->isSubmitted() && $domainForm->isValid()) {
                if(!$this->userHasDomain($id)) {
                    $domainNew = $request->request->get('domain_new');
                    $domainNew["user"] = $id;
                    $addressForm = $domainNew["address"];
                    $address = $this->submitAddress($addressForm);
                    $domainNew["address"] = $address->getId();
                    $domain = $this->submitDomain($domainNew);
                    return $this->redirect($this->generateUrl('domain_edit', array('userId'=> $id)) . '#presentation');
                } else {
                    $this->addFlash('domainError', $this->get('translator')->trans('error.domain_already_exist'));
                }
            }
        }
        return $this->render('host/user/new.html.twig', $return);
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
    public function userHasDomain($userId) {
        $result = false;
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        if($response->getBody()->getContents() != 'null') {
            $result = true;
        }
        return $result;
    }
    public function emailExist($email) {
        $result = false;
        $api = $this->container->get('winefing.api_controller');
        $response =  $api->get($this->get('router')->generate('api_get_user_by_email', array('email' => $email)));
        if($response->getBody()->getContents() != 'null') {
            $result = true;
        }
        return $result;
    }

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
        $api->put($this->get('router')->generate('api_put_user_password'), $password);
    }

    public function submitSubscriptions($subscription)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_user_subscriptions'), $subscription);
    }
}