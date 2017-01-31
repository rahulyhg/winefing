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
use Winefing\ApiBundle\Entity\ScopeEnum;
use Symfony\Component\Form\FormError;


class DomainController extends Controller
{
//    protected $domain;
//
//    public function __construct() {
//        $this->domain = $this->getDomain();
//    }

    /**
     * @Route("/domain/{id}/wishlist", name="domain_add_to_wishlist")
     */
    public function addToWishlistAction($id) {
        if(!empty($this->getUser())) {
            $body['user'] = $this->getUser()->getId();
            $body['domain'] = $id;
            $api = $this->container->get('winefing.api_controller');
            $api->patch($this->get('router')->generate('api_patch_user_domain'), $body);
        } else {
            $error = '%error.not_connecteed%';
            throw new \Exception($error);
        }
        return new Response();
    }
    /**
     * @Route("/explore", name="explore")
     */
    public function winefingSelection(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response =  $api->get($this->get('router')->generate('api_get_domains_explore', array('language'=>$request->getLocale())));
        $domains = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Domain>', 'json');
        return $this->render('user/explore.html.twig', array('domains'=>$domains));
    }
    /**
     * @Route("/domain/{id}", name="domain")
     */
    public function getDomainAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response =  $api->get($this->get('router')->generate('api_get_domain_all_informations', array('id'=>$id, 'language'=>$request->getLocale())));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $this->render('user/domain/card.html.twig', array('domain'=>$domain));
    }

    /**
     * @Route("/domains", name="domains")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response =  $api->get($this->get('router')->generate('api_get_domains'));
        $domains = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Domain>', 'json');
        return $this->render('user/domain/index.html.twig', array(
                'domains' => $domains
        ));
    }
    /**
     * @Route("/host/domain/edit/{nav}", name="domain_edit")
     */
    public function getAction($nav = 'presentation', Request $request) {
        $domain = $this->getDomain();
        $this->getDoctrine()->getEntityManager()->persist($domain->getWineRegion());
        $domainForm = $this->createForm(DomainType::class, $domain, array('language'=>$request->getLocale()));
        $domainForm->handleRequest($request);
        if($domainForm->isSubmitted() && $domainForm->isValid()) {
                $domainForm = $request->request->get('domain');
                $domainForm["id"] = $domain->getId();
                $this->submit($domainForm);
        }
        $addressForm = $this->createForm(AddressType::class, $domain->getAddress());
        $addressForm->handleRequest($request);
        if($addressForm->isSubmitted()) {
            $nav = 'address';
            if($addressForm->isValid()) {
                $geocoder = $this->container->get('winefing.geocoder_controller');
                $coordinate = $geocoder->geocode($request->request->get('address')['formattedAddress']);
                if(!$coordinate){
                    $addressForm->get('formattedAddress')->addError(new FormError($this->get('translator')->trans('error.address_not_correct')));
                } else {
                    $addressForm = $request->request->get('address');
                    $addressForm["id"] = $domain->getAddress()->getId();
                    $addressForm["domain"] = $domain->getId();
                    $addressForm["lat"] = $coordinate[0];
                    $addressForm["lng"] = $coordinate[1];
                    $this->submitAddress($addressForm);
                    return $this->redirect($this->generateUrl('domain_edit', array('nav' => 'address')));
                }
            }
        }
        $characteristicCategories = $this->getCharacteristicCategory($domain, $request->getLocale());
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($media)) {
                $media["domain"] = $domain->getId();
                $this->submitPictures($media);
                return $this->redirect($this->generateUrl('domain_edit', array('nav' => 'pictures')));
            } elseif (!empty($characteristicValueForm)) {
                $characteristicValueForm["domain"] = $domain->getId();
                $this->submitCharacteristicValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('domain_edit', array('nav' => 'informations')));
            }
        }
        $serializer = $this->container->get('jms_serializer');
        return $this->render('host/domain/edit.html.twig', array(
            'domainForm' => $domainForm->createView(),
            'addressForm'=>$addressForm->createView(),
            'characteristicCategories' => $characteristicCategories,
            'medias' => $serializer->serialize($domain->getMedias(), 'json'), 'nav'=>$nav)
        );
    }
    /**
     * @Route("/domain/upload/picture", name="domain_upload_picture")
     */
    public function domainUploadPicture(Request $request) {
        $media = array();
        $logger = $this->get('logger');
        $logger->info($request->files->get('file'));
        ($request->files->get('file'));
        $media['media'] = $request->files->get('file');
        $media["domain"] = $this->getDomain()->getId();
        return new Response($this->submitPictures($media));
    }
    /**
     * @Route("/domain/delete/picture/{id}", name="domain_delete_picture")
     */
    public function domainDeletePicture($id) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_media', array('id'=>$id, "directoryUpload"=>"domain_directory_upload")));
        return $this->redirect($this->generateUrl('domain_edit', array('nav' => 'pictures')));
    }
    public function getDomain() {
        $userId = $this->getUser()->getId();
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $domain;
    }
    public function submit($domain)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_domain'), $domain);
    }
    public function submitPictures($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["domain"] = $media["domain"];
        $uploadDirectory["upload_directory"] = $this->getParameter('domain_directory_upload');
        $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media['media']);
        $jsonResponse = $response->getBody()->getContents();
        $media = $serializer->deserialize($jsonResponse, 'Winefing\ApiBundle\Entity\Media', "json");
        $body["media"] = $media->getId();
        $api->put($this->get('router')->generate('api_put_media_domain'), $body);
        return $jsonResponse;
    }

    public function submitAddress($address) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $domain = $address['domain'];
        if(empty($address["id"])) {
            $response =  $api->post($this->get('router')->generate('api_post_address'), $address);
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
            $body["address"] = $address->getId();
            $body["domain"] = $domain;
            $api->patch($this->get('router')->generate('api_patch_property_address'), $body);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_address'), $address);
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        }
        return $address;
    }

    public function submitCharacteristicDomainValues(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $domain = $request->request->all()["characteristicValue"]["domain"];
        foreach($request->request->all()["characteristicValue"]["characteristicValue"] as $characteristicValue) {
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
     * Get all the characteristic (current + missing)
     * @param $domain
     * @param $language
     * @return mixed
     */
    public function getCharacteristicCategory($domain, $language) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_characteristic_values_all_by_scope', array('id'=>$domain->getId(), 'scope'=>ScopeEnum::Domain, 'language'=>$language)));

        $characteristicValues = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\CharacteristicValue>', "json");
        $characteristicService = $this->container->get('winefing.characteristic_service');
        return $characteristicService->getByCharacteristicCategory($characteristicValues);
    }

    /**
     * @param $characteristicValueForm
     */
    public function submitCharacteristicValues($characteristicValueForm) {
        $characteristicService = $this->container->get('winefing.characteristic_service');
        $characteristicService->submitCharacteristicValues($characteristicValueForm, ScopeEnum::Domain);
    }
}