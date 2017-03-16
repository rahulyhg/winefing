<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\ArticleCategoryType;
use AppBundle\Form\ArticleType;
use AppBundle\Form\BoxType;
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
use Winefing\ApiBundle\Entity\Article;
use Winefing\ApiBundle\Entity\ArticleCategory;
use Winefing\ApiBundle\Entity\ArticleCategoryTr;
use Winefing\ApiBundle\Entity\ArticleTr;
use Winefing\ApiBundle\Entity\Box;
use Winefing\ApiBundle\Entity\BoxTr;
use JMS\Serializer\SerializationContext;


class BoxController extends Controller
{

    /**
     * @Route("/boxes/", name="boxes")
     */
    public function cgetAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_boxes_by_language', array('language' => $request->getLocale())));
        $boxes = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Box>', 'json');
        return $this->render('user/box/index.html.twig', array("boxes" => $boxes));
    }
    /**
     * @Route("/box/{id}", name="box")
     */
    public function getAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_box', array('language' => $request->getLocale(), 'id'=>$id)));
        $box = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Box', 'json');
        return $this->render('user/box/card.html.twig', array("box" => $box));
    }

    /**
     * @Route("/admin/boxes/", name="boxes_admin")
     */
    public function cgetAdminAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_boxes'));
        $boxes = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Box>', 'json');
        return $this->render('admin/box/index.html.twig', array("boxes" => $boxes)
        );
    }

    /**
     * @Route("/boxes/items", name="boxes_items")
     */
    public function cgetItemsAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_box_items'));
        $items = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/box/item/index.html.twig', array("items" => $items)
        );
    }

    /**
     * @Route("/admin/box/new", name="box_new")
     */
    public function newAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $box = new Box();
        $languagesId = array();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $boxTr = new BoxTr();
            $boxTr->setLanguage($language);
            $box->addBoxTr($boxTr);
        }
        $boxForm = $this->createForm(BoxType::class, $box);
        $boxForm->handleRequest($request);
        if($boxForm->isSubmitted() && $boxForm->isValid()) {
            $boxId = $this->postBox($api, $serializer, $request->request->get('box'));
            $body['id'] = $boxId;
            $body['boxTrs'] = $request->request->get('box')['boxTrs'];
            $this->submitBoxTrs($body);
            $this->addFlash('success', $this->get('translator')->trans('success.generic_added'));
            return $this->redirectToRoute('box_edit', array('id'=>$boxId));
        }
        return $this->render('admin/box/form.html.twig', array(
            'boxForm' => $boxForm->createView()
        ));
    }
    /**
     * @Route("/box/{id}/upload/picture", name="box_upload_picture")
     */
    public function boxUploadPicture($id, Request $request) {
        $media = array();
        $logger = $this->get('logger');
        $logger->info($request->files->get('file'));
        ($request->files->get('file'));
        $media['media'] = $request->files->get('file');
        $media["box"] = $id;
        return new Response($this->submitPictures($media));
    }
    /**
     * @Route("/box/delete/picture/{id}", name="box_delete_picture")
     */
    public function domainDeletePicture($id) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_media', array('id'=>$id, "directoryUpload"=>"box_directory_upload")));
        return new Response();
    }
    /**
     * @Route("/box/edit/{id}", name="box_edit")
     */
    public function editAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($id);
        $box->setBoxOrdersNumber();

        $languagesId = array();
        foreach ($box->getBoxTrs() as $tr) {
            $languagesId[] = $tr->getLanguage()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $boxTr = new BoxTr();
            $boxTr->setLanguage($language);
            $box->addBoxTr($boxTr);
        }
        $boxForm = $this->createForm(BoxType::class, $box);
        if($box->getBoxOrdersNumber() > 0) {
            $boxForm->remove('boxTrs');
        }
        $boxForm->handleRequest($request);
        if($boxForm->isSubmitted() && $boxForm->isValid()) {
            $boxId = $this->putBox($api, $serializer, $request->request->get('box')['price'], $box->getId());
            $body = array();
            $body['id'] = $boxId;
            if(($box->getBoxOrdersNumber()== 0)) {
                $body['boxTrs'] = $request->request->get('box')['boxTrs'];
                $this->submitBoxTrs($body);
            }
            return $this->redirectToRoute('boxes_admin');
        }
        return $this->render('admin/box/form.html.twig', array(
            'boxForm' => $boxForm->createView(), 'medias'=>$serializer->serialize($box->getMedias(), 'json',SerializationContext::create()->setGroups(array('default')))
        ));
    }
    public function postBox($api, $serializer, $box) {
        $body["price"] = $box['price'];
        $response = $api->post($this->get('router')->generate('api_post_box'), $body);
        $box = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Box', "json");
        return $box->getId();
    }
    public function putBox($api, $serializer, $price, $id) {
        $body["price"] = $price;
        $body["id"] = $id;
        $response = $api->put($this->get('router')->generate('api_put_box'), $body);
        $box = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Box', "json");
        return $box->getId();
    }
    public function postMedias($boxId, $medias) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body['box'] = $boxId;
        foreach($medias as $media) {
            $uploadDirectory["upload_directory"] = $this->getParameter('box_directory_upload');
            $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media);
            $media = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Media', "json");
            $body["media"] = $media->getId();
            $api->put($this->get('router')->generate('api_put_media_box'), $body);
        }
    }
    public function submitBoxTrs($box) {
        $body = array();
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body['box'] = $box['id'];
        foreach($box['boxTrs'] as $boxTr){
            $body['name'] = $boxTr['name'];
            $body['description'] = $boxTr['description'];
            if(!empty($boxTr['id'])) {
                $body['id'] = $boxTr['id'];
                $api->put($this->get('_router')->generate('api_put_box_tr'), $body);
            } else {
                $body['language'] = $boxTr['language'];
                $response = $api->post($this->get('_router')->generate('api_post_box_tr'), $body);
                $boxTr = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\BoxTr', "json");
                $body['boxTr'] = $boxTr->getId();
                $api->put($this->get('_router')->generate('api_put_box_box_tr'), $body);

            }

        }
    }
    /**
     * @Route("/box/delete/{id}", name="box_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get("winefing.api_controller");
        $api->delete($this->get('router')->generate('api_delete_box', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The box is well deleted.");
        return $this->redirectToRoute('boxes_admin');
    }

    public function submitPictures($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["box"] = $media["box"];
        $uploadDirectory["upload_directory"] = $this->getParameter('box_directory_upload');
        $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media['media']);
        $jsonResponse = $response->getBody()->getContents();
        $media = $serializer->deserialize($jsonResponse, 'Winefing\ApiBundle\Entity\Media', "json");
        $body["media"] = $media->getId();
        $api->patch($this->get('router')->generate('api_patch_media_box'), $body);
        return $jsonResponse;
    }

}