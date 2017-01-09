<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\BoxItemType;
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
use Winefing\ApiBundle\Entity\BoxItemTr;
use Winefing\ApiBundle\Entity\BoxItem;
use Winefing\ApiBundle\Entity\BoxTr;


class BoxItemController extends Controller
{
    protected $api;

    protected $serializer;

//    public function __construct(){
//        $this->api = $this->container->get('winefing.api_controller');
//        $this->serializer = $this->container->get('winefing.serializer_controller');
//    }
    /**
     * @Route("/box/item/form/{boxId}/{id}", name="box_item_form")
     */
    public function newFormAction($boxId, $id = '', Request $request) {
        if(empty($id)) {
            $boxItem = new BoxItem();
        } else {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:BoxItem');
            $boxItem = $repository->findOneById($id);
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($boxId);
        $boxItem->setBox($box);
        $this->setBoxItemTrs($boxItem);
        $option["language"] = $request->request->get('local');
        $boxItemForm = $this->createForm(BoxItemType::class, $boxItem, array(
            'action' => $this->generateUrl('box_item_submit_form', array('id' => $boxItem->getId())), 'language' => $request->request->get('local')));
        return $this->render('admin/box/item/form.html.twig', array("boxItemForm" => $boxItemForm->createView())
        );
    }
    /**
     * @Route("/box/item/submit/form", name="box_item_submit_form")
     */
    public function submitFormAction(Request $request) {
        $boxItemId = $request->request->get('box_item')['id'];
        $boxItemTrs = $request->request->get('box_item')['boxItemTrs'];
        if(empty($boxItemId)) {
            $boxItemId = $this->submitItemBox($request->request->get('box_item'));
        }
        $body['boxItem'] = $boxItemId;
        foreach($boxItemTrs as $boxItemTr) {
            $body['id'] = $boxItemTr['id'];
            $body['name'] = $boxItemTr['name'];
            $body['language'] = $boxItemTr['language'];
            $body['description'] = $boxItemTr['description'];
            $this->submitBoxItemTr($body);
        }
        return $this->redirectToRoute('boxes_admin');
    }
    public function submitItemBox($boxItem) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->post($this->get('router')->generate('api_post_box_item'), $boxItem);
        $boxItem = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxItem', 'json');
        return $boxItem->getId();
    }
    public function submitBoxItemTr($boxItemTr) {
        $api = $this->container->get('winefing.api_controller');
        if(empty($boxItemTr['id'])) {
            $api->post($this->get('router')->generate('api_post_boxitem_tr'), $boxItemTr);
        } else {
            $api->put($this->get('router')->generate('api_put_boxitem_tr'), $boxItemTr);
        }
    }
    public function setBoxItemTrs($boxItem){
        $languagesId = array();
        if(!empty($boxItem->getBoxItemTrs())) {
            foreach ($boxItem->getBoxItemTrs() as $boxItemTr) {
                $languagesId[] = $boxItemTr->getLanguage()->getId();
            }
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $boxItemTr = new BoxItemTr();
            $boxItemTr->setLanguage($language);
            $boxItem->addBoxItemTr($boxItemTr);
        }
    }
    /**
     * @Route("/box/item/delete/{id}", name="box_item_delete")
     */
    public function deleteBoxItem($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_box_item', array('id' => $id)));
        return $this->redirectToRoute('boxes_admin');
    }
}