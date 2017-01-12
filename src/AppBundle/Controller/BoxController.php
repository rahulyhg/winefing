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


class BoxController extends Controller
{

    /**
     * @Route("/boxes/", name="boxes")
     */
    public function cgetAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_boxes_by_language', array('language' => $request->getLocale())));
        $boxes = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_box_media_path'));
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $this->render('user/box/index.html.twig', array("boxes" => $boxes, 'mediaPath' => $mediaPath));
    }

    /**
     * @Route("/admin/boxes/", name="boxes_admin")
     */
    public function cgetAdminAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_boxes'));
        $boxes = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_box_media_path'));
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_languages_picture_path'));
        $languagePicturePath = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/box/index.html.twig', array("boxes" => $boxes, 'mediaPath' => $mediaPath, 'languagePicturePath'=>$languagePicturePath)
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
     * @Route("/box/new", name="box_new")
     */
    public function newAction(Request $request) {
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
            $boxId = $this->postBox($request->request->get('box'));
            $body['id'] = $boxId;
            $body['boxTrs'] = $request->request->get('box')['boxTrs'];
            $this->submitBoxTrs($body);
            $this->postMedias($boxId, $request->files->get('box')['medias']);
            return $this->redirectToRoute('boxes_admin');
        }
        return $this->render('admin/box/form.html.twig', array(
            'boxForm' => $boxForm->createView()
        ));
    }
    /**
     * @Route("/box/edit/{id}", name="box_edit")
     */
    public function editAction($id, Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Box');
        $box = $repository->findOneById($id);

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
            $boxId = $this->postBox($request->request->get('box'));
            $body = array();
            $body['id'] = $boxId;
            $body['boxTrs'] = $request->request->get('box')['boxTrs'];
            $this->submitBoxTrs($body);
            $this->postMedias($boxId, $request->files->get('box')['medias']);
            return $this->redirectToRoute('boxes_admin');
        }
        return $this->render('admin/box/form.html.twig', array(
            'boxForm' => $boxForm->createView()
        ));
    }
    public function postBox($box) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["price"] = $box['price'];
        $response = $api->post($this->get('router')->generate('api_post_box'), $body);
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
}