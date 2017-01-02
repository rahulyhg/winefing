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
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Serializer\Serializer;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\UserBundle\Doctrine\UserManagerInterface;
use JMS\Serializer\SerializationContext;



class HostUserController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les users possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $users = $repository->findHost();
        return new Response($serializer->serialize($users, 'json', SerializationContext::create()->setGroups(array('default'))));
    }
    public function getByEmailAction($email) {
        $userManager = $this->get('fos_user.user_manager');
        $serializer = $this->container->get("jms_serializer");
        $user = $userManager->findUserBy(array('email'=>$email));
        return new Response($serializer->serialize($user, 'json'));
    }
    public function getAction($id)
    {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    public function getMediaPathAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('user_directory'));
        return new Response($serializer->serialize($picturePath));
    }

    /**
     * New a user
     * @param Request $request
     * @return Response
     */
    public function postAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->createUser();
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get("jms_serializer");
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName(strtoupper($request->request->get('lastName')));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setEmail($request->request->get('email'));
        $user->setUserName($request->request->get('email'));
        $user->setEnabled(1);
        $user->setPlainPassword("winefing");
        $role[0] = UserGroupEnum::Host;
        $user->setRoles($role);
        if(!empty($request->request->get('birthDate'))) {
            $user->setBirthDate($request->request->get('birthDate'));
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $userManager->updateUser($user);
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('id'))));
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
        if(!empty($user) && $user->getId() != $request->request->get('id')) {
            throw new BadRequestHttpException("this email is already use.");
        }
        $em = $this->getDoctrine()->getManager();
        $user = $userManager->findUserBy(array('id'=>$request->request->get('id')));
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName(strtoupper($request->request->get('lastName')));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setEmail($request->request->get('email'));
        $user->setUserName($request->request->get('email'));
        $user->setDescription($request->request->get('description'));
        if(!empty($request->request->get('birthDate'))) {
            $user->setBirthDate(date_create_from_format('U', $request->request->get('birthDate')));
        }
        $user->setSex($request->request->get('sex'));
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $userManager->updateUser($user);
    }
    public function postPictureAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $uploadedFile = $request->files->get('media');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Image);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $userManager = $this->get('fos_user.user_manager');
        $user = $userManager->findUserBy(array('id'=>$request->request->get('user')));
        if(!empty($user->getPicture())) {
            unlink($this->getParameter('user_directory_upload') . $user->getPicture());
        }
        $user->setPicture($fileName);
        $uploadedFile->move(
            $this->getParameter('user_directory_upload'),
            $fileName
        );
        $em->persist($user);
        $em->flush();
    }

    /**
     * Edit a user
     * @param Request $request
     * @return Response
     */
    public function putPasswordAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get("jms_serializer");
        $user = $userManager->findUserBy(array('id'=>$request->request->get('user')));
        $user->setPlainPassword($request->request->get('password'));
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $userManager->updateUser($user);
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('default'))));
    }
    public function putSubscriptionsAction(Request $request)
    {
        $userManager = $this->get('fos_user.user_manager');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $em = $this->getDoctrine()->getManager();
        $user = $userManager->findUserBy(array('id'=>$request->request->get('user')));
        $user->resetSubscriptions();
        foreach($request->request->get('subscriptions') as $subscription) {
            if($subscription["checkbox"] == 1) {
                $user->addSubscription($repository->findOneById($subscription["id"]));
            }
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $userManager->updateUser($user);
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