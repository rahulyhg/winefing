<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 19:17
 */

namespace Winefing\UserBundle\Controller;

use AppBundle\Form\AdminUserType;
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
use Winefing\ApiBundle\Entity\User;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class AdminController extends Controller
{
    /**
     * @Route("lolilo/user/new/{id}", name="user_new_form")
     */
    public function newFormAction($id = '') {
        $user = new User();
        if(!empty($id)){
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
            $user = $repository->findOneById($id);
        }
        $userForm = $this->createForm(AdminUserType::class, $user, array('action'=>$this->generateUrl('users_admin_submit')));
        return $this->render('admin/user/form.html.twig', array(
            'user' => $userForm->createView()
        ));
    }
    /**
     * @Route("admin/users/submit", name="users_admin_submit")
     */
    public function postAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $user = $request->request->all()["admin_user"];
        if(empty($user["id"])) {
            $api->post($this->get("_router")->generate("api_post_user"), $user);
        } else {
            $api->put($this->get("_router")->generate("api_put_user"), $user);
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The user is well created.");
        return $this->redirectToRoute('users_by_group', array('group'=>UserGroupEnum::Admin), 301);
    }
    /**
     * @Route("admin/user/delete/{id}", name="user_admin_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array('id'=>$id));
        $blog = false;
        foreach($user->getRoles() as $role) {
            if($role == UserGroupEnum::Blog) {
                $blog = true;
            }
        }
        if($blog) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
            $article = $repository->findByUserId($user->getId());
        }
        if(!empty($article)) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "You can't delete this user because he wrote some articles. Put this user desabled.");
            return $this->redirectToRoute('users_admin');
        }
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get("_router")->generate("api_post_admin_user", array("id" => $id)), $request->request->all()["user"]);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The user is well deleted.");
        return $this->redirectToRoute('users_admin');
    }

    /**
     * @Route("admin/user/activated/", name="user_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get("_router")->generate("api_put_admin_user_activated"), $request->request->all());
        return new Response(json_encode([200, "success"]));
    }

}