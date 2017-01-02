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
     * Get all the properties for the current user
     * @return
     * @Route("/properties", name="properties")
     */
    public function cgetAction() {
        $userId = $this->getUser()->getId();
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
     * Edit the property of the current user (host).
     * This part allows to edit, the property's information or the property's address or the property's pictures.
     * @param $id, Request $request
     * @return
     * @Route("/property/edit/{id}", name="property_edit")
     */
    public function putAction($id, Request $request) {
        $return = array();
        $property = $this->getProperty($id);
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
        if (empty($address->getId())) {
            $addressListChoice = $this->createAddressListChoice($property);
            $return['addressListChoice'] = $addressListChoice->createView();
        }
        $addressForm = $this->createForm(AddressType::class, $address);
        $characteristicCategories = $this->getCharacteristicValuesByCategory($property->getId());

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
     * Create and handle the submition of a new property.
     * This correspond to the "step-1" of the creation of a new property.
     * @param Request $request
     * @return string
     * @Route("/property/new/", name="property_new")
     */
    public function newAction(Request $request) {
        $return = array();
        $propertyForm = $this->createForm(PropertyType::class, new Property());
        $propertyForm->handleRequest($request);
        if ($propertyForm->isSubmitted()) {
            if ($propertyForm->isValid()) {
                $propertyForm = $request->request->all()['property'];
                $propertyForm["id"] = '';
                $propertyForm["domain"] = $this->getDomainId();
                $property = $this->submit($propertyForm);
                return $this->redirectToRoute('property_characteristics', array('idProperty'=>$property->getId()));
            }
        }
        $return['propertyForm'] = $propertyForm->createView();
        return $this->render('host/property/new/property.html.twig', $return);
    }
    /**
     * Create and handle the submition of the property's characteristicValues (new or create new one for the characteristicMissing).
     * This correspond to the "step-2" of the creation of a new property.
     * @param $idProperty, Request $request
     * @return
     * @Route("/property/{idProperty}/characteristics", name="property_characteristics")
     */
    public function putPropertyCharacteristics($idProperty, Request $request){
        $property = $this->getProperty($idProperty);
        $characteristicCategories = $this->getCharacteristicValuesByCategory($property->getId());
        if ($request->isMethod('POST')) {
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($characteristicValueForm)) {
                $characteristicValueForm["property"] = $property->getId();
                $this->submitCharacteristicPropertyValues($characteristicValueForm);
                return $this->redirectToRoute('property_address', array('idProperty' => $idProperty));
            }
        }
        $return['characteristicCategories'] = $characteristicCategories;
        return $this->render('host/property/new/information.html.twig', $return);
    }
    /**
     * Create and handle the submition of a new property's address (in the case where the address is different from the domain's address).
     * This correspond to the "step-3" of the creation of a new property.
     * @param $idProperty, Request $request
     * @return
     * @Route("/property/{idProperty}/address", name="property_address")
     */
    public function putPropertyAddress($idProperty, Request $request) {
        $property = $this->getProperty($idProperty);
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
                return $this->redirectToRoute('property_pictures', array('idProperty' => $idProperty));
            }
        }

        $return['addressForm'] = $addressForm->createView();
        return $this->render('host/property/new/address.html.twig', $return);
    }
    /**
     * Create and handle the submition of a new property's picture (entity Media).
     * This correspond to the "step-3" of the creation of a new property.
     * @param $idProperty, Request $request
     * @return
     * @Route("/property/{idProperty}/pictures", name="property_pictures")
     */
    public function putPropertyPictures($idProperty, Request $request) {
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            if (count($media)['medias'] > 0) {
                $media["property"] = $idProperty;
                $this->submitPictures($media);
            }
            return $this->redirect($this->generateUrl('property_edit', array('id' => $idProperty)) . '#presentation');
        }
        return $this->render('host/property/new/picture.html.twig');
    }

    /**
     * Return the domain of the current user.
     * The user can have multiple domains (in the database, in case where winefing decide to change the way the website work).
     * But for the moment the host can have only one domain regarding the fonctional part of the website.
     * So the function return the first domain encountered
     * @return domainId
     */
    public function getDomainId() {
        $userId = $this->getUser()->getId();
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $domain->getId();
    }

    /**
     * Get the property for the id given.
     * @param $id
     * @return Property $property
     */
    public function getProperty($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_property', array('id' => $id)));
        $property = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Property', 'json');
        return $property;
    }
    /**
     * If the property's address = domain's address so a new address is return else the proerty's address is return.
     * @param Property $property
     * @return Address $address
     */
    public function getAddress($property) {
        if($property->isAddressDomain()) {
            $address = new Address();
        } else {
            $address = $property->getAddress();
        }
        return $address;
    }

    /**
     * Get the property domain's address entity.
     * @param $propertyId
     * @return Address $address
     */
    public function getPropertyDomainAddress($propertyId){
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_property_domain_address', array('propertyId' => $propertyId)));
        $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        return $address;
    }

    /**
     * If the property's address = domain's address a form is created with one field, a select item with the fomattedAddress of the domain's address and the text "new address".
     * @param Property $property
     * @return $form
     */
    public function createAddressListChoice($property) {
        $data = array();
        $address = $this->getPropertyDomainAddress($property->getId());
        return $form = $this->createFormBuilder($data)->add('address', ChoiceType::class,
                array('choices' => array($this->get('translator')->trans('label.new_address') => '', $address->getFormattedAddress() => $address->getId()),
                    'preferred_choices' => array($address->getId()),
                    'data' => $address->getId()))
            ->getForm();
    }

    /**
     * Get the path for the properties' pictures.
     * @return string
     */
    public function getMediaPath() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_property_media_path'));
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $mediaPath;
    }
    /**
     * Get the path for the rentals' pictures.
     * @return string
     */
    public function getRentalMediaPath() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_media_path'));
        $rentalMediaPath = $serializer->decode($response->getBody()->getContents());
        return $rentalMediaPath;
    }
    /**
     * Submit the property : if it's new one (empty id), call the post api route, else the put api route.
     * @param array
     * @return string
     */
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
    /**
     * Submit all the pictures : call for each picture the post api route to save the and upload the picture, and then the route allowing to create the link between the picture and the property
     * @param array
     */
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
    /**
     * Submit the property's address : if the address is new, call the post api route otherwise call the put api route and then the route allowing to create the link between the address and the property.
     * @param arrays
     * @return Address $address
     */
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

    /**
     * Submit the property's characteristics : foreach element is the characteristic's value's id is empty create a new characteristicValue() and then create the link between the property and the characteristicValue.
     * Else --> api put characteristicValue
     * @param array
     */
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
     * Delete a property.
     * @param $id, Request $request
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
     * Get all the characteristic not filled in, create a characteristic value and add it to the array.
     * @param $propertyId
     */
    public function getMissingCharacteristicValues($propertyId, $characteristicValues)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_property_missing_characteristics', array('propertyId' => $propertyId)));
        $missingCharacteristics = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Characteristic>', 'json');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicValue = new CharacteristicValue();
            $characteristicValue->setCharacteristic($characteristic);
            array_push($characteristicValues, $characteristicValue);
        }
        return $characteristicValues;
    }

    /**
     * Get all the characteristicValues of the property.
     * @param $propertyId
     * @return array of characteristicValues
     */
    public function getCharacteristicValues($propertyId)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_property_characteristic_values', array('propertyId' => $propertyId)));
        return $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\CharacteristicValue>', 'json');
    }

    /**
     * Get all the characteristicValues organize by characteristicCategory.
     * @param array of characteristicValues
     * @return array[characteristicCategory][] = [characteristicValue]
     */
    public function getCharacteristicCategories($characteristicValues) {
        $list = array();
        foreach($characteristicValues as $characteristicValue) {
            $list[$characteristicValue
                ->getCharacteristic()
                ->getCharacteristicCategory()
                ->getCharacteristicCategoryTrs()[0]
                ->getName()][]
                = $characteristicValue;
        }
        return $list;
    }

    /**
     * Get all the characteristicValue possible (the one already fill in by the host, and the one missing) by Category
     * @param $propertyId
     * @return array
     */
    public function getCharacteristicValuesByCategory($propertyId) {
        $characteristicValues = $this->getCharacteristicValues($propertyId);
        $characteristicValues = $this->getMissingCharacteristicValues($propertyId, $characteristicValues);
        $characteristicCategories = $this->getCharacteristicCategories($characteristicValues);
        return  $characteristicCategories;
    }
}