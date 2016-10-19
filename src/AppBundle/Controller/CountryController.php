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
use AppBundle\Form\CountryType;
use GuzzleHttp\Client;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class CountryController extends Controller
{
    /**
     * @Route("/countries", name="countries")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('router')->generate('api_get_countries'));
        $countries = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/country/index.html.twig', array(
            'countries' => $countries));
    }
    /**
     * @Route("/country/newForm/{id}", name="country_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Country');
        if(empty($id)) {
        }
        $form = $this->createForm(CountryType::class, $repository->findOneById($id), array(
            'action' => $this->generateUrl('country_submit'),
            'method' => 'POST'));
        return $this->render('admin/country/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/country/submit", name="country_submit")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $country = $request->request->all()["country"];
        if(empty($country["id"])) {
            $response =  $api->post($this->get('router')->generate('api_post_country'), $country);
        } else {
            $response =  $api->put($this->get('router')->generate('api_post_country'), $country);
        }
        $serializer = $this->container->get('winefing.serializer_controller');
        $country = $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The Country \"".$country["name"]."\"is well created/modified.");
        return $this->redirectToRoute('countries');
    }

    /**
     * @Route("/country/delete/{id}", name="country_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_country', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The country is well deleted.");
        return $this->redirectToRoute('countries');
    }
}