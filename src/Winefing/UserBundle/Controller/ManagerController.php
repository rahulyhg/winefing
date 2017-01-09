<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 30/12/2016
 * Time: 10:06
 */

namespace Winefing\UserBundle\Controller;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Winefing\ApiBundle\Entity\UserGroupEnum;

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
}