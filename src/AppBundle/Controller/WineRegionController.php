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



class WineRegionController extends Controller
{
    /**
     * @Route("/wine/regions", name="wine_regions")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->generateUrl('api_get_wine_regions'));
        $serializer = $this->container->get('winefing.serializer_controller');
        $wineRegions = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_languages_picture_path'));
        $languagePicturePath = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/wineRegion/index.html.twig', array("wineRegions" => $wineRegions, 'languagePicturePath'=>$languagePicturePath));
    }

    /**
     * @Route("/wineRegion/newForm/{id}", name="wineRegion_new_form")
     */
    public function newFormAction($id = '') {
        if(empty($id)) {
            $wineRegion = new WineRegion();
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $languages = $repository->findAll();
            foreach($languages as $language) {
                $wineRegionTr = new WineRegionTr();
                $wineRegionTr->setLanguage($language);
                $wineRegion->addWineRegionTr($wineRegionTr);
            }
        }
        else {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
            $wineRegion = $repository->findOneById($id);
        }
        $form = $this->createForm(WineRegionType::class, $wineRegion, array(
            'action' => $this->generateUrl('wineRegion_submit'),
            'method' => 'POST'));
        return $this->render('admin/wineRegion/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/wineRegion/submit", name="wineRegion_submit")
     */
    public function submitAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $wineRegion = $request->request->all()["wine_region"];
        $wineRegionTrs = $wineRegion["wineRegionTrs"];
        unset($wineRegion["wineRegionTrs"]);
        if(empty($wineRegion["id"])) {
            $response = $api->post($this->generateUrl('api_post_wine_region'), $wineRegion);
        } else {
            $response = $api->put($this->generateUrl('api_put_wine_region'), $wineRegion);
        }
        $wineRegion = $serializer->decode($response->getBody()->getContents());
        foreach ($wineRegionTrs as $wineRegionTr) {
            $wineRegionTr["wineRegion"] = $wineRegion["id"];
            if(empty($wineRegionTr["id"])) {
                $api->post($this->generateUrl('api_post_wineregion_tr'), $wineRegionTr);
            } else {
                $api->put($this->generateUrl('api_put_wineregion_tr'), $wineRegionTr);
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The wine Region is well created/modified.");
        return $this->redirectToRoute('wine_regions');

    }
    /**
     * @Route("/wineRegion/delete/{id}", name="wineRegion_delete")
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