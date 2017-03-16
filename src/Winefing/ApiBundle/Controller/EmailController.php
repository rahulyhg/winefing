<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use JMS\Serializer\SerializationContext;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use Winefing\ApiBundle\Entity\RentalOrder;


class EmailController extends Controller implements ClassResourceInterface
{

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","email" },
     *  parameters={
     *      {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id"
     *      },
     *  },
     *  description="Send an email for the registration",
     *  statusCodes={
     *         204="Returned when no content",
     *
     *     }
     * )
     */
    public function postRegistrationAction(Request $request) {
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
//        $user = $repository->findOneById($request->request->get('user'));
//        $message = \Swift_Message::newInstance()
//            ->setSubject($this->get('translator')->trans('label.welcome', array(), 'messages', $request->request->get('language')))
//            ->setFrom($this->container->getParameter('mailer_user'))
//            ->setTo('sebastienmrcpro@gmail.com')
//            ->setBody(
//                $this->renderView(
//                // app/Resources/views/Emails/registration.html.twig
//                    'email/registration.html.twig',
//                    array('user'=>$user, 'language'=>$request->request->get('language'), 'verifyEmail'=>$this->get('_router')->generate('email_verify',
//                        array('email'=>$user->getEmail()), UrlGeneratorInterface::ABSOLUTE_URL))
//            ),
//                'text/html'
//            );
//        $this->get('mailer')->send($message);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","email" },
     *  parameters={
     *      {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id"
     *      },
     *  },
     *  description="Send an email for reset the password",
     *  statusCodes={
     *         204="Returned when no content",
     *
     *     }
     * )
     */
    public function postPasswordForgottenAction(Request $request) {
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
//        $user = $repository->findOneById($request->request->get('user'));
//        $message = \Swift_Message::newInstance()
//            ->setSubject($this->get('translator')->trans('label.reset_password', array(), 'messages', $request->request->get('language')))
//            ->setFrom($this->container->getParameter('mailer_user'))
////            ->setTo('sebastienmrcpro@gmail.com')
//            ->setTo('sebastienmrcpro@gmail.com')
//            ->setBody(
//                $this->renderView(
//                // app/Resources/views/Emails/registration.html.twig
//                    'email/resetPassword.html.twig',
//                    array('user'=>$user, 'language'=>$request->request->get('language'), 'verifyEmail'=>$this->get('_router')->generate('email_verify',
//                        array('email'=>$user->getEmail()), UrlGeneratorInterface::ABSOLUTE_URL))
//                ),
//                'text/html'
//            );
//        $this->get('mailer')->send($message);
    }
    public function validateRentalOrder() {

    }
    public function validateRefuseOrder() {

    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","email" },
     *  parameters={
     *      {
     *          "name"="user", "dataType"="integer", "required"=true, "description"="user id"
     *      },
     *  },
     *  description="Send an email for the registration",
     *  statusCodes={
     *         204="Returned when no content",
     *
     *     }
     * )
     */
    public function postPaiementAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $order = $repository->findOneById($request->request->get('order'));
        if(!$order instanceof RentalOrder) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxOrder');
            $order = $repository->findOneById($request->request->get('order'));
            $message = $this->get('translator')->trans('text.user_box_order', array('%url%'=>$this->get('_router')->generate('box_order_detail', array('id'=>$order->getId()))), 'messages', $request->request->get('language'));
        } else {
            $message = $this->get('translator')->trans('text.user_rental_order', array('%url%'=>$this->get('_router')->generate('rental_order_detail', array('id'=>$order->getId()))), 'messages', $request->request->get('language'));

            //send the mail to the wine hoster
            $mail = \Swift_Message::newInstance()
                ->setSubject($this->get('translator')->trans('label.order', array(), 'messages', $request->request->get('language')))

                ->setFrom($this->container->getParameter('mailer_user'))
                ->setTo('carval.audrey@gmail.com')
                ->setBody(
                    $this->renderView(
                        'email/template.html.twig',
                        array('message'=>$message)
                    ),
                    'text/html'
                );
            $this->get('mailer')->send($mail);

            $message = $this->get('translator')->trans('text.user_rental_order', array('%url%'=>$this->get('_router')->generate('rental_order_detail', array('id'=>$order->getId()))), 'messages', $request->request->get('language'));
        }

        //send the mail to the
        $message = \Swift_Message::newInstance()
            ->setSubject($this->get('translator')->trans('label.order', array(), 'messages', $request->request->get('language')))

            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo('carval.audrey@gmail.com')
            ->setBody(
                $this->renderView(
                    'email/template.html.twig',
                    array('message'=>$message)
            ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }
    public function postContactAction(Request $request) {
        $message = \Swift_Message::newInstance()
            ->setSubject('Nouveau message de la part de '.$request->request->get('name').' email : '.$request->request->get('email'))
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo($this->getParameter('winefing_email'))
            ->setBody($request->request->get('message'));
        $this->get('mailer')->send($message);
    }

    public function postTestAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $message = \Swift_Message::newInstance()
            ->setSubject('test')
            ->setFrom($this->container->getParameter('mailer_user'))
            ->setTo('carval.audrey@gmail.com')
            ->setBody(
                $this->renderView(
                // app/Resources/views/Emails/registration.html.twig
                    'email/template.html.twig',
                    array('user'=>$user, 'message'=>'<p>Coucou</p>')
                ),
                'text/html'
            );
        $this->get('mailer')->send($message);
    }

}