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
use Winefing\ApiBundle\Entity\Error;
use Winefing\ApiBundle\Entity\ErrorEnum;
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use Winefing\ApiBundle\Entity\User;
use Winefing\ApiBundle\Entity\UserGroupEnum;
use Symfony\Component\Serializer\Serializer;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use FOS\UserBundle\Doctrine\UserManagerInterface;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Translation\Translator;



class UserController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de tout les users possible en base
     * @return Response
     */
    public function cgetAction($role)
    {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        if($role == UserGroupEnum::Admin) {
            $users = $repository->findAdmin();
        } else {
            $users = $repository->findByRoles($role);
        }
        return new Response($serializer->serialize($users, 'json', SerializationContext::create()->setGroups(array('default'))));
    }
    public function getByEmailAction($email) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $serializer = $this->container->get("jms_serializer");
        $user = $repository->findOneByEmail($email);
        if(count($user)>0) {
            return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('id'))));
        }
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
        $serializer = $this->container->get("jms_serializer");
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $newUser = $request->request->all();
        $user->setRoles($newUser["roles"]);
        switch ($user->getRoles()) {
            case UserGroupEnum::Host :
                $this->setHost($user, $newUser);
                break;
            case UserGroupEnum::User:
                if(!empty($this->findUserByEmail($newUser['email']))) {
                    throw new HttpException('An email already exist with this count');
                }
                $this->setUser($user, $newUser);
                break;
            default :
                $this->setAdmin($user, $newUser);
                break;
        }
        $this->setEncodePassword($user, $newUser["password"]);
        $user->setEnabled(1);
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400,$errorsString);
        }
        $em->persist($user);
        $em->flush();
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
    public function setHost($user, $newUser) {
        $user->setFirstName($newUser['firstName']);
        $user->setLastName(strtoupper($newUser['lastName']));
        $user->setPhoneNumber($newUser['phoneNumber']);
        $user->setEmail($newUser['email']);
        $user->setUserName($newUser['email']);
        $user->setVerify(0);
        $user->setLastLogin(new \DateTime());
    }
    public function setUser($user, $newUser) {
        $user->setEmail($newUser['email']);
        $user->setUserName($newUser['email']);
        $user->setLastLogin(new \DateTime());
    }
    public function setAdmin($user, &$newUser) {
        $user->setFirstName($newUser['firstName']);
        $user->setLastName(strtoupper($newUser['lastName']));
        $user->setPhoneNumber($newUser['phoneNumber']);
        $user->setEmail($newUser['email']);
        $user->setUserName($newUser['email']);
        $newUser['password'] = "winefing";
    }
    public function findUserByEmail($email) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user =  $repository->findOneByEmail($email);
        return $user;
    }

    /**
     * Edit a user
     * @param Request $request
     * @return Response
     */
    public function putAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneByEmail($request->request->get("email"));
        if(!empty($user) && $user->getId() != $request->request->get('id')) {
            throw new BadRequestHttpException("this email is already use.");
        }
        $em = $this->getDoctrine()->getManager();
        $user = $repository->findOneById($request->request->get('id'));
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
        $em->flush($user);
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
        $user = $userManager->findBy(array('id'=>$request->request->get('user')));
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
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findBy(array("id" => $request->request->get("id")));
        $user->setPlainPassword($request->request->get('password'));
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $em->flush($user);
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('default'))));
    }
    public function putSubscriptionsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $userRepository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $em = $this->getDoctrine()->getManager();
        $user = $userRepository->findBy(array('id'=>$request->request->get('user')));
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
        $em->flush($user);
    }

    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findBy(array("id" => $request->request->get("id")));
        $user->setEnabled($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
    public function setEncodePassword($user, $password) {
        $plainPassword = $password;
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
    }
    public function putAdminAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findBy(array("email" => $request->request->get("email")));
        if(!empty($user) && $user->getId() != $request->request->get('id')) {
            throw new BadRequestHttpException("this email is already use.");
        }
        $em = $this->getDoctrine()->getManager();
        $user = $repository->findBy(array('id'=>$request->request->get('id')));
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
        $em->flush($user);
    }
    public function putDomainAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        $user->addElementInWineList($domain);
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $em->flush($user);
    }
    public function putBoxAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $domain = $repository->findOneById($request->request->get('box'));
        $user->addElementInBoxList($domain);
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $em->flush($user);
    }
    public function putVerifyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get("id"));
        $user->setVerify($request->request->get("verify"));
        $em->persist($user);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
    public function deleteAction($id) {
        $user = $this->findUserById($id);
        switch($user->getRoles()) {
            case UserGroupEnum::Host :
                $error = $this->deleteHost($user);
                break;
            case UserGroupEnum::User :
                $error = $this->deleteUser($user);
                break;
            default :
                $error = $this->deleteAdmin($user);
                break;
        }
        if(!empty($error)) {
            return new Response(json_encode($error));
        }
    }
    public function deleteAdmin($user) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $articles = $repository->findByUser($user);
        if(!empty($articles)) {
            return array('msg'=> 'You can delete this user');
        }
        $em->remove($user);
        $em->flush();

    }
    public function deleteHost($user) {
        $em = $this->getDoctrine()->getManager();

    }
    public function deleteUser($user) {
        $em = $this->getDoctrine()->getManager();

    }
    public function findUserById($id) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        return $user;
    }
}