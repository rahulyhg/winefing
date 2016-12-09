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
use AppBundle\Form\AddressType;
use AppBundle\Form\DomainType;
use Winefing\ApiBundle\Entity\Address;
use Winefing\ApiBundle\Entity\Domain;

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
        $addressForm = $this->createForm(AddressType::class, new Address());
        $domainForm = $this->createForm(DomainType::class, new Domain());
        return $this->render('host/registration.html.twig', array(
            'addressForm' => $addressForm->createView(), 'domainForm' => $domainForm->createView()
        ));
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