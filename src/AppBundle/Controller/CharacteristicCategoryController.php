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
use Winefing\ApiBundle\Entity\Characteristic;
use Winefing\ApiBundle\Entity\CharacteristicCategoryTr;
use Winefing\ApiBundle\Entity\Language;
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
        $response = $api->get('http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/'.$scopeName.'/categories');
        $characteristicsJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $characteristicCategories = $serializer->decode($characteristicsJson, 'json');
        return $this->render('admin/characteristic/index.html.twig', array(
            'characteristicCategories' => $characteristicCategories, 'scopeName' => $scopeName));
    }
    /**
     * @Route("/characteristicCategory/newForm/{scopeName}/{id}", name="characteristicCategory_new_form")
     */
    public function newFormAction($scopeName, $id = '') {
        if(empty($id)) {
            $caracteristicCategory = new CharacteristicCategory();
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $languages = $repository->findAll();
            foreach($languages as $language) {
                $caracteristicCategoryTr = new CharacteristicCategoryTr();
                $caracteristicCategoryTr->setLanguage($language);
                $caracteristicCategory->addCharacteristicCategoryTr($caracteristicCategoryTr);
            }
        } else {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
            $caracteristicCategory = $repository->findOneById($id);
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Scope');
        $caracteristicCategory->setScope($repository->findOneByName($scopeName));
        $form = $this->createForm(CharacteristicCategoryType::class, $caracteristicCategory, array(
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
        $characteristicCategoryTrs = $request->request->all()["characteristic_category"]["characteristicCategoryTrs"];
        $characteristicCategory = $request->request->all()["characteristic_category"];
        unset($characteristicCategory["characteristicCategoryTrs"]);
        $response = $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/categories", $characteristicCategory, null);
        $characteristicCategoryJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $characteristicCategory = $serializer->decode($characteristicCategoryJson, 'json');
        $characteristicCategoryId = $characteristicCategory["id"];
        foreach($characteristicCategoryTrs as $characteristicCategoryTr) {
            $characteristicCategoryTr["characteristicCategory"] = $characteristicCategoryId;
            if(empty($characteristicCategoryTr["id"])) {
                $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/characteristiccategories/trs", $characteristicCategoryTr, null);
            } else {
                $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/characteristiccategory/tr", $characteristicCategoryTr, null);
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
        $client = new Client();
        $response = $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/'.$id.'/category');
        var_dump($response->getBody()->getContents());
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
        $api->put('http://104.47.146.137/winefing/web/app_dev.php/api/characteristic/category/activated', $request->request->all());
        return new Response(json_encode([200, "success"]));
    }
}