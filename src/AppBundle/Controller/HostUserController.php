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
use Winefing\ApiBundle\Entity\SubscriptionFormatEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;

class HostUserController extends Controller
{
    /**
     * @Route("host/{id}", name="user_host")
     */
    public function getHost($id = 57, $anchor = 'edit-profil') {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');

        $response = $api->get($this->get('_router')->generate('api_get_host_user', array('id'=>$id)));
        $user= $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\User', 'json');

        $response = $api->get($this->get('router')->generate('api_get_subscriptions_user_group', array('userGroup'=> UserGroupEnum::Host)));
        $subscriptions = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Subscription>', 'json');
        $subscriptionFormatList = $this->subscriptionsByFormat($user, $subscriptions);


        $response = $api->get($this->get('_router')->generate('api_get_host_users_picture_path'));
        $serializer = $this->container->get('winefing.serializer_controller');
        $picturePath = $serializer->decode($response->getBody()->getContents());


        return $this->render('host/user/index.html.twig', array(
            'user' => $user,
            'picturePath' => $picturePath,
            'subscriptionFormatList' => $subscriptionFormatList,
            'anchor' => $anchor
        ));
    }
    public function subscriptionsByFormat($user, $subscriptions) {
        $subscriptionFormatList = array();
        foreach($subscriptions as $subscription) {
            $subscriptionFormatList[$subscription->getFormat()][] = $subscription;
        }
        return $subscriptionFormatList;
    }

    /**
     * @Route("/submit/host", name="user_host_submit")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("winefing.serializer_controller");

        $user = $request->request->all()["user"];
        $response =  $api->post($this->get('router')->generate('api_post_host_user'), $user);
        $user = $serializer->decode($response->getBody()->getContents());

        $address = $request->request->all()["address"];
        $response =  $api->post($this->get('router')->generate('api_post_address'), $address);
        $address = $serializer->decode($response->getBody()->getContents());

        $domain = $request->request->all()["domain"];
        $domain["user"] = $user["id"];
        $domain["address"] = $address["id"];
        $api->post($this->get('router')->generate('api_post_domain'), $domain);


        return $this->redirectToRoute('user_host');
//        $serializer = $this->container->get('winefing.serializer_controller');
//        $response =  $api->put($this->get('router')->generate('api_put_host_user'), $request->request->all());
//        $host = $serializer->decode($response->getBody()->getContents());
//        $request->getSession()
//            ->getFlashBag()
//            ->add('success', "The user is well modified.");
//        return $this->redirectToRoute('user_host');
    }
    /**
     * @Route("/submit/pictures/host", name="host_picture_submit")
     */
    public function submitPictureAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $user["user"] = $request->request->get('user');
        $api->file($this->get('router')->generate('api_post_host_user_picture'), $user, $request->files->all()['picture']);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The pciture is well modified.");

        return $this->redirect($this->generateUrl('user_host'). '#profil-password');
    }

    /**
     * @Route("/submit/password/host", name="host_password_submit")
     */
    public function submitPasswordAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        if($request->request->get('password') != $request->request->get('confirmationPassword')) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "The two password not correspond");
            return $this->redirectToRoute('user_host', ['_fragment' => 'edit-password']);
        }
        $api->put($this->get('router')->generate('api_put_host_user_password'), $request->request->all());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The password is well modified.");
        return $this->redirect($this->generateUrl('user_host'). '#edit-password');
    }
    /**
     * @Route("/submit/subscriptions/host", name="host_subscriptions_submit")
     */
    public function submitSubscriptionsAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_host_user_subscriptions'), $request->request->all()["subscription"]);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The subscription is well modified.");
        return $this->redirect($this->generateUrl('user_host'). '#notifications');
    }
}