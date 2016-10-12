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
     * @Route("/characteristic/newForm/{characteristicCategoryId}/{scopeName}/{id}", name="characteristic_new_form")
     */
    public function newFormAction($characteristicCategoryId, $scopeName, $id = '') {
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
            'action' => $this->generateUrl('characteristic_submit', array('scopeName'=> $scopeName)),
            'method' => 'POST'));
        return $this->render('admin/characteristic/form/characteristic.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/characteristic/submit/{scopeName}", name="characteristic_submit")
     */
    public function postAction($scopeName, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $characteristicTrs = $request->request->all()["characteristic"]["characteristicTrs"];
        $characteristic = $request->request->all()["characteristic"];
        unset($characteristic["characteristicTrs"]);
        if(empty($characteristic["id"])) {
            $response = $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/characteristics", $characteristic, null);
        } else {
            $response = $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/characteristic", $characteristic, null);
        }
        $characteristicJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $characteristic = $serializer->decode($characteristicJson, 'json');
        $characteristicId = $characteristic["id"];

        foreach($characteristicTrs as $characteristicTr) {
            $characteristicTr["characteristic"] = $characteristicId;
            if(empty($characteristicTr["id"])) {
                $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/trs", $characteristicTr, null);
            } else {
                $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/characteristic/tr", $characteristicTr, null);
            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The Characteristic is well modified/created.");
        return $this->redirectToRoute('characteristics_categories', ['scopeName' => $scopeName]);
    }

    /**
     * @Route("/characteristic/delete/{id}/{scopeName}", name="characteristic_delete")
     */
    public function deleteAction($id, $scopeName, Request $request)
    {
        $client = new Client();
        $response = $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/characteristics/'.$id);
        var_dump($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The Characteristic is well deleted.");
        return $this->redirectToRoute('characteristics_categories', ['scopeName' => $scopeName]);
    }

}