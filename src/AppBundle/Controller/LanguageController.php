<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\LanguageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilderInterface;
use Winefing\ApiBundle\Entity\Language;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;

use GuzzleHttp\Client;

class LanguageController extends Controller
{
    /**
     * @Route("/language", name="language")
     */
    public function cgetAction() {
        $client = new Client();
        $response = $client->request('GET', 'http://104.47.146.137/winefing/web/app_dev.php/api/languages', []);
        $languagesJson = $response->getBody()->getContents();

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $languages = $serializer->decode($languagesJson, 'json');

        return $this->render('admin/language/index.html.twig', array(
            'languages' => $languages
        ));
    }
    /**
     * @Route("/language/createForm", name="language_new_empty_form")
     */
    public function newEmptyFormAction() {
        $form = $this->createForm(LanguageType::class, new Language(), array(
            'action' => $this->generateUrl('language_new'),
            'method' => 'POST'));
        return $this->render('admin/language/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/language/createForm/{id}", name="language_new_form")
     */
    public function newFormAction($id) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($id);
        $form = $this->createForm(LanguageType::class, $language, array(
            'action' => $this->generateUrl('language'),
            'method' => 'GET'));
        return $this->render('admin/language/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/language/new", name="language_new")
     */
    public function newAction(Request $request)
    {

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $json = $serializer->serialize($request, 'json');
        //$languages = $serializer->decode(curl_exec($ch), 'json');
        //var_dump(curl_exec($ch));
        //return $this->redirectToRoute('language');
        $client = new Client();
        //var_dump( $request->request->all());
        $response = $client->request('POST', 'http://104.47.146.137/winefing/web/app_dev.php/api/languages',  [ 'form_params' => $request->request->all() ]); #set body!
        var_dump($response->getBody()->getContents());
        return new Response();
    }

    public function editAction($id)
    {
    }
}