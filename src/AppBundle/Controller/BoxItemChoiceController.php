<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\BoxItemChoiceType;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Winefing\ApiBundle\Entity\BoxItemChoice;
use Winefing\ApiBundle\Entity\BoxItemChoiceTr;


class BoxItemChoiceController extends Controller
{
    /**
     * @Route("/box/item/choice/form/{boxItemId}/{id}", name="box_item_choice_form")
     */
    public function newFormAction($boxItemId, $id = '') {
        if(empty($id)) {
            $boxItemChoice = new BoxItemChoice();
        } else {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItemChoice');
            $boxItemChoice = $repository->findOneById($id);
        }
        var_dump($boxItemChoice->getId());
        $this->setBoxItemChoiceTrs($boxItemChoice);
        $boxItemChoiceForm = $this->createForm(BoxItemChoiceType::class, $boxItemChoice, array(
            'action' => $this->generateUrl('box_item_choice_submit_form', array('boxItemId' => $boxItemId))));
        return $this->render('admin/box/item/itemChoiceForm.html.twig', array("boxItemChoiceForm" => $boxItemChoiceForm->createView())
        );
    }
    /**
     * @Route("/box/item/{boxItemId}/choice/submit/form", name="box_item_choice_submit_form")
     */
    public function submitFormAction($boxItemId, Request $request) {
        $boxItemChoiceId = $request->request->get('box_item_choice')['id'];
        $boxItemChoiceTrs = $request->request->get('box_item_choice')['boxItemChoiceTrs'];
        if(empty($boxItemChoiceId)) {
            $boxItemChoiceId = $this->submitBoxItemChoice($boxItemId);
        }
        $body['boxItemChoice'] = $boxItemChoiceId;
        var_dump($body['boxItemChoice']);
        foreach($boxItemChoiceTrs as $boxItemChoiceTr) {
            $body['id'] = $boxItemChoiceTr['id'];
            $body['name'] = $boxItemChoiceTr['name'];
            $body['language'] = $boxItemChoiceTr['language'];
            $body['description'] = $boxItemChoiceTr['description'];
            $this->submitBoxItemChoiceTr($body);
        }
        return $this->redirectToRoute('boxes_admin');
    }
    public function submitBoxItemChoice($boxItemId) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body['boxItem'] = $boxItemId;
        $response = $api->post($this->get('router')->generate('api_post_boxitem_choice'), $body);
        $boxItemChoice = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxItemChoice', 'json');
        return $boxItemChoice->getId();
    }
    public function submitBoxItemChoiceTr($boxItemChoiceTr) {
        $api = $this->container->get('winefing.api_controller');
        if(empty($boxItemChoiceTr['id'])) {
            $api->post($this->get('router')->generate('api_post_boxitemchoice_tr'), $boxItemChoiceTr);
        } else {
            $api->put($this->get('router')->generate('api_put_boxitemchoice_tr'), $boxItemChoiceTr);
        }
    }
    public function setBoxItemChoiceTrs($boxItemChoice){
        $languagesId = array();
        if(!empty($boxItemChoice->getBoxItemChoiceTrs())) {
            foreach ($boxItemChoice->getBoxItemChoiceTrs() as $boxItemChoiceTr) {
                $languagesId[] = $boxItemChoiceTr->getLanguage()->getId();
            }
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $boxItemChoiceTr = new BoxItemChoiceTr();
            $boxItemChoiceTr->setLanguage($language);
            $boxItemChoice->addBoxItemChoiceTr($boxItemChoiceTr);
        }
    }

    /**
     * @Route("/box/item/choice/delete/{id}", name="box_item_choice_delete")
     */
    public function deleteBoxItem($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_boxitem_choice', array('id' => $id)));
        return $this->redirectToRoute('boxes_admin');
    }
}