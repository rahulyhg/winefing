<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\AddressType;
use AppBundle\Form\DomainType;
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
use Winefing\ApiBundle\Entity\ScopeEnum;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class DomainController extends Controller
{
    /**
     * @Route("/domain/{userId}", name="domain_user")
     */
    public function getAction($userId = 20) {
        $api = $this->container->get('winefing.api_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $serializer = $this->container->get('jms_serializer');
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        $domain = $this->getDoctrine()->getEntityManager()->merge($domain);
        $domainForm = $this->createForm(DomainType::class, $domain, array(
            'action' => $this->generateUrl('domain_submit')));
        return $this->render('host/domain/index.html.twig', array('domainForm' => $domainForm->createView()));
    }

    /**
     * @Route("/submit/domain", name="domain_submit")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response =  $api->put($this->get('router')->generate('api_put_domain'), $request->request->all()['domain']);
        $domain = $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The language is well modified.");
        return $this->redirectToRoute('domain_user');
    }
    /**
     * @Route("/submit/pictures/domain", name="domain_pictures_submit")
     */
    public function submitPictureAction(Request $request, $userId = 20)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $serializer = $this->container->get('jms_serializer');
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        foreach($request->files->all()["pictures"] as $picture) {
            $api->file($this->file('router')->generate('api_post_picture_domain'), $domain->getId(), $picture);
        }
//        var_dump($request->request->all());
//        $response =  $api->put($this->get('router')->generate('api_put_domain'), $request->request->all()['domain']);
//        $domain = $serializer->decode($response->getBody()->getContents());
//        $request->getSession()
//            ->getFlashBag()
//            ->add('success', "The language is well modified.");
//        return $this->redirectToRoute('domain_user');
        return new Response();
    }

    /**
     * @Route("/delete/domain/{id}", name="domain_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_domain', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The domain is well deleted.");
        return $this->redirectToRoute('domain_user');
    }
    /**
     * @Route("/address/domain/{userId}", name="domain_address")
     */
    public function getAddressAction($userId = 20) {
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $serializer = $this->container->get('jms_serializer');
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        $address = $domain->getAddress();
        $domainForm = $this->createForm(AddressType::class, $address, array(
            'action' => $this->generateUrl('domain_submit')));
        return $this->render('host/domain/address.html.twig', array('addressForm' => $domainForm->createView()));
    }
    /**
     * @Route("/pictures/domain/{userId}", name="domain_pictures")
     */
    public function getPictureAction($userId = 20) {
        $api = $this->container->get('winefing.api_controller');
//        $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
//        $serializer = $this->container->get('jms_serializer');
//        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
//        $address = $domain->getAddress();
//        $domainForm = $this->createForm(AddressType::class, $address, array(
//            'action' => $this->generateUrl('domain_submit')));
        return $this->render('host/domain/picture.html.twig');
    }

    public function getCharacteristicsAction($userId = 20){
        $api = $this->container->get("winefing.api_controller");
        $serializer = $this->container->get("winefing.serializer_controller");
        $response = $api->get($this->get('_router')->generate('api_get_characteristic_categories', array('scopeName' => ScopeEnum::Domain)));
        $characteristicCategories = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\CharacteristicCategory', 'json');
        foreach($characteristicCategories as $characteristicCategory) {
            foreach($characteristicCategory->getCharacteristics() as $characteristic) {
                
            }
        }

    }
}