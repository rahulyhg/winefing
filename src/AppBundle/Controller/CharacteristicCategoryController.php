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
use AppBundle\Form\CharacteristicCategoryType;
use Winefing\ApiBundle\Entity\CharacteristicCategoryTr;
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class CharacteristicCategoryController extends Controller
{
    /**
     * @Route("/characteristics/categories/{scopeName}", name="characteristics_categories")
     */
    public function cgetAction($scopeName) {
        $api = $this->container->get("winefing.api_controller");
        $serializer = $this->container->get("winefing.serializer_controller");
        $response = $api->get($this->get('_router')->generate('api_get_characteristic_categories', array('scopeName' => $scopeName)));
        $characteristicCategories = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/characteristic/index.html.twig', array(
            'characteristicCategories' => $characteristicCategories, 'scopeName' => $scopeName));
    }
    /**
     * @Route("/characteristicCategory/newForm/{scopeName}/{id}", name="characteristicCategory_new_form")
     */
    public function newFormAction($scopeName, $id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($id);
        if(empty($characteristicCategory)) {
            $characteristicCategory = new CharacteristicCategory();
        }
        $languagesId = array();
        foreach ($characteristicCategory->getCharacteristicCategoryTrs() as $characteristicCategoryTr) {
            $languagesId[] = $characteristicCategoryTr->getLanguage()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $characteristicCategoryTr = new CharacteristicCategoryTr();
            $characteristicCategoryTr->setLanguage($language);
            $characteristicCategory->addCharacteristicCategoryTr($characteristicCategoryTr);
        }

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Scope');
        $characteristicCategory->setScope($repository->findOneByName($scopeName));
        $form = $this->createForm(CharacteristicCategoryType::class, $characteristicCategory, array(
            'action' => $this->generateUrl('characteristicCategory_post', array('scopeName' => $scopeName)),
            'method' => 'POST'));
        return $this->render('admin/characteristic/form/characteristicCategory.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/characteristicCategory/{scopeName}", name="characteristicCategory_post")
     */
    public function postAction($scopeName, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("winefing.serializer_controller");
        $characteristicCategoryTrs = $request->request->all()["characteristic_category"]["characteristicCategoryTrs"];
        $characteristicCategory = $request->request->all()["characteristic_category"];
        unset($characteristicCategory["characteristicCategoryTrs"]);
        if(empty($characteristicCategory["id"])) {
            $response = $api->post($this->get('_router')->generate('api_post_characteristic_category'), $characteristicCategory);
        } else {
            $response = $api->put($this->get('_router')->generate('api_put_characteristic_category'), $characteristicCategory);
        }
        $characteristicCategory = $serializer->decode($response->getBody()->getContents());
        $characteristicCategoryId["id"] = $characteristicCategory["id"];
        $picture = $request->files->all()["characteristic_category"]["picture"];
        if(!empty($picture)) {
            $api->file($this->get('_router')->generate('api_post_characteristic_category_file'), $characteristicCategoryId, $picture);
        }
        foreach($characteristicCategoryTrs as $characteristicCategoryTr) {
            $characteristicCategoryTr["characteristicCategory"] = $characteristicCategoryId["id"];
            if(empty($characteristicCategoryTr["id"])) {
                $api->post($this->get('_router')->generate('api_post_characteristiccategory_tr'), $characteristicCategoryTr);
            } else {
                $api->put($this->get('_router')->generate('api_put_characteristiccategory_tr'), $characteristicCategoryTr);
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The characteristic's category is well created/modified.");
        return $this->redirectToRoute('characteristics_categories', ['scopeName' => $scopeName]);
    }

    /**
     * @Route("/characteristicCategory/delete/{scopeName}/{id}", name="characteristicCategory_delete")
     */
    public function deleteAction($scopeName, $id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('_router')->generate('api_delete_characteristic_category', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The CharacteristicCategory is well deleted.");
        return $this->redirectToRoute('characteristics_categories', ['scopeName' => $scopeName]);
        //return new Response();
    }
    /**
     * @Route("/characteristicCategory/activated/", name="characteristicCategory_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('_router')->generate('api_put_characteristic_category_activated'), $request->request->all());
        return new Response(json_encode([200, "success"]));
    }
}