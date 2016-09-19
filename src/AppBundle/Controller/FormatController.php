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
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class FormatController extends Controller
{
    /**
     * @Route("/format", name="format")
     */
    public function cgetAction() {
        $client = new Client();
        $response = $client->request('GET', 'http://104.47.146.137/winefing/web/app_dev.php/api/formats', []);
        $languagesJson = $response->getBody()->getContents();

        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);
        $formats = $serializer->decode($languagesJson, 'json');
        return $this->render('admin/format/index.html.twig', array(
            'formats' => $formats
        ));
    }
    /**
     * @Route("/language/createForm/{id}", name="language_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        if(empty($id)) {
            $language = new Language();
        } else {
            $language = $repository->findOneById($id);
        }
        $form = $this->createForm(LanguageType::class, $language, array(
            'action' => $this->generateUrl('post_language'),
            'method' => 'POST'));
        return $this->render('admin/language/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/format/post", name="post_format")
     */
    public function postAction(Request $request)
    {
        if(empty($request->request->all()["format"]["name"])) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "The name is mandatory.");
            return $this->redirectToRoute('language');
        } elseif(empty($request->request->all()["language"]["code"])) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "The code is mandatory.");
            return $this->redirectToRoute('language');
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        if(empty($request->request->all()["language"]["id"]) && !empty($repository->findOneByCode($request->request->all()["language"]['code']))) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "A language with this code already exist.");
            return $this->redirectToRoute('language');
        }
        $api = $this->container->get('winefing.api_controller');
        try {
            $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/languages", $request->request->all()["language"], $request->files->all()["language"]["picture"]);
        } catch(\Exception $e) {
            error_log($e->getMessage());
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The language is well created/modified.");
        return $this->redirectToRoute('format');
    }

    /**
     * @Route("/format/delete/{id}", name="language_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $client = new Client();
        try {
            $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/formats/'.$id);
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The language is well deleted.");
        return $this->redirectToRoute('format');
    }
}