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
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use GuzzleHttp;
use GuzzleHttp\Client;
use GuzzleHttp\RequestOptions;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class LanguageController extends Controller
{
    /**
     * @Route("/languages", name="languages")
     */
    public function cgetAction() {
        $media = $this->container->get('winefing.media_format_controller');
        $f = $media->getMediaFormatExtentionsPossible(MediaFormatEnum::Icon);
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_languages'));
        $languages = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_languages_picture_path'));
        $picturePath = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/language/index.html.twig', array(
            'languages' => $languages, 'picturePath' => $picturePath)
        );
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
            'action' => $this->generateUrl('submit_language'),
            'method' => 'POST'));
        return $this->render('admin/language/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/language/submit", name="submit_language")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $language = $request->request->all()["language"];
        if(empty($language["id"])) {
            $response =  $api->post($this->get('router')->generate('api_post_language'), $language);
        } else {
            $response =  $api->put($this->get('router')->generate('api_put_language'), $language);
        }
        $language = $serializer->decode($response->getBody()->getContents());
        $picture = $request->files->all()["language"]["picture"];
        if($picture != null) {
            $api->file($this->get('router')->generate('api_post_language_file'), $language, $picture);
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The language is well created/modified.");
        return $this->redirectToRoute('languages');
    }

    /**
     * @Route("/language/delete/{id}", name="language_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_language', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The language is well deleted.");
        return $this->redirectToRoute('languages');
    }
}