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
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristic->setChacarteristicCategory($repository->findOneById($characteristicCategoryId));
        $languagesId = array();
        foreach ($characteristic->getCharacteristicTrs() as $characteristicTr) {
            $languagesId[] = $characteristicTr->getLanguage()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $characteristicTr = new CharacteristicTr();
            $characteristicTr->setLanguage($language);
            $characteristic->addCharacteristicTr($characteristicTr);
        }
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
        $serializer = $this->container->get('winefing.serializer_controller');
        $characteristicTrs = $request->request->all()["characteristic"]["characteristicTrs"];
        $characteristic = $request->request->all()["characteristic"];
        unset($characteristic["characteristicTrs"]);
        if(empty($characteristic["id"])) {
            $response = $api->post($this->get('_router')->generate('api_post_characteristic'), $characteristic);
        } else {
            $response = $api->put($this->get('_router')->generate('api_put_characteristic'), $characteristic);
        }
        $characteristic = $serializer->decode($response->getBody()->getContents());
        $characteristicId["id"] = $characteristic["id"];
        $picture = $request->files->all()["characteristic"]["picture"];
        if(!empty($picture)) {
            $api->file($this->get('_router')->generate('api_post_characteristic_file'), $characteristicId, $picture);
        }
        foreach($characteristicTrs as $characteristicTr) {
            $characteristicTr["characteristic"] = $characteristicId["id"];
            if(empty($characteristicTr["id"])) {
                $api->post($this->get('_router')->generate('api_post_characteristic_tr'), $characteristicTr);
            } else {
                $api->put($this->get('_router')->generate('api_put_characteristic_tr'), $characteristicTr);
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
        $api = $this->container->get('winefing.api_controller');
        $response = $api->delete($this->get('_router')->generate('api_delete_characteristic', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The Characteristic is well deleted.");
        return $this->redirectToRoute('characteristics_categories', ['scopeName' => $scopeName]);
    }

    /**
     * @Route("/characteristic/activated/", name="characteristic_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('_router')->generate('api_put_characteristic_activated'), $request->request->all());
        return new Response(json_encode([200, "success"]));
    }

}