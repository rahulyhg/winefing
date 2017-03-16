<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 30/12/2016
 * Time: 10:06
 */

namespace Winefing\UserBundle\Controller;
use AppBundle\Form\ChangePasswordType;
use Winefing\ApiBundle\Entity\ChangePassword;
use AppBundle\Form\PasswordEditType;
use AppBundle\Form\PictureType;
use AppBundle\Form\UserType;
use AppBundle\Form\IbanType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Form\FormError;
use Symfony\Component\Security\Core\Encoder\EncoderFactoryInterface;

class ManagerController extends Controller
{
    /**
     * @Route("/admin/users/{group}", name="users_by_group")
     */
    public function cgetAction($group) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_users', array('role' => $group)));
        $users= $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\User>', 'json');
        return $this->render('admin/user/index.html.twig', array(
            'users' => $users
        ));
    }
    /**
     * @Route("wishlist", name="wishlist")
     */
    public function getWishlistAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_domains_wine_list', array('userId' => $this->getUser()->getId(), 'language'=>$request->getLocale())));
        $domains = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Domain>', 'json');
        $response = $api->get($this->get('_router')->generate('api_get_boxes_box_list', array('userId' => $this->getUser()->getId(), 'language'=>$request->getLocale())));
        $boxes = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Box>', 'json');
        return $this->render('user/wishlist.html.twig', array(
            'domains' => $domains,
            'boxes'=> $boxes
        ));
    }
    /**
     *
     * @Route("admin/user/verify", name="user_verify")
     */
    public function verifyUserAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get("id"));
        if($request->request->get("verify") == "1") {
            $error = $this->createWallet($user);
            if(!empty($this->createWallet($user))) {
                throw new \Exception($error);
            }
        }
        $api->put($this->get('_router')->generate('api_put_user_verify'), $request->request->all());
        return new Response();
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
                return $error;
            }
        }
    }

    /**
     * @Route("admin/user/{id}/delete", name="user_delete")
     * @param $id
     */
    public function deleteUser($id, Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        $api = $this->container->get('winefing.api_controller');
//        $response = $api->delete($this->get('_router')->generate('api_delete_user', array('id' => $id)));
//        if(!empty($response->getBody()->getContents())) {
//            $request->getSession()
//                ->getFlashBag()
//                ->add('error', $response->getBody()->getContents());
//        }
//        return $this->redirectToRoute('users_by_group', array('group'=>$user->isAdmin() ? UserGroupEnum::Admin : $user->getRoles()));
    }

    /**
     * @Route("/user/{id}/edit/{nav}", name="user_edit")
     */
    public function getHost($id, $nav = 'profil',Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $return = array();
        $user = $this->getUserById($id);
        $userForm =  $this->createForm(UserType::class, $user);
        if($user->isAdmin()) {
            $userForm->add('facebook', null, array('attr'=>['class'=>'form-control']));
            $userForm->add('twitter', null, array('attr'=>['class'=>'form-control']));
            $userForm->add('instagram', null, array('attr'=>['class'=>'form-control']));
            $userForm->add('google', null, array('attr'=>['class'=>'form-control']));
            $return['admin'] = 'admin';
        }
        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()) {
            $userEdit = $request->request->get('user');
            $userEdit["birthDate"] = strtotime($userEdit["birthDate"]);
            $userEdit["id"] = $user->getId();
            $this->submit($userEdit);
            $this->addFlash('success', $this->get('translator')->trans('success.profil_edit'));
            return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId())) . '#profil');
        }

        // if the user is host, we have to create a new part for the user company bank details.
        if($user->isHost()) {
            $iban = $this->getIban($api, $serializer, $id);
            $ibanForm = $this->createForm(IbanType::class, $iban);
            $ibanForm->handleRequest($request);
            if ($ibanForm->isSubmitted() && $ibanForm->isValid()) {
                $nav = 'iban';

                //edit the company information
                $company['name'] = $ibanForm->get('company')->get('name')->getData();
                $company['id'] = $iban->getCompany()->getId();
                $api->put($this->get('router')->generate('api_put_company'), $company);


                //edit the company address
                $address = $request->request->get('iban')['company']['address'];
                $address['id'] = $iban->getCompany()->getAddress()->getId();
                $api->put($this->get('router')->generate('api_put_address'), $address);

                //edit or create the company iban
                $this->submitIban($api, $user, $iban, $ibanForm);

            }

            $return['ibanForm'] = $ibanForm->createView();
        }

        $passwordForm = $this->createForm(ChangePasswordType::class);
        $passwordForm->handleRequest($request);
        if($passwordForm->isSubmitted()) {
            $nav = 'password';
            $encoder = $this->container->get('security.password_encoder');
            if(!$encoder->isPasswordValid($user, $request->request->get('change_password')["currentPassword"])) {
                $passwordForm->get('currentPassword')->addError(new FormError($this->get('translator')->trans('error.not_current_password')));
                $this->addFlash('error', $this->get('translator')->trans('error.not_current_password'));
            } else {
                $passwordFormNew["password"] = $request->request->get('change_password')["password"]['first'];
                $passwordFormNew['user'] = $user->getId();
                $this->submitPassword($passwordFormNew);
                $this->addFlash('success', $this->get('translator')->trans('success.password_edit'));
                return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId(), 'nav'=>$nav)));
            }
        }


        $response = $api->get($this->get('router')->generate('api_get_subscriptions_by_user', array('user'=> $user->getId(), 'language'=>$request->getLocale())));
        $subscriptions = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Subscription>', 'json');
        $subscriptionFormatList = $this->subscriptionsByFormat($subscriptions, $user);

        //for the picture form
        $pictureForm =  $this->createForm(PictureType::class);
        $pictureForm->handleRequest($request);
        if ($pictureForm->isSubmitted() && $pictureForm->isValid()) {
            $nav = 'picture';
            $picture = $pictureForm->get('picture')->getData();
            if($picture->getClientSize() > $this->getParameter('max_upload_file_size')) {
                $pictureForm->get('picture')->addError(new FormError($this->get('translator')->trans('error.max_file_size', array('%maxSize%'=>'1M'))));
                $this->addFlash('error', $this->get('translator')->trans('error.max_file_size', array('%maxSize%'=>'1M')));
            } else {
                $body["user"] = $user->getId();
                $api->file($this->get('router')->generate('api_post_user_picture'), array('user'=>$id), $picture);
                $this->addFlash('success', $this->get('translator')->trans('success.picture_edit'));
                return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId(), 'nav'=>$nav)));
            }
        }
        //
        if($request->isMethod('POST')) {
            $subscription = $request->request->get('subscriptionForm');
            if($subscription != null) {
                $nav = 'subscriptions';
                $subscription["user"] = $user->getId();
                $this->submitSubscriptions($subscription);
                $this->addFlash('success', $this->get('translator')->trans('success.modifications_saved'));
                return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId(), 'nav'=>$nav)));
            }
        }
        $return['userForm']= $userForm->createView();
        $return['picture']= $user->getPicture();
        $return['pictureForm']= $pictureForm->createView();
        $return['subscriptionFormatList']= $subscriptionFormatList;
        $return['passwordForm']= $passwordForm->createView();
        $return['nav']= $nav;
        return $this->render('userEdit.html.twig', $return);
    }
    public function test($encoder, $user) {
        var_dump($encoder->isPasswordValid($user, 'winefing'));
    }

    /**
     * Create a new iban or submit the edit the information of the old one.
     * @param $api
     * @param $serializer
     * @param $user
     * @param $iban
     * @param $ibanForm
     */
    public function submitIban($api, $user, $iban, &$ibanForm) {
        $ibanNew['bic'] = $ibanForm->get('bic')->getData();
        $ibanNew['iban'] = $ibanForm->get('iban')->getData();
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        if(empty($iban->getId())) {
            //register new iban on lemon way
            $lemonWay->registerIban($user, $ibanForm);
            if($ibanForm->get('iban')->isValid()) {
                $ibanNew['company'] = $iban->getCompany()->getId();
                $api->post($this->get('router')->generate('api_post_iban'), $ibanNew);
            } else {
                $this->addFlash('error', $this->get('translator')->trans('error.generic_form_error'));
            }
            //there is no possibility to edit an iban on lemon way. So we create and edit the iban only if it's different from the one existing in database.
        } elseif(($ibanNew['bic'] != $iban->getBic()) || ($ibanNew['iban'] != $iban->getIban())) {
            $lemonWay->registerIban($user, $ibanForm);
            if($ibanForm->get('iban')->isValid()) {
                $ibanNew['id'] = $iban->getId();
                $api->put($this->get('router')->generate('api_put_iban'), $ibanNew);
            } else {
                $this->addFlash('error', $this->get('translator')->trans('error.generic_form_error'));
            }
        }
    }
    public function getIban($api, $serializer, $userId) {
        $response= $api->get($this->get('_router')->generate('api_get_iban_by_user', array('userId'=>$userId)));
        $iban = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Iban', 'json');
        return $iban;
    }
    public function getTwigView($user) {
        $template = 'admin/userEdit.html.twig';
        if(in_array(UserGroupEnum::Host, $user->getRoles())) {
            $template = 'host/user/edit.html.twig';
        } elseif(in_array(UserGroupEnum::User, $user->getRoles())) {
            $template = 'user/backend/userEdit.html.twig';
        }
        return $template;
    }
    public function subscriptionsByFormat($subscriptions, $user) {
        $subscriptionFormatList = array();
        foreach($subscriptions as $subscription) {
            $subscriptionFormatList[$subscription->getFormat()][] = $subscription;
        }
        return $subscriptionFormatList;
    }

    /**
     * Route define intside the mail received by the user after the registration.
     * @Route("/user/{email}/verify/email", name="email_verify")
     */
    public function verifyEmailAction($email, Request $request) {
        $body['email'] = $email;
        $body['emailVerify'] = 1;
        $api = $this->container->get('winefing.api_controller');
        $api->patch($this->get('_router')->generate('api_patch_user_email_verify'), $body);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneByEmail($email);

        //connect the user
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $this->get('session')->set('_security_main', serialize($token));

        //add flash message
        $this->get('session')
            ->getFlashBag()
            ->add('success', $this->get('translator')->trans('success.email_verify'));
        if(implode(",", $user->getRoles())==UserGroupEnum::Host) {
            return $this->redirectToRoute('domain_edit');
        } else {
            return $this->redirectToRoute('home');
        }
    }
    /**
     * Route define intside the mail received by the user after the registration.
     * @Route("/reset/password", name="reset_password")
     */
    public function resetPassword(Request $request) {
        $this->get('session')->getFlashBag()->clear();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneByEmail($request->request->get('email'));
        if($user) {
            $this->addFlash('success', $this->get('translator')->trans('success.resetting_password'));
        } else {
            $this->addFlash('error', $this->get('translator')->trans('error.resetting_password'));
        }
//        return new Response();
        return $this->redirect($request->query->get('url'));
    }
    /**
     * New Host User
     * @param $user
     * @return mixed
     */
    public function submit($user)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_user'), $user);
    }

    public function getUserById($id) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        return $user;
    }
    public function submitSubscriptions($subscription)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->patch($this->get('router')->generate('api_patch_user_subscriptions'), $subscription);
    }
    public function submitPassword($password)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->patch($this->get('router')->generate('api_patch_user_password'), $password);
    }

    /**
     * @Route("user/{id}/orders", name="user_orders")
     *
     *
     */
    public function ordersAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        if ($user->isAdmin()) {
            $response = $api->get($this->get('_router')->generate('api_get_box_orders'));
            $boxOrders = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\BoxOrder>', 'json');

            $response = $api->get($this->get('_router')->generate('api_get_rental_orders'));
            $rentalOrders = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\RentalOrder>', 'json');

            $return['boxOrders'] = $boxOrders;
            $return['rentalOrders'] = $rentalOrders;
        } elseif ($user->isHost()) {
            //get only the rental orders
            $response = $api->get($this->get('_router')->generate('api_get_rental_orders_by_user', array('user' => $user->getId(), 'language' => $request->getLocale())));
            $rentalOrders = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\RentalOrder>', 'json');

            $return['rentalOrders'] = $rentalOrders;
        } else {

            //get the box orders
            $response = $api->get($this->get('_router')->generate('api_get_box_orders_by_user', array('user' => $user->getId(), 'language' => $request->getLocale())));
            $boxOrders = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\BoxOrder>', 'json');

            //get the rental Orders
            $response = $api->get($this->get('_router')->generate('api_get_rental_orders_by_user', array('user' => $user->getId(), 'language' => $request->getLocale())));
            $rentalOrders = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\RentalOrder>', 'json');

            $return['boxOrders'] = $boxOrders;
            $return['rentalOrders'] = $rentalOrders;
        }
        return $this->render('order.html.twig', $return);
    }
}