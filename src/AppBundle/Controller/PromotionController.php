<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\PromotionType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\Promotion;


class PromotionController extends Controller
{
    /**
     * @Route("/promotions", name="promotions")
     */
    public function cgetAction() {
        $client = new Client();
        $response = $client->request('GET', 'http://104.47.146.137/winefing/web/app_dev.php/api/promotions');
        $promotionsJson = $response->getBody()->getContents();
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $promotions = $serializer->decode($promotionsJson, 'json');
        return $this->render('admin/promotion/index.html.twig', array(
            'promotions' => $promotions));
    }
    /**
     * @Route("/promotion/newForm/{id}", name="promotion_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Promotion');
        $promotion = $repository->findOneById($id);
        $method = 'PUT';
        $action = $this->generateUrl('promotion_put');
        if(empty($promotion)) {
            $promotion = new Promotion();
            $method = 'POST';
            $action = $this->generateUrl('promotion_post');
        }
        $form = $this->createForm(PromotionType::class, $promotion, array(
            'action' => $action,
            'method' => $method));
        return $this->render('admin/promotion/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/promotion/post", name="promotion_post")
     */
    public function postAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/promotions", $request->request->all()["promotion"], null);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The promotion is well created.");
        return new Response();
        //return $this->redirectToRoute('promotion');
    }
    /**
     * @Route("/promotion/put", name="promotion_put")
     */
    public function putAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/promotion", $request->request->all()["promotion"]);

        $request->getSession()
            ->getFlashBag()
            ->add('success', "The promotion is well modified.");
        return $this->redirectToRoute('promotions');
    }

    /**
     * @Route("/promotion/delete/{id}", name="promotion_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $client = new Client();
        $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/promotions/'.$id);

        $request->getSession()
            ->getFlashBag()
            ->add('success', "The promotion is well deleted.");
        return $this->redirectToRoute('promotions');
    }
}