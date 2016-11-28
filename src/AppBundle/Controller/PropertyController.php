<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\CharacteristicValueType;
use AppBundle\Form\PropertyType;
use AppBundle\Form\AddressType;
use Symfony\Component\Form\Extension\Core\Type\CollectionType;

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
use Winefing\ApiBundle\Entity\Address;
use Winefing\ApiBundle\Entity\CharacteristicpropertyValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\CharacteristicValue;
use Winefing\ApiBundle\Entity\Scope;
use Winefing\ApiBundle\Entity\ScopeEnum;
use Winefing\ApiBundle\Entity\Property;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;


class PropertyController extends Controller
{
    /**
     * @Route("/properties", name="properties")
     */
    public function cgetAction() {
        $userId = 57;
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
//        $response = $response = $api->get($this->get('_router')->generate('api_get_properties', array('userId' => $userId)));
//        $properties = $serializer->decode($response->getBody()->getContents());
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $properties = $repository->findByUser($userId);
        var_dump(count($properties));
        $response = $api->get($this->get('_router')->generate('api_get_properties_picture_path'));
        $serializer = $this->container->get('winefing.serializer_controller');
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $this->render('host/property/index.html.twig', array(
            'properties' => $properties,
            'mediaPath' => $mediaPath));

    }
    /**
     * @Route("/property/edit/{id}", name="property_edit")
     */
    public function putAction($id) {
        $userId = 57;
        return $this->render('host/property/edit.html.twig');

    }
    /**
     * @Route("/property/new/{id}", name="property_new")
     */
    public function newAction($id = '') {
        $userId = 57;
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        if(!empty($id)) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
            $property = $repository->findOneById($id);
        } else {
            $property = new Property();
            $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
            $domain = $serializer->decode($response->getBody()->getContents());
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
            $domain = $repository->findOneById($domain["id"]);
            $domain = $this->getDoctrine()->getEntityManager()->merge($domain);
            $property->setAddress($domain->getAddress());
        }
        $propertyForm = $this->createForm(PropertyType::class, $property, array('action' => $this->generateUrl('property_address_submit')));
        $propertyForm->add('address', ChoiceType::class,
                array('choices' => array($this->get('translator')->trans('label.new_address') => '', $property->getAddress()->getFormattedAddress() => $property->getAddress()->getId()),
            'preferred_choices' => array($property->getAddress()->getId()),
            'data' => $property->getAddress()->getId()));
        $address = new Address();
        $addressForm = $this->createForm(AddressType::class, $address, array('action' => $this->generateUrl('domain_address_submit')));
        $this->setMissingCharacteristicsAction($property);
        $characteristicCategories = $this->getCharacteristicCategories($property);
        return $this->render('host/property/new.html.twig', array(
            'propertyForm' => $propertyForm->createView(),
            'addressForm'=>$addressForm->createView(),
            'characteristicCategories' => $characteristicCategories));
    }

    /**
     * @Route("/submit/property", name="property_submit")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response =  $api->post($this->get('router')->generate('api_post_property'), $request->request->all()["property"]);
        $property = $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The property is well modified.");
        return $this->redirect($this->generateUrl('property_new', array('id' => $property["id"])). '#step-2');
    }
    /**
     * @Route("/submit/pictures/property", name="property_pictures_submit")
     */
    public function submitPictureAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $property["property"] = $request->request->all()["property"];
        var_dump($request->files->all());
        foreach($request->files->all()["medias"] as $media) {
            var_dump("o");
            $api->file($this->get('router')->generate('api_post_property_picture'), $property, $media);
        }
        return $this->redirect($this->generateUrl('property_user'). '#pictures');
    }
    /**
     * @Route("/submit/address/property", name="property_address_submit")
     */
    public function submitAddressAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $property = $request->request->all()['address']['property'];
        $response =  $api->post($this->get('router')->generate('api_post_address'), $request->request->all()['address']);
        $address = $serializer->decode($response->getBody()->getContents());
        $body["address"] = $address["id"];
        $body["property"] = $property;
        $api->put($this->get('router')->generate('api_put_property_address'), $body);
        return $this->redirect($this->generateUrl('property_new', array('id' => $property)). '#step-4');
    }
    /**
     * @Route("/submit/characteristicpropertyValues", name="characteristic_property_value_submit")
     */
    public function submitCharacteristicPropertyValues(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $property = $request->request->all()["characteristicValueForm"]["property"];
        foreach($request->request->all()["characteristicValueForm"]["characteristicValue"] as $characteristicValue) {
            if (empty($characteristicValue["id"])) {
                var_dump("empty");
                $response = $api->post($this->get('router')->generate('api_post_characteristic_value'), $characteristicValue);
                $characteristicValue = $serializer->decode($response->getBody()->getContents());
                $characteristicValueProperty["property"] = $property;
                $characteristicValueProperty["characteristicValue"] = $characteristicValue["id"];
                $api->put($this->get('router')->generate('api_put_characteristic_value_property'), $characteristicValueProperty);
            } else {
                $api->put($this->get('router')->generate('api_put_characteristic_value'), $characteristicValue);
            }
        }
        return $this->redirect($this->generateUrl('property_new', array('id'=>$property)). '#step-3');
    }

    /**
     * @Route("/delete/property/{id}", name="property_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_property', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The property is well deleted.");
        return $this->redirectToRoute('property_user');
    }
    /**
     * @Route("/address/property/{userId}", name="property_address")
     */
    public function getAddressAction($userId = 57) {
        return $this->render('host/property/test.html.twig');
    }
    /**
     * @Route("/pictures/property/submit", name="property_pictures_submit")
     */
    public function getPictureAction() {
        $api = $this->container->get('winefing.api_controller');
//        $response = $api->get($this->get('_router')->generate('api_get_property_by_user', array('userId' => $userId)));
//        $serializer = $this->container->get('jms_serializer');
//        $property = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\property', 'json');
//        $address = $property->getAddress();
//        $propertyForm = $this->createForm(AddressType::class, $address, array(
//            'action' => $this->generateUrl('property_submit')));
        return $this->redirectToRoute('properties');
    }

    /**
     * @return array ["characteristicCateory"]["characteristicValueForms"]
     */
    public function createCharacteristicValueForms() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_characteristics', array('scopeName' => ScopeEnum::Property)));
        $characteristics= $serializer->decode($response->getBody()->getContents());
        $characteristicValues = array();
        foreach($characteristics as $characteristic) {
            $options = array();
            $options["characteristic"] = $characteristic["id"];
            $options["valueTypeLabel"] = $characteristic["characteristicTrs"][0]["name"];
            $options["valueType"] = $characteristic["format"]["name"];
            $form = $this->createForm(CharacteristicValueType::class, new CharacteristicValue(), $options);
            $characteristicValues[$characteristic["characteristicCategory"]["characteristicCategoryTrs"][0]["name"]][] = $form->createView();
        }
        return $characteristicValues;
    }

    public function setMissingCharacteristicsAction($property)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        if(!empty($property->getId())) {
            $response = $response = $api->get($this->get('_router')->generate('api_get_property_missing_characteristics', array('propertyId' => $property->getId())));
        } else {
            $response = $response = $api->get($this->get('_router')->generate('api_get_characteristics', array('scopeName' => ScopeEnum::Property)));
        }
        $missingCharacteristics = $serializer->decode($response->getBody()->getContents());
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicValue = new CharacteristicValue();
            $characteristic = $repository->findOneById($characteristic["id"]);
            $characteristicValue->setCharacteristic($characteristic);
            $property->addCharacteristicValue($characteristicValue);
        }
    }

    public function getCharacteristicCategories($property) {
        $list = array();
        foreach($property->getCharacteristicValues() as $characteristicValue) {
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