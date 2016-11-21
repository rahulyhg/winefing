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
use Winefing\ApiBundle\Entity\CharacteristicDomainValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\CharacteristicValue;


class DomainController extends Controller
{
    /**
     * @Route("/domain/{userId}", name="domain_user")
     */
    public function getAction($userId = 57) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $domain = $serializer->decode($response->getBody()->getContents());
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($domain["id"]);
        $domain = $this->getDoctrine()->getEntityManager()->merge($domain);
        $this->setMissingCharacteristicsAction($domain);
        $characteristicCategories = $this->getCharacteristicCategory($domain);
        $domainForm = $this->createForm(DomainType::class, $domain);
        $addressForm = $this->createForm(AddressType::class, $domain->getAddress(), array('action' => $this->generateUrl('domain_address_submit')));
        $response = $api->get($this->get('_router')->generate('api_get_domains_picture_path'));
        $picturePath = $serializer->decode($response->getBody()->getContents());
        return $this->render('host/domain/index.html.twig', array(
            'domainForm' => $domainForm->createView(),
            'addressForm'=>$addressForm->createView(),
            'domain' => $domain,
            'picturePath' => $picturePath,
            'characteristicCategories' => $characteristicCategories)
        );
    }

    /**
     * @Route("/submit/domain", name="domain_submit")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response =  $api->put($this->get('router')->generate('api_put_domain'), $request->request->all()['domain']);
        $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The domain is well modified.");
        return $this->redirectToRoute('domain_user');
    }
    /**
     * @Route("/submit/pictures/domain", name="domain_pictures_submit")
     */
    public function submitPictureAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $domain["domain"] = $request->request->all()["domain"];
        var_dump($request->files->all());
        foreach($request->files->all()["medias"] as $media) {
            var_dump("o");
            $api->file($this->get('router')->generate('api_post_domain_picture'), $domain, $media);
        }
        return $this->redirect($this->generateUrl('domain_user'). '#pictures');
    }
    /**
     * @Route("/submit/address/domain", name="domain_address_submit")
     */
    public function submitAddressAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $domain["domain"] = $request->request->all()["domain"];
        var_dump($request->files->all());
        foreach($request->files->all()["medias"] as $media) {
            var_dump("o");
            $api->file($this->get('router')->generate('api_post_domain_picture'), $domain, $media);
        }
        return $this->redirect($this->generateUrl('domain_user'). '#pictures');
    }
    /**
     * @Route("/submit/characteristicDomainValues", name="characteristic_domain_value_submit")
     */
    public function submitCharacteristicDomainValues(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $domain = $request->request->all()["characteristicValue"]["domain"];
        foreach($request->request->all()["characteristicValue"]["characteristicValue"] as $characteristicValue) {
            var_dump(empty($characteristicValue["id"]));
            if (empty($characteristicValue["id"])) {
                $response = $api->post($this->get('router')->generate('api_post_characteristic_value'), $characteristicValue);
                $characteristicValue = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\CharacteristicValue', 'json');
                $characteristicDomainValue["domain"] = $domain;
                $characteristicDomainValue["characteristicValue"] = $characteristicValue->getId();
                $api->put($this->get('router')->generate('api_put_characteristic_value_domain'), $characteristicDomainValue);
            } else {
                $api->put($this->get('router')->generate('api_put_characteristic_value'), $characteristicValue);
            }
        }
        return $this->redirect($this->generateUrl('domain_user'). '#informations');
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
    public function getAddressAction($userId = 57) {
        return $this->render('host/domain/test.html.twig');
    }
    /**
     * @Route("/pictures/domain/{userId}", name="domain_pictures")
     */
    public function getPictureAction($userId = 57) {
        $api = $this->container->get('winefing.api_controller');
//        $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
//        $serializer = $this->container->get('jms_serializer');
//        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
//        $address = $domain->getAddress();
//        $domainForm = $this->createForm(AddressType::class, $address, array(
//            'action' => $this->generateUrl('domain_submit')));
        return $this->render('host/domain/picture.html.twig');
    }

    public function setMissingCharacteristicsAction($domain)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_missing_characteristics', array('domainId' => $domain->getId())));
        $missingCharacteristics = $serializer->decode($response->getBody()->getContents());

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicValue = new CharacteristicValue();
            $characteristic = $repository->findOneById($characteristic["id"]);
            $characteristicValue->setCharacteristic($characteristic);
            $domain->addCharacteristicValue($characteristicValue);
        }
    }

    public function getCharacteristicCategory($domain) {
        $list = array();
        foreach($domain->getCharacteristicValues() as $characteristicValue) {
            $list[$characteristicValue
                    ->getCharacteristic()
                    ->getCharacteristicCategory()
                    ->getCharacteristicCategoryTrs()[0]
                    ->getName()][]
                = $characteristicValue;
        }
        return $list;
    }
}