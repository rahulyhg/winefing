<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Winefing\ApiBundle\Entity\Subscription;
use Winefing\ApiBundle\Entity\SubscriptionFormatEnum;
use Winefing\ApiBundle\Entity\SubscriptionTr;
use AppBundle\Form\SubscriptionType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use GuzzleHttp\Client;



class SubscriptionController extends Controller
{
    /**
     * @Route("/subscriptions", name="subscriptions")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->generateUrl('api_get_subscriptions'));
        $serializer = $this->container->get('jms_serializer');
        $subscriptions = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Subscription>', 'json');
        return $this->render('admin/subscription/index.html.twig', array("subscriptions" => $subscriptions));
    }

    /**
     * @Route("/subscription/newForm/{id}", name="subscription_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Subscription');
        $subscription = $repository->findOneById($id);
        if(empty($subscription)) {
            $subscription = new Subscription();
        }
        $languagesId = array();
        foreach ($subscription->getSubscriptionTrs() as $subscriptionTr) {
            $languagesId[] = $subscriptionTr->getLanguage()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $subscriptionTr = new SubscriptionTr();
            $subscriptionTr->setLanguage($language);
            $subscription->addSubscriptionTr($subscriptionTr);
        }
        $form = $this->createForm(SubscriptionType::class, $subscription, array(
            'action' => $this->generateUrl('subscription_submit'),
            'method' => 'POST'));
        return $this->render('admin/subscription/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/subscription/submit", name="subscription_submit")
     */
    public function submitAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $subscription = $request->request->all()["subscription"];
        $subscriptionTrs = $subscription["subscriptionTrs"];
        unset($subscription["subscriptionTrs"]);
        if(empty($subscription["id"])) {
            $response = $api->post($this->generateUrl('api_post_subscription'), $subscription);
        } else {
            $response = $api->put($this->generateUrl('api_put_subscription'), $subscription);
        }
        $subscription = $serializer->decode($response->getBody()->getContents());
        foreach ($subscriptionTrs as $subscriptionTr) {
            $subscriptionTr["subscription"] = $subscription["id"];
            if(empty($subscriptionTr["id"])) {
                $api->post($this->generateUrl('api_post_subscription_tr'), $subscriptionTr);
            } else {
                $api->put($this->generateUrl('api_put_subscription_tr'), $subscriptionTr);
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The subscription is well created/modified.");
        return $this->redirectToRoute('subscriptions');

    }
    /**
     * @Route("/subscription/delete/{id}", name="subscription_delete")
     */
    public function deleteAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_subscription', array('id' => $id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The subscription is well created/modified.");
        return $this->redirectToRoute('subscriptions');
    }

    /**
     * @Route("/subscription/activated/", name="subscription_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('_router')->generate('api_put_subscription_activated'), $request->request->all());
        return new Response(json_encode([200, "success"]));
    }
}