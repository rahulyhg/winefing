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
use Nelmio\ApiDocBundle\Annotation\ApiDoc;



class UserController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all the user by role.",
     *  views = { "index", "user" },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\User",
     *      "groups"={"id", "default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="role", "dataType"="string", "required"=true, "description"="user role"
     *      }
     *     }
     * )
     */
    public function cgetAction($role)
    {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $repositoryRentalOrder = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        if($role == UserGroupEnum::Admin) {
            $users = $repository->findAdmin();
            $json = $serializer->serialize($users, 'json', SerializationContext::create()->setGroups(array('id','default')));
        } else {
            $users = $repository->findByRoles($role);
            foreach($users as $user) {
                $rentalOrders = $repositoryRentalOrder->findWithUser($user->getId());
                $user->setHostRentalOrders($rentalOrders);
            }
            $json = $serializer->serialize($users, 'json', SerializationContext::create()->setGroups(array('id','default','hostRentalOrders', 'domains')));

        }
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Return the user by email.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\User",
     *      "groups"={"id"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="email", "dataType"="string", "required"=true, "description"="user email"
     *      }
     *     }
     * )
     */
    public function getByEmailAction($email) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $serializer = $this->container->get("jms_serializer");
        $user = $repository->findOneByEmail($email);
        if(!empty($user)) {
            return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('id'))));
        }
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Return the user by id.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\User",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     }
     * )
     */
    public function getAction($id)
    {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('default'))));
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  parameters={
     *      {
     *          "name"="firstName", "dataType"="string", "required"=true
     *      },
     *      {
     *          "name"="lastName", "dataType"="string", "required"=true, "description"="timestamp"
     *      },
     *      {
     *          "name"="phoneNumber", "dataType"="string", "required"=false, "description"="required only if the user is an host"
     *      },
     *     {
     *          "name"="email", "dataType"="string", "required"=true
     *      },
     *     {
     *          "name"="password", "dataType"="string", "required"=true
     *      },
     *     {
     *          "name"="roles", "dataType"="string", "required"=true, "description"="array of size 1. ROLE_USER or ROLE_HOST or ROLE_ADMIN"
     *      },
     *   },
     *  description="New object.",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\User",
     *      "groups"={"id"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid",
     *         409="Returned when a user already exist with the same email",
     *
     *     }
     * )
     */
    public function postAction(Request $request)
    {
        $serializer = $this->container->get("jms_serializer");
        $user = new User();
        $em = $this->getDoctrine()->getManager();
        $newUser = $request->request->all();
        $user->setRoles($newUser["roles"]);
        switch ($newUser["roles"]) {
            case UserGroupEnum::Host :
                $this->setHost($user, $newUser);
                break;
            case UserGroupEnum::User:
                if(!empty($this->findUserByEmail($newUser['email']))) {
                    throw new HttpException(409, 'An email already exist with this count');
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
        //CHANGER
        $user->setVerify(1);
        $user->setLastLogin(new \DateTime());
        $newUser['password'] = "winefing";
    }
    public function setUser($user, $newUser) {
        $user->setFirstName($newUser['firstName']);
        $user->setLastName(strtoupper($newUser['lastName']));
        $user->setEmail($newUser['email']);
        $user->setUserName($newUser['email']);
        $user->setLastLogin(new \DateTime());
        $user->setBirthDate(date_create_from_format('U', $newUser['birthDate']));
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
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  input="AppBundle\Form\UserType",
     *  description="New object.",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid",
     *         409="Returned when a user already exist with the same email",
     *
     *     }
     * )
     */
    public function putAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneByEmail($request->request->get("email"));
        if(!empty($user) && $user->getId() != $request->request->get('id')) {
            throw new HttpException(409, "this email is already use.");
        }
        $em = $this->getDoctrine()->getManager();
        $user = $repository->findOneById($request->request->get('id'));
        $user->setFirstName($request->request->get('firstName'));
        $user->setLastName(strtoupper($request->request->get('lastName')));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $user->setEmail($request->request->get('email'));
        $user->setUserName($request->request->get('email'));
        $user->setDescription($request->request->get('description'));

        //for the admin
        $user->setFacebook($request->request->get('facebook'));
        $user->setTwitter($request->request->get('twitter'));
        $user->setInstagram($request->request->get('instagram'));
        $user->setGoogle($request->request->get('google'));

        if(!empty($request->request->get('birthDate'))) {
            $user->setBirthDate(date_create_from_format('U', $request->request->get('birthDate')));
        }
        $user->setSex($request->request->get('sex'));
        $user->setPhoneNumber($request->request->get('phoneNumber'));
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $em->flush($user);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="New object.",
     *  parameters={
     *     {
     *          "name"="media", "dataType"="file", "required"=true, "description"="user's picture"
     *      },
     *      {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content",
     *         400="Returned when the entity is not valid",
     *         409="Returned when a user already exist with the same email",
     *
     *     }
     * )
     */
    public function postPictureAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $uploadedFile = $request->files->get('media');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Image);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
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
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Edit password.",
     *  parameters={
     *     {
     *          "name"="password", "dataType"="file", "required"=true, "description"="new password"
     *      },
     *      {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchPasswordAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get("user"));
//        $encoder = $this->container->get('security.password_encoder');
//        if(!$encoder->isPasswordValid($user, $request->request->get('password'))) {
//            throw new \Exception('lol');
//        } else {
            $this->setEncodePassword($user, $request->request->get('password'));
//        }
        $validator = $this->get('validator');
        $errors = $validator->validate($user);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($user);
        $em->flush($user);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Add subscriptions.",
     *  parameters={
     *     {
     *          "name"="subscriptions", "dataType"="subscription", "required"=true, "description"="array collection of subscription."
     *      },
     *      {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchSubscriptionsAction(Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $userRepository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $em = $this->getDoctrine()->getManager();
        $user = $userRepository->findOneById($request->request->get('user'));
        $user->resetSubscriptions();
        foreach($request->request->get('subscription') as $subscription) {
            if($subscription["value"] == 1) {
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
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Change the enabled field.",
     *  parameters={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id."
     *      },
     *      {
     *          "name"="enabled", "dataType"="boolean", "required"=true, "description"="if enabled = 0, the user can't access to his account"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchEnabledAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findBy(array("id" => $request->request->get("id")));
        $user->setEnabled($request->request->get("enabled"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
    }
    public function setEncodePassword($user, $password) {
        $plainPassword = $password;
        $encoder = $this->container->get('security.password_encoder');
        $encoded = $encoder->encodePassword($user, $plainPassword);
        $user->setPassword($encoded);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Add element in the winelist.",
     *  parameters={
     *     {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id."
     *      },
     *      {
     *          "name"="domain", "dataType"="integer", "required"=true, "description"="domain id"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchDomainAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('domain'));
        if($user->getWineList()->contains($domain)) {
            //if the route is call and the domain is already in the wineList,
            // is that the user don't want any more the domain in the wine list
            $user->removeElementInWineList($domain);
        } else {
            $user->addElementInWineList($domain);
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
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Add element in the boxList.",
     *  parameters={
     *     {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id."
     *      },
     *      {
     *          "name"="box", "dataType"="integer", "required"=true, "description"="box id"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchBoxAction(Request $request) {
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
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Set the verify field of the user.If an hos is not verify, he is not displaying on the website.",
     *  parameters={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id."
     *      },
     *      {
     *          "name"="verify", "dataType"="boolean", "required"=true, "description"="verify"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchtVerifyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get("id"));
        $user->setVerify($request->request->get("verify"));
        $em->persist($user);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Know if the email is verify.",
     *  parameters={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id."
     *      },
     *      {
     *          "name"="emailVerify", "dataType"="boolean", "required"=true, "description"="1 or 0"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchEmailVerifyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneByEmail($request->request->get("email"));
        $user->setEmailVerify($request->request->get("emailVerify"));
        $em->persist($user);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Know if the phone number is verify",
     *  parameters={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id."
     *      },
     *      {
     *          "name"="phoneNumberVerify", "dataType"="boolean", "required"=true, "description"="1 or 0"
     *      }
     *     },
     *  statusCodes={
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function patchPhoneNumberVerifyAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get("id"));
        $user->setVerify($request->request->get("phoneNumberVerify"));
        $em->persist($user);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Delete an user.User : only if the user has no rental with rental's order (rentalOrders is empty). Host : only is the host has no rental with rentalOrder. Admin : only is the admin haven't write any article",
     *  statusCodes={
     *         204="Returned when no content",
     *         422="The object can't be deleted."
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="user id"
     *      }
     *     },
     *
     * )
     */
    public function deleteAction($id) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($id);
        switch(implode(',', $user->getRoles())) {
            case UserGroupEnum::Host :
                $this->deleteHost($user);
                break;
            case UserGroupEnum::User :
                $this->deleteUser($user);
                break;
            default :
                $this->deleteAdmin($user);
                break;
        }
        $em->remove($user);
        $em->flush();
    }
    public function deleteAdmin($user) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $articles = $repository->findByUser($user);
        if(!empty($articles)) {
            throw new HttpException(422, "you can't delete this user");
        }

    }
    public function deleteHost($user) {
        foreach($user->getDomains() as $domain) {
            foreach($domain->getProperties() as $property) {
                foreach($property->getRentals() as $rental) {
                    if(!empty($rental->getRentalOrders())) {
                        throw new HttpException(422, "you can't delete this user");
                    }
                }
            }
        }

    }
    public function deleteUser($user) {
        if(!empty($user->getRentalOrders())) {
            throw new HttpException(422, "you can't delete this user");
        }

    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user" },
     *  description="Get the user's token",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\User",
     *      "groups"={"token", "default"}
     *     },
     *  parameters={
     *     {
     *          "name"="email", "dataType"="email", "required"=true, "description"="the user's email."
     *      },
     *      {
     *          "name"="plainPassword", "dataType"="string", "required"=true, "description"="user password without encoding"
     *      }
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *
     *     }
     * )
     */
    public function postTokenAction(Request $request) {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneBy(array('email'=>$request->request->get('email')));
        if($user) {
            $encoder = $this->container->get('security.password_encoder');
            if($encoder->isPasswordValid($user, $request->request->get('plainPassword'))) {
                return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('token', 'default'))));
            }
        }
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","user", "webPath"},
     *  description="Return the web path for the entity image",
     *  statusCodes={
     *         200="Returned when successful",
     *     }
     *
     * )
     */
    public function getMediaPathAction() {
        $webPath = $this->container->get('winefing.webpath_controller');
        $picturePath = $webPath->getPath($this->getParameter('user_directory'));
        return new Response(json_encode($picturePath));
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "id"},
     *  description="Return user for a domain id given",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     *
     * )
     */
    public function getUserByDomainAction($domain) {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($domain);
        return new Response($serializer->serialize($domain->getUser(), 'json', SerializationContext::create()->setGroups(array('id'))));
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "id"},
     *  description="Return user for a domain id given",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     *
     * )
     */
    public function getHostByRentalAction($rental) {
        $serializer = $this->container->get("jms_serializer");
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneWithRental($rental);
        return new Response($serializer->serialize($user, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
}