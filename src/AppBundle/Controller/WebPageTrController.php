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
            $language = $repository->findOneById($webPageId);
            $webPageTr->setLanguage($language);
        }
        $form = $this->createForm(WebPageTrType::class, $webPageTr, array(
            'action' => $this->generateUrl('webPageTr_submit'),
            'method' => 'POST'));

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
        if(empty($webPageId)){
            $response = $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/webs/pages", $request->request->all()["web_page_tr"]["webPage"], null);
            $webPageJson = $response->getBody()->getContents();
            $encoders = array(new JsonEncoder());
            $normalizers = array(new ObjectNormalizer());
            $serializer = new Serializer($normalizers, $encoders);
            $webPage = $serializer->decode($webPageJson, 'json');
            $webPageId = $webPage["id"];
        }
        $webPageTr = $request->request->all()["web_page_tr"];
        unset($webPageTr["webPage"]["id"]);
        $webPageTr["webPage"] = $webPageId;
        if(empty($webPageTr["id"])){
            $response = $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/webpages/trs", $webPageTr, null);
        } else {
            $response = $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/webpage/tr", $webPageTr, null);
        }
        $webPageTrJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $webPageTr = $serializer->decode($webPageTrJson, 'json');

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
        $client = new Client();
        $response = $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/webpages/'.$id.'/tr');
        $webPageTrJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $webPageTr = $serializer->decode($webPageTrJson, 'json');
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
        $response = $api->put('http://104.47.146.137/winefing/web/app_dev.php/api/webpage/tr/activated', $request->request->all());
        return new Response();
    }

}