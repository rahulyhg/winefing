<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 30/12/2016
 * Time: 10:06
 */

namespace Winefing\UserBundle\Controller;
use AppBundle\Form\PasswordEditType;
use AppBundle\Form\UserType;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

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
        $response = $api->get($this->get('_router')->generate('api_get_domains_wine_list', array('userId' => $this->getUser()->getId())));
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
        var_dump($user->getRoles());
        $api = $this->container->get('winefing.api_controller');
        $response = $api->delete($this->get('_router')->generate('api_delete_user', array('id' => $id)));
        if(!empty($response->getBody()->getContents())) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', $response->getBody()->getContents());
        }
        return $this->redirectToRoute('users_by_group', array('group'=>$user->isAdmin() ? UserGroupEnum::Admin : $user->getRoles()));
    }

    /**
     * @Route("/user/{id}/edit/{nav}", name="user_edit")
     */
    public function getHost($id, $nav = 'profil',Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');

        $user = $this->getUserById($id);
        $userForm =  $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        if($userForm->isSubmitted() && $userForm->isValid()) {
            $userEdit = $request->request->get('user');
            $userEdit["birthDate"] = strtotime($userEdit["birthDate"]);
            $userEdit["id"] = $user->getId();
            $this->submit($userEdit);
            $this->addFlash('userSuccess', $this->get('translator')->trans('success.profil_edit'));
            return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId())) . '#profil');
        }
        $passwordForm = $this->createForm(PasswordEditType::class, $user);
        $passwordForm->handleRequest($request);
        if($passwordForm->isSubmitted() && $passwordForm->isValid()) {
            $nav = 'password';
            $encoder = $this->container->get('security.password_encoder');
            var_dump($request->request->get('password_edit')["currentPassword"]);
            $password = $request->request->get('password_edit')["currentPassword"];
            var_dump($encoder->isPasswordValid($user, (string) $password));
            if(!$encoder->isPasswordValid($user, $request->request->get('password_edit')["currentPassword"])) {
                $this->addFlash('passwordError', $this->get('translator')->trans('error.not_current_password'));
            } else {
                $passwordForm["password"] = $request->request->get('password_edit')["password"]["first"];
                $passwordForm['user'] = $user->getId();
                $this->submitPassword($passwordForm);
                $this->addFlash('passwordSuccess', $this->get('translator')->trans('success.password_edit'));
                return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId(), 'nav'=>$nav)));
            }
        }

        $response = $api->get($this->get('router')->generate('api_get_subscriptions_by_user', array('user'=> $user->getId(), 'language'=>$request->getLocale())));
        $subscriptions = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Subscription>', 'json');
        $subscriptionFormatList = $this->subscriptionsByFormat($subscriptions, $user);
        if ($request->isMethod('POST')) {
            $picture = $request->files->get('picture');
            $subscription = $request->request->get('subscriptionForm');
            if($picture !=null) {
                $nav = 'picture';
                $body["user"] = $user->getId();
                $this->submitPicture($picture, $body);
                $this->addFlash('pictureSuccess', $this->get('translator')->trans('success.picture_edit'));
                return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId(), 'nav'=>$nav)));
            }
            if($subscription != null) {
                $nav = 'subscriptions';
                $subscription["user"] = $user->getId();
                $this->submitSubscriptions($subscription);
                $this->addFlash('subscriptionsSuccess', $this->get('translator')->trans('success.modifications_saved'));
                return $this->redirect($this->generateUrl('user_edit', array('id' => $user->getId(), 'nav'=>$nav)));
            }
        }

        return $this->render('userEdit.html.twig', array(
            'userForm' => $userForm->createView(),
            'picture' => $user->getPicture(),
            'subscriptionFormatList' => $subscriptionFormatList,
            'passwordForm' => $passwordForm->createView(),
            'nav'=>$nav
        ));
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
     * @Route("/user/{id}/reset/password", name="reset_password")
     */
    public function resetPassword($id) {
//        $body['email'] = $email;
//        $body['emailVerify'] = 1;
//        $api = $this->container->get('winefing.api_controller');
//        $api->patch($this->get('_router')->generate('api_patch_user_email_verify'), $body);
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
//        $user = $repository->findOneByEmail($email);
//
//        //connect the user
//        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
//        $this->get('security.token_storage')->setToken($token);
//        $this->get('session')->set('_security_main', serialize($token));
//
//        //add flash message
//        $this->get('session')
//            ->getFlashBag()
//            ->add('success', $this->get('translator')->trans('success.email_verify'));
//        if(implode(",", $user->getRoles())==UserGroupEnum::Host) {
//            return $this->redirectToRoute('domain_edit');
//        } else {
//            return $this->redirectToRoute('home');
//        }
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
}