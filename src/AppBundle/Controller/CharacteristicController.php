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
use AppBundle\Form\CharacteristicType;
use Winefing\ApiBundle\Entity\Characteristic;
use Winefing\ApiBundle\Entity\CharacteristicTr;
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




class CharacteristicController extends Controller
{
    /**
     * @Route("/characteristic/{scopeName}", name="characteristic")
     */
    public function cgetAction($scopeName) {
        $client = new Client();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Scope');
        $scope = $repository->findOneByName($scopeName);
        $characteristicCategories = $scope->getCharacteristicCategories();
//        $test = reset($characteristicCategories);
//        $characteristics = $test[0]->getCharacteristics();
//        $t = reset($characteristics);
//        echo '<pre>';
//        var_dump($t[0]->getDescription());
//        echo '</pre>';

        //$repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        //$categories = $repository->findByScope($scope);
        //var_dump($categories);
        //$repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        //$characteristics =  $repository->findByCharacteristicCategory($categories);
        //var_dump($characteristics);
/*        $response = $client->request('GET', 'http://104.47.146.137/winefing/web/app_dev.php/api/characteristics', []);
        $formatsJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $characteristics = $serializer->decode($formatsJson, 'json');*/
        return $this->render('admin/characteristic/index.html.twig', array(
            'characteristicCategories' => $characteristicCategories));

        //return new Response();
    }
    /**
     * @Route("/characteristic/newForm/{characteristicCategoryId}/{id}", name="characteristic_new_form")
     */
    public function newFormAction($characteristicCategoryId, $id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($id);
        if(empty($characteristic)) {
            $characteristic = new Characteristic();
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $languages = $repository->findAll();
            foreach($languages as $language) {
                $caracteristicTr = new CharacteristicTr();
                $caracteristicTr->setLanguage($language);
                $characteristic->addCharacteristicTr($caracteristicTr);
            }
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristic->setChacarteristicCategory($repository->findOneById($characteristicCategoryId));
        $form = $this->createForm(CharacteristicType::class, $characteristic, array(
            'action' => $this->generateUrl('characteristic_post'),
            'method' => 'POST'));
        return $this->render('admin/characteristic/form/characteristic.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/characteristic/", name="characteristic_post")
     */
    public function postAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/characteristics", $request->request->all()["characteristic"], null);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The Characteristic is well modified/created.");
        return new Response();
        //return $this->redirectToRoute('characteristic', ['scopeName' => 'RENTAL']);

    }

    /**
     * @Route("/characteristic/delete/{id}", name="characteristic_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $client = new Client();
        $response = $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/'.$id);
        var_dump($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The Characteristic is well deleted.");
        return $this->redirectToRoute('characteristic', ['scopeName' => 'RENTAL']);
    }

}