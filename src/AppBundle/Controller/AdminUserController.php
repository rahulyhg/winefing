<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 24/09/2016
 * Time: 19:17
 */

namespace AppBundle\Controller;

namespace AppBundle\Controller;
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

class AdminUserController extends Controller
{
    /**
     * @Route("users/admin", name="users_admin")
     */
    public function cgetAdmin() {
        $client = new Client();
        $response = $client->request('GET', 'http://104.47.146.137/winefing/web/app_dev.php/api/admin/users', []);
        $usersJson = $response->getBody()->getContents();

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $users = $serializer->decode($usersJson, 'json');
        return $this->render('admin/user/index.html.twig', array(
            'users' => $users
        ));
    }
    /**
     * @Route("/user/newForm/{id}", name="user_new_form")
     */
    public function newFormAction($id = 'user_post') {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array('id'=>$id));
        $action = $this->generateUrl('users_admin_put');
        if(empty($user)){
            $user = new User();
            $action = $this->generateUrl('users_admin_post');;
        }
        return $this->render('admin/user/form.html.twig', array(
            'user' => $user, 'action' => $action, 'method' => 'POST'
        ));
    }
    /**
     * @Route("/users/admin/post", name="users_admin_post")
     */
    public function postAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/admins/users", $request->request->all()["user"], null);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The user is well created.");
        return $this->redirectToRoute('users_admin');
    }
    /**
     * @Route("/users/admin/put", name="users_admin_put")
     */
    public function putAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/admin/user", $request->request->all()["user"]);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The user is well modified.");
        //return new Response();
        return $this->redirectToRoute('users_admin');
    }

    /**
     * @Route("user/admin/delete/{id}", name="user_admin_delete")
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
        $client = new Client();
        $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/admins/'.$id.'/user');
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The user is well deleted.");
        return $this->redirectToRoute('users_admin');
    }

    /**
     * @Route("/user/activated/", name="user_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put('http://104.47.146.137/winefing/web/app_dev.php/api/admin/user/activated', $request->request->all());
        return new Response(json_encode([200, "success"]));
    }

}