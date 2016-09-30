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
     * @Route("/characteristicCategory/newForm/{scopeId}/{id}", name="characteristicCategory_new_form")
     */
    public function newFormAction($scopeId, $id = '') {
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
        $caracteristicCategory->setScope($repository->findOneById($scopeId));
        $form = $this->createForm(CharacteristicCategoryType::class, $caracteristicCategory, array(
            'action' => $this->generateUrl('characteristicCategory_post'),
            'method' => 'POST'));
        return $this->render('admin/characteristic/form/characteristicCategory.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/characteristicCategory/", name="characteristicCategory_post")
     */
    public function postAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        //var_dump($request->request->all()["characteristic_category"]);
        $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/categories", $request->request->all()["characteristic_category"], null);
        //var_dump($request->request->all());
        return new Response();
    }

    /**
     * @Route("/characteristicCategory/delete/{id}", name="characteristicCategory_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $client = new Client();
        $response = $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/'.$id.'/category');
        var_dump($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The CharacteristicCategory is well deleted.");
        return $this->redirectToRoute('characteristic', ['scopeName' => 'RENTAL']);
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