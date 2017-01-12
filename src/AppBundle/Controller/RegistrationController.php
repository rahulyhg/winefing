<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 12/07/2016
 * Time: 19:30
 */

namespace AppBundle\Controller;

use AppBundle\Form\UserRegistrationType;
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
use Symfony\Component\Form\FormError;
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use Winefing\ApiBundle\Entity\User;
use Winefing\ApiBundle\Entity\UserGroupEnum;

class RegistrationController extends Controller
{
    /**
     * @Route("/registration", name="registration")
     */
    public function userAction(Request $request) {
        $user = new User();
        $form =$this->createForm(UserRegistrationType::class, $user);
        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $api = $this->container->get('winefing.api_controller');
            $body['email'] = $request->request->get('user_registration')['email']['first'];
            if($api->get($this->get('router')->generate('api_get_user_by_email', $body))->getStatusCode() != StatusCodeEnum::empty_response) {
                $form->get('email')['first']->addError(new FormError($this->get('translator')->trans('error.email_existing')));
            } else {
                $body['roles'] = UserGroupEnum::User;
                $body['password'] = $request->request->get('user_registration')['password']['first'];
                $response = $api->post($this->get('router')->generate('api_post_user'), $body);
            }

        }
        return $this->render('user/registration.html.twig', array(
            'user' => $form->createView()
        ));
    }
}