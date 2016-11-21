<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\UserBundle\Doctrine\UserManagerInterface;



class AdminUserController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les users possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get("winefing.serializer_controller");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $users = $repository->findAdmin();
        foreach($users as $user) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
            $articles = $repository->findByUserId($user->getId());
            if(empty($articles)) {
                $user->setDeleted(true);
            } else {
                $user->setDeleted(false);
            }
        }
        $json = $serializer->serialize($users);
        return new Response($json);
    }
    /**
     * Create or update a user from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $serializer = $this->container->get("winefing.serializer_controller");
        $user = $userManager->findUserByEmail($request->request->get('email'));
        if(!empty($user)) {
            $api = $this->container->get('winefing.api_controller');
            return  $api->put($this->get("_router")->generate("api_put_admin_user"), $request->request->all());
        }
        $user = $userManager->createUser();
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setEmail($request->request->get('email'));
        $user->setUserName($request->request->get('email'));
        $user->setVerify(1);
        $roles[] = $request->request->get('roles');
        $user->setRoles($roles);
        $user->setEnabled($request->request->get('enabled'));
        $user->setPlainPassword(0000);
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        } else {
            $userManager->updateUser($user);
        }
        return new Response($serializer->serialize($user));
    }

    /**
     * Edit a user
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array('email'=>$request->request->get('email')));
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName(strtoupper($request->request->get('lastName')));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setEmail($request->request->get('email'));
        $user->setUserName($request->request->get('email'));
        $user->setVerify(1);
        $roles[] = $request->request->get('roles');
        $user->setRoles($roles);
        $user->setEnabled($request->request->get('enabled'));
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        } else {
            $userManager->updateUser($user);
        }
        return new Response(json_encode([200, "The user is well modified."]));
    }
    public function postHostAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get("winefing.serializer_controller");
        $user = $userManager->findUserByEmail($request->request->get('email'));
        if(!empty($user)) {
            throw new HttpException(400, "there is already an user with this mail.");
        }
        $user = $userManager->createUser();
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName($request->request->get('lastName'));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setEmail($request->request->get('email'));
        $user->setUserName($request->request->get('email'));
        $user->setVerify(1);
        $roles[] = UserGroupEnum::Host;
        $user->setRoles($roles);
        $user->setEnabled(1);
        $user->setPlainPassword("winefing");
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $userManager->updateUser($user);
        return new Response($serializer->serialize($user));
    }

    /**
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array("id" => $id));
        $em = $this->getDoctrine()->getManager();
        $em->remove($user);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }

    public function putActivatedAction(Request $request) {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array("id" => $request->request->get("id")));
        $user->setEnabled($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}