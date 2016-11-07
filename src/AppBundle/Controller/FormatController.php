<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\FormatType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Winefing\ApiBundle\Entity\Format;
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
     * @Route("/formats", name="formats")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_formats'));
        $formats = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/format/index.html.twig', array(
            'formats' => $formats, 'entity' => 'format'
        ));
    }
    /**
     * @Route("/format/newForm/{id}", name="format_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        if(empty($id)) {
            $form = $this->createForm(FormatType::class, new Format(), array(
                'action' => $this->generateUrl('format_post'),
                'method' => 'POST'));
        } else {
            $form = $this->createForm(FormatType::class, $repository->findOneById($id), array(
                'action' => $this->generateUrl('format_put'),
                'method' => 'PUT'));
        }
        return $this->render('admin/format/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/format/post", name="format_post")
     */
    public function postAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        if(empty($request->request->all()["format"]["name"])) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "The name is mandatory.");
            return $this->redirectToRoute('format');
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        if(!empty($repository->findOneByName($request->request->all()["format"]['name']))) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "A format with this name already exist.");
            return $this->redirectToRoute('format');
        }
        $api->post($this->get('router')->generate('api_post_format'),  $request->request->all()["format"]);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The format is well created/modified.");
        return $this->redirectToRoute('formats');
    }
    /**
     * @Route("/format/put", name="format_put")
     */
    public function putAction(Request $request)
    {
        if(empty($request->request->all()["format"]["name"])) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "The name is mandatory.");
            return $this->redirectToRoute('format');
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $format = $repository->findOneByName($request->request->all()["format"]['name']);
        if(!empty($format) && ($format->getId() != $request->request->all()["format"]["id"])) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "A format with this name already exist.");
            return $this->redirectToRoute('formats');
        }
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_format'),  $request->request->all()["format"]);

        $request->getSession()
            ->getFlashBag()
            ->add('success', "The format is well created/modified.");
        return $this->redirectToRoute('formats');
    }

    /**
     * @Route("/format/delete/{id}", name="format_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get("_router")->generate('api_delete_format', array('id' => $id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The format is well deleted.");
        return $this->redirectToRoute('formats');
    }
}