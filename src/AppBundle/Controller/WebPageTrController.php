<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\WebPageCategoryType;
use AppBundle\Form\WebPageType;
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
use Winefing\ApiBundle\Entity\WebPage;
use Winefing\ApiBundle\Entity\WebPageCategory;
use Winefing\ApiBundle\Entity\WebPageCategoryTr;
use Winefing\ApiBundle\Entity\WebPageTr;
use AppBundle\Form\WebPageTrType;


class WebPageTrController extends Controller
{
    /**
     * @Route("/webPageTr/{id}/{webPageId}/{languageId}", name="webPageTr_new_form")
     */
    public function newFormAction($id = '', $webPageId ='', $languageId = '', Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPageTr');
        $webPageTr = $repository->findOneById($id);
        if(empty($webPageTr)) {
            $webPageTr = new WebPageTr();
        }
        if(!empty($webPageId)) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WebPage');
            $webPage = $repository->findOneById($webPageId);
            $webPageTr->setWebPage($webPage);
        }
        if(!empty($languageId)) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $language = $repository->findOneById($languageId);
            $webPageTr->setLanguage($language);
        }
        $form = $this->createForm(WebPageTrType::class, $webPageTr, array(
            'action' => $this->generateUrl('webPageTr_submit'),
            'method' => 'POST'));
//        $form->get('language')->setData($language);
//        var_dump($languageId);


        return $this->render('admin/webPage/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/submit/webPageTr", name="webPageTr_submit")
     */
    public function submitAction(Request $request){
        $webPageId = $request->request->all()["web_page_tr"]["webPage"]["id"];
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        if(empty($webPageId)){
            $response = $api->post($this->get('_router')->generate('api_post_web_page'), $request->request->all()["web_page_tr"]["webPage"]);
            $webPage = $serializer->decode($response->getBody()->getContents());
            $webPageId = $webPage["id"];
        }
        $webPageTr = $request->request->all()["web_page_tr"];
        unset($webPageTr["webPage"]["id"]);
        $webPageTr["webPage"] = $webPageId;
        if(empty($webPageTr["id"])){
            $response = $api->post($this->get('_router')->generate('api_post_webpage_tr'), $webPageTr);
        } else {
            $response = $api->put($this->get('_router')->generate('api_put_webpage_tr'), $webPageTr);
        }
        $webPageTr = $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The webPage \"".$webPageTr["title"]."\" is well created/modified.");
        return $this->redirectToRoute('web_pages');
    }

    /**
     * @Route("/delete/webPageTr/{id}", name="webPageTr_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->delete($this->get('_router')->generate('api_delete_webpage_tr', array('id' => $id)));
        $webPageTr = $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The webPage \"".$webPageTr["title"]."\" is well deleted.");
        return $this->redirectToRoute('web_pages');
    }

    /**
     * @Route("/activated/webPageTr/", name="webPageTr_activated")
     */
    public function activatedAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('_router')->generate('api_put_webpage_tr_activated'), $request->request->all());
        return new Response();
    }

}