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
     * @Route("host/domain/{id}/properties", name="host_domain_properties")
     */
    public function cgetAction($id, Request $request) {

        //check if the user can access to the edit property view
        if($this->getUser()->isHost()) {
            $domain = $this->getDomainByUser($this->getUser()->getId());
            if($domain->getId() != $id) {
                throw $this->createAccessDeniedException('You cannot access this page!');
            }
        }
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_properties_by_domain', array('domainId' => $id, 'language'=>$request->getLocale())));
        $properties = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Property>', 'json');
        return $this->render('host/property/index.html.twig', array(
            'properties' => $properties));
    }

    /**
     * Edit the property of the current user (host).
     * This part allows to edit, the property's information or the property's address or the property's pictures.
     * @param $id, Request $request
     * @return
     * @Route("/property/{id}/edit/{nav}", name="property_edit")
     */
    public function putAction($id, $nav = '#presentation', Request $request) {
        $return = array();
        $property = $this->getProperty($id);
        //persit the domain object
        $this->getDoctrine()->getEntityManager()->persist($property->getDomain());
        $propertyForm =  $this->createForm(PropertyType::class, $property, array('language'=>$request->getLocale()));
        $propertyForm->handleRequest($request);
        if ($propertyForm->isSubmitted()) {
            $nav = 'presentation';
            if($propertyForm->isValid()) {
                $propertyEdit = $request->request->all()['property'];
                $propertyEdit["id"] = $property->getId();
                $this->submit($propertyEdit);
                $request->getSession()
                    ->getFlashBag()
                    ->add('presentationSuccess', $this->get('translator')->trans('success.generic_edit_form'));
                return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId(), 'nav' => $nav)));
            } else {
                $request->getSession()
                    ->getFlashBag()
                    ->add('presentationError', $this->get('translator')->trans('error.generic_form_error'));
            }
        }
        $return['propertyForm'] = $propertyForm->createView();
        $address = $this->getAddress($property);
        if (empty($address->getId())) {
            $addressListChoice = $this->createAddressListChoice($property);
            $return['addressListChoice'] = $addressListChoice->createView();
        }
        $addressForm = $this->createForm(AddressType::class, $address);
        $addressForm->handleRequest($request);
        if ($addressForm->isSubmitted()) {
            $nav = 'address';
            if ($addressForm->isValid()) {
                $addressForm = $request->request->all()['address'];
                $addressForm["property"] = $property->getId();
                $addressForm["id"] = $address->getId();
                $this->submitAddress($addressForm);
                $request->getSession()
                    ->getFlashBag()
                    ->add('addressSuccess', $this->get('translator')->trans('success.generic_edit_form'));
                return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId(), 'nav' => $nav)));
            } else {
                $request->getSession()
                    ->getFlashBag()
                    ->add('addressError', $this->get('translator')->trans('error.generic_form_error'));
            }
        }
        if ($request->isMethod('POST')) {
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($characteristicValueForm)) {
                $nav = 'informations';
                $characteristicValueForm["property"] = $property->getId();
                $this->submitCharacteristicPropertyValues($characteristicValueForm);
                $request->getSession()
                    ->getFlashBag()
                    ->add('informationsSuccess', $this->get('translator')->trans('success.generic_edit_form'));
                return $this->redirect($this->generateUrl('property_edit', array('id' => $property->getId(), 'nav' => $nav)));
            }
        }
        $serializer = $this->container->get('jms_serializer');
        $return['addressForm'] = $addressForm->createView();
        $return['characteristicCategories'] = $this->getCharacteristicCategory($property, $request->getLocale());
        $return['medias'] = $serializer->serialize($property->getMedias(), 'json');
        $return['rentals'] = $this->getRentals($property->getId(), $request->getLocale());
        $return['nav'] = $nav;
        return $this->render('host/property/edit.html.twig', $return);
    }
    public function getRentals($propertyId, $language) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental_by_property', array('property'=>$propertyId, 'language' => $language)));
        $rentals = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Rental>', 'json');
        return $rentals;
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
        $characteristicCategories = $this->getCharacteristicCategory($property, $request->getLocale());
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
                return $this->redirectToRoute('property_picture', array('idProperty' => $idProperty));
            }
        }

        $return['addressForm'] = $addressForm->createView();
        return $this->render('host/property/new/address.html.twig', $return);
    }
    /**
     * Create and handle the submition of a the property's picture of presentation (entity Media).
     * This correspond to the "step-3" of the creation of a new property.
     * @param $idProperty, Request $request
     * @return
     * @Route("/property/{idProperty}/picture", name="property_picture")
     */
    public function putPropertyPictures($idProperty, Request $request) {
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            if ($request->files->get('media')['media']) {
                $media["property"] = $idProperty;
                $media["presentation"] = 1;
                $this->submitPicture($media);
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
        $response = $api->get($this->get('_router')->generate('api_get_address_by_property', array('propertyId' => $propertyId)));
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
     * Submit picture : call the post api route to save the and upload the picture, and then the route allowing to create the link between the picture and the property
     * @param array
     */
    public function submitPicture($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["property"] = $media["property"];
        $media["upload_directory"] = $this->getParameter('property_directory_upload');
        $response = $api->file($this->get('router')->generate('api_post_media'),
            $media,
            $media['media']);
        $jsonResponse = $response->getBody()->getContents();
        $media = $serializer->deserialize($jsonResponse, 'Winefing\ApiBundle\Entity\Media', "json");
        $body["media"] = $media->getId();
        $api->put($this->get('router')->generate('api_put_media_property'), $body);
        return $jsonResponse;
    }
    /**
     * @Route("/property/delete/picture/{id}", name="property_delete_picture")
     */
    public function domainDeletePicture($id) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_media', array('id'=>$id, "directoryUpload"=>"property_directory_upload")));
        return new Response();
    }
    /**
     * @Route("/property/{id}/upload/picture", name="property_upload_picture")
     */
    public function propertyUploadPicture($id, Request $request) {
        $media = array();
        $logger = $this->get('logger');
        $logger->info($request->files->get('file'));
        ($request->files->get('file'));
        $media['media'] = $request->files->get('file');
        $media["property"] = $id;
        return new Response($this->submitPicture($media));
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
            $api->patch($this->get('router')->generate('api_patch_property_address'), $body);
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
        $characteristicService = $this->container->get('winefing.characteristic_service');
        $characteristicService->submitCharacteristicValues($characteristicValueForm, ScopeEnum::Property);
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
     * Get all the characteristic (current + missing)
     * @param $domain
     * @param $language
     * @return mixed
     */
    public function getCharacteristicCategory($property, $language) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_characteristic_values_all_by_scope', array('id'=>$property->getId(), 'scope'=>ScopeEnum::Property, 'language'=>$language)));

        $characteristicValues = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\CharacteristicValue>', "json");
        $characteristicService = $this->container->get('winefing.characteristic_service');
        return $characteristicService->getByCharacteristicCategory($characteristicValues);
    }

    public function getDomainByUser($userId) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $domain;
    }
}