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
use Winefing\ApiBundle\Entity\ScopeEnum;
use Winefing\ApiBundle\Entity\Property;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use FOS\RestBundle\Controller\Annotations\RequestParam;

class PropertyController extends Controller
{
    /**
     * @Route("/properties", name="properties")
     */
    public function cgetAction() {
        $userId = 57;
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_properties_by_user', array('userId' => $userId)));
        $properties = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Property>', 'json');
        $response = $api->get($this->get('_router')->generate('api_get_property_media_path'));
        $serializer = $this->container->get('winefing.serializer_controller');
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $this->render('host/property/index.html.twig', array(
            'properties' => $properties,
            'mediaPath' => $mediaPath));
    }
    /**
     * @Route("/property/edit/{id}", name="property_edit")
     */
    public function putAction($id, Request $request) {
        $return = array();
        $property = $this->getProperty($id);

        $property = $this->getDoctrine()->getEntityManager()->merge($property);
        $propertyForm =  $this->createForm(PropertyType::class, $property);
        $propertyForm->handleRequest($request);
        if ($propertyForm->isSubmitted()) {
            if($propertyForm->isValid()) {
                $propertyForm = $request->request->all()['property'];
                $propertyForm["id"] = $property->getId();
                $property = $this->submit($propertyForm);
            }
            return $this->redirect($this->generateUrl('property_edit', array('id'=> $property->getId())). '#presentation');
        }
        $return['propertyForm'] = $propertyForm->createView();


        $address = $this->getAddress($property);
        $addressForm = $this->createForm(AddressType::class, $address);
        if (empty($address->getId())) {
            $addressListChoice = $this->createAddressListChoice($property);
            $return['addressListChoice'] = $addressListChoice->createView();
        }

        $this->setMissingCharacteristicsAction($property);
        $characteristicCategories = $this->getCharacteristicCategories($property);

        $mediaPath = $this->getMediaPath();
        $rentalMediaPath = $this->getRentalMediaPath();

        $addressForm->handleRequest($request);
        if ($addressForm->isSubmitted()) {
            if ($addressForm->isValid()) {
                $addressForm = $request->request->all()['address'];
                $addressForm["property"] = $property->getId();
                $addressForm["id"] = $address->getId();
                $this->submitAddress($addressForm);
            }
            return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId())) . '#address');
        }
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($media)) {
                $media["property"] = $property->getId();
                $this->submitPictures($media);
                return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId())) . '#medias');
            } elseif (!empty($characteristicValueForm)) {
                $characteristicValueForm["property"] = $property->getId();
                $this->submitCharacteristicPropertyValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId())) . '#informations');
            }
        }
        $return['addressForm'] = $addressForm->createView();
        $return['characteristicCategories'] = $characteristicCategories;
        $return['medias'] = $property->getMedias();
        $return['rentals'] = $property->getRentals();
        $return['mediaPath'] = $mediaPath;
        $return['rentalMediaPath'] = $rentalMediaPath;
        return $this->render('host/property/edit.html.twig', $return);
    }
    /**
     * @Route("/property/new/{step}/{id}", name="property_new")
     */
    public function newAction($step, $id = '', Request $request) {
        $return = array();
        if(empty($id)) {
            $property = new Property();
        } else {
            $property = $this->getProperty($id);
        }
        $property = $this->getDoctrine()->getEntityManager()->merge($property);

        if($step == "step-1") {
            $propertyForm = $this->createForm(PropertyType::class, $property);
            $propertyForm->handleRequest($request);
            if ($propertyForm->isSubmitted()) {
                if ($propertyForm->isValid()) {
                    $propertyForm = $request->request->all()['property'];
                    $propertyForm["id"] = $property->getId();
                    $property = $this->submit($propertyForm);
                }
                return $this->redirect($this->generateUrl('property_new', array('step' => 'step-2', 'id' => $property->getId())) . '#step-2');

            }
            $return['propertyForm'] = $propertyForm->createView();
        }
        if($step == 'step-3') {
            $address = $this->getAddress($property);
            $addressForm = $this->createForm(AddressType::class, $address);
            if (empty($address->getId())) {
                $addressListChoice = $this->createAddressListChoice($property);
                $return['addressListChoice'] = $addressListChoice->createView();
            }
            $addressForm->handleRequest($request);
            if ($addressForm->isSubmitted()) {
                if ($addressForm->isValid()) {
                    $addressForm = $request->request->all()['address'];
                    $addressForm["property"] = $property->getId();
                    $addressForm["id"] = $address->getId();
                    $this->submitAddress($addressForm);
                }
                return $this->redirect($this->generateUrl('property_new', array('step' => 'step-3', 'id' => $property->getId())) . '#step-3');

            }
            $return['addressForm'] = $addressForm->createView();
        }
        if($step == 'step-4') {
            if ($request->isMethod('POST')) {
                $media = $request->files->get('media');
                if (!empty($media)) {
                    $media["property"] = $property->getId();
                    $this->submitPictures($media);
                    return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId())) . '#presentation');

                }
            }
        }
        if($step == 'step-2') {
            $this->setMissingCharacteristicsAction($property);
            $characteristicCategories = $this->getCharacteristicCategories($property);
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($characteristicValueForm)) {
                $characteristicValueForm["property"] = $property->getId();
                $this->submitCharacteristicPropertyValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('property_new', array('step' => 'step-4', 'id' => $property->getId())) . '#step-4');

            }
            $return['characteristicCategories'] = $characteristicCategories;
        }
        return $this->render('host/property/new.html.twig', $return);
    }
    public function getProperty($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_property', array('id' => $id)));
        $property = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Property', 'json');
        return $property;
    }
    /**
     *
     * @param $property
     * @return mixed
     */
    public function getAddress($property) {
        if($property->isAddressDomain()) {
            $address = new Address();
        } else {
            $address = $property->getAddress();
        }
        return $address;
    }
    public function createAddressListChoice($property) {
        $data = array();
        return $form = $this->createFormBuilder($data)->add('address', ChoiceType::class,
                array('choices' => array($this->get('translator')->trans('label.new_address') => '', $property->getAddress()->getFormattedAddress() => $property->getAddress()->getId()),
                    'preferred_choices' => array($property->getAddress()->getId()),
                    'data' => $property->getAddress()->getId()))
            ->getForm();
    }

    public function getMediaPath() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_property_media_path'));
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $mediaPath;
    }
    public function getRentalMediaPath() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_media_path'));
        $rentalMediaPath = $serializer->decode($response->getBody()->getContents());
        return $rentalMediaPath;
    }
    public function submit($property)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        if(empty($property["id"])) {
            $response = $api->post($this->get('router')->generate('api_post_property'), $property);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_property'), $property);
        }
        $property = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Property', 'json');
        return $property;
    }

    public function submitPictures($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["property"] = $media["property"];
        foreach($media["medias"] as $media) {
            $uploadDirectory["upload_directory"] = $this->getParameter('property_directory_upload');
            $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media);
            $media = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Media', "json");
            $body["media"] = $media->getId();
            $api->put($this->get('router')->generate('api_put_media_property'), $body);
        }
    }

    public function submitAddress($address) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $property = $address['property'];
        if(empty($address["id"])) {
            $response =  $api->post($this->get('router')->generate('api_post_address'), $address);
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
            $body["address"] = $address->getId();
            $body["property"] = $property;
            $api->put($this->get('router')->generate('api_put_property_address'), $body);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_address'), $address);
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        }
        return $address;
    }
    public function submitCharacteristicPropertyValues($characteristicValueForm) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $property = $characteristicValueForm["property"];
        foreach($characteristicValueForm["characteristicValue"] as $characteristicValue) {
            if (empty($characteristicValue["id"])) {
                $response = $api->post($this->get('router')->generate('api_post_characteristic_value'), $characteristicValue);
                $characteristicValue = $serializer->decode($response->getBody()->getContents());
                $characteristicValueProperty["property"] = $property;
                $characteristicValueProperty["characteristicValue"] = $characteristicValue["id"];
                $api->put($this->get('router')->generate('api_put_characteristic_value_property'), $characteristicValueProperty);
            } else {
                $api->put($this->get('router')->generate('api_put_characteristic_value'), $characteristicValue);
            }
        }
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