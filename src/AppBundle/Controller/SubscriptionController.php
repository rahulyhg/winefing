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
use Winefing\ApiBundle\Entity\WineRegion;
use Winefing\ApiBundle\Entity\WineRegionTr;
use AppBundle\Form\WineRegionType;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;



class SubscriptionController extends Controller
{
    /**
     * @Route("/subscriptions", name="subscriptions")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->generateUrl('api_get_subscriptions'));
        $serializer = $this->container->get('winefing.serializer_controller');
        $subscriptions = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_languages_picture_path'));
        $languagePicturePath = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/subscription/index.html.twig', array("subscriptions" => $subscriptions, 'languagePicturePath'=>$languagePicturePath));
    }

    /**
     * @Route("/subscription/newForm/{id}", name="subscription_new_form")
     */
    public function newFormAction($id = '') {
        if(empty($id)) {
            $subscription = new WineRegion();
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $languages = $repository->findAll();
            foreach($languages as $language) {
                $subscriptionTr = new WineRegionTr();
                $subscriptionTr->setLanguage($language);
                $subscription->addWineRegionTr($subscriptionTr);
            }
        }
        else {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
            $subscription = $repository->findOneById($id);
        }
        $form = $this->createForm(WineRegionType::class, $subscription, array(
            'action' => $this->generateUrl('wineRegion_submit'),
            'method' => 'POST'));
        return $this->render('admin/wineRegion/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/subscription/submit", name="subscription_submit")
     */
    public function submitAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $subscription = $request->request->all()["wine_region"];
        $subscriptionTrs = $subscription["wineRegionTrs"];
        unset($subscription["wineRegionTrs"]);
        if(empty($subscription["id"])) {
            $response = $api->post($this->generateUrl('api_post_wine_region'), $subscription);
        } else {
            $response = $api->put($this->generateUrl('api_put_wine_region'), $subscription);
        }
        $subscription = $serializer->decode($response->getBody()->getContents());
        foreach ($subscriptionTrs as $subscriptionTr) {
            $subscriptionTr["wineRegion"] = $subscription["id"];
            if(empty($subscriptionTr["id"])) {
                $api->post($this->generateUrl('api_post_wineregion_tr'), $subscriptionTr);
            } else {
                $api->put($this->generateUrl('api_put_wineregion_tr'), $subscriptionTr);
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The wine Region is well created/modified.");
        return $this->redirectToRoute('wine_regions');

    }
    /**
     * @Route("/subscription/delete/{id}", name="subscription_delete")
     */
    public function deleteAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_wine_region', array('id' => $id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The wine Region is well created/modified.");
        return $this->redirectToRoute('wine_regions');
    }
}