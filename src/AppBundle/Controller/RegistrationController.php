<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 12/07/2016
 * Time: 19:30
 */

namespace AppBundle\Controller;

use Symfony\Component\Translation\Translator;
use Symfony\Component\Translation\Loader\ArrayLoader;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\Security\Core\Encoder\BasePasswordEncoder;
use Symfony\Component\Security\Core\Exception\BadCredentialsException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\Extension\Core\Type\TextType;
use Symfony\Component\Form\Extension\Core\Type\EmailType;
use Symfony\Component\Form\Extension\Core\Type\PasswordType;
use FOS\UserBundle\Doctrine\UserManagerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use AppBundle\Entity\Property;

class RegistrationController extends Controller
{
    /**
     * @Route("/registration/test", name="test")
     */
    public function test()
    {

        return $this->render('host/index.html.twig');
    }
    /**
     * @Route("/registration/host", name="host_registration_index")
     */
    public function host()
    {
        return $this->render('registration/host.html.twig');
    }

    /**
     * @Route("/registration/addHost", name="host_registration_add")
     * @Method({"POST"})
     */
    public function addHost(Request $request)
    {
        var_dump($request->request->all());
        $session = new Session();
        $userManager = $this->get('fos_user.user_manager');
        $email = $request->request->get('email');
        $user = $userManager->findUserByEmail($email);
        $code = 200;

        if(!empty($user)) {
            $repository = $this->getDoctrine()->getRepository('AppBundle:Property');
            $property = $repository->findOneByUser($user);
            if(!empty($property)) {
                $errors = json_encode(array('message' => 'Déjà ce mail'));
                $code = 419;
            } else {
                $session->set('user', $user);
            }
        } else {
            $newUser = $userManager->createUser();
            $newUser->setFirstName($request->request->get('firstName'));
            $newUser->setLastName($request->request->get('lastName'));
            $newUser->setPhoneNumber($request->request->get('phoneNumber'));
            $newUser->setEmail($email);
            $newUser->setUsername($email);
            $newUser->setPlainPassword($request->request->get('password'));
            $newUser->addRole('ROLE_HOST');
            $userManager->updateUser($newUser);

            $session->set('user', $newUser);

        }
        return new Response("lol", $code);
    }

    /**
     * @Route("/registration/addProperty", name="property_registration_add")
     * @Method({"POST"})
     */
    public function addProperty(Request $request) {
        $session = new Session();
        $property = new Property();
        $property->setName($request->request->get('name'));
        $property->setUser($session->get('user'));
        $property->setAddress($request->request->get('address'));
        $property->setStreet($request->request->get('address'));
        $property->setPostalCode(strval($request->request->get('postal_code')));
        $property->setLocality($request->request->get('locality'));
        $property->setCountry($request->request->get('country'));
        $property->setLng(123);
        $property->setLat(3456);

        $em = $this->getDoctrine()->getManager();
        $em->merge($property);
        $em->flush();
        $session->remove('user');
        return new Response();

    }
}