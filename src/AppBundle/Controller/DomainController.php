<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\AddressType;
use AppBundle\Form\DomainFilterType;
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
use Winefing\ApiBundle\Entity\ScopeEnum;
use Symfony\Component\Form\FormError;
use Winefing\ApiBundle\Entity\StatusCodeEnum;


class DomainController extends Controller
{
//    protected $domain;
//
//    public function __construct() {
//        $this->domain = $this->getDomain();
//    }
    /**
     * get domains depending of criterias (people number, wineRegion, tags, price, when).
     * @Route("/filter/domains", name="domains_by_criteria")
     */
    public function cgetDomainByCriteria(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');

        $domainFilterParams = $request->query->get('domain_filter');
        if(!empty($domainFilterParams)) {
            //get the domains depending the params
            $response =  $api->get($this->get('router')->generate('api_get_domains_by_criteria'), $domainFilterParams);
            $domains = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Domain>', 'json');
        } else {
            //get all the domain
            $response =  $api->get($this->get('router')->generate('api_get_domains'));
            $domains = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Domain>', 'json');
        }

        $filterForm = $this->createForm(DomainFilterType::class, null, array('language'=>$request->getLocale()));

//        //set the wine region if not empty
        if(!empty($domainFilterParams['wineRegion'])) {
            $this->setWineRegionForm($domainFilterParams['wineRegion'], $filterForm);
        }

        if($filterForm->isSubmitted() && $filterForm->isValid()) {

        }
        //get the minimum price
        $response =  $api->get($this->get('router')->generate('api_get_rental_by_price', ['order'=>'ASC']));
        $rentalMinPrice = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');

        //get the maximum price
        $response =  $api->get($this->get('router')->generate('api_get_rental_by_price', ['order'=>'DESC']));
        $rentalMaxPrice = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');

        //price range slider
        if(!empty($domainFilterParams['price'])) {
            $price = implode(",", $request->query->get('domain_filter')['price']);
            $dataSliderMinValue = $price[0];
            $dataSliderMaxValue = $price[1];
        } else {
            $dataSliderMinValue = $rentalMinPrice->getPrice();
            $dataSliderMaxValue = $rentalMaxPrice->getPrice();
        }
        //add the field to the form
        $filterForm->add('price', null, ['label'=>false, 'attr'=>['class'=>'slider',
            'data-slider-min'=>$rentalMinPrice->getPrice(),
            'data-slider-max'=>$rentalMaxPrice->getPrice(), "data-provide"=>"slider", "data-slider-value"=>"[".$dataSliderMinValue.",".$dataSliderMaxValue."]"]]);

        //set the people number
        if(!empty($domainFilterParams['peopleNumber'])) {
            $filterForm->get('peopleNumber')->setData($domainFilterParams['peopleNumber']);
        }

        //set the date
        if(!empty($domainFilterParams['startDate'])) {
            $filterForm->get('startDate')->setData(new \DateTime($domainFilterParams['startDate']));
            $filterForm->get('endDate')->setData(new \DateTime($domainFilterParams['endDate']));
        }


        $filterForm->handleRequest($request);

        return $this->render('user/filter.html.twig', ['domains'=>$domains, 'filterForm'=>$filterForm->createView()]);
    }

    /**
     * Set the wineRegion field of the forms
     * @param $wineRegions
     * @param $filterForm
     */
    public function setWineRegionForm($wineRegions, &$filterForm) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $wineRegionsArray = new ArrayCollection();
        foreach($wineRegions as $wineRegion) {
            $wineRegionsArray[] = $repository->findOneById($wineRegion);
        }
        $filterForm->get('wineRegion')->setData($wineRegions);
    }

    /**
     * @Route("/domain/{id}/wishlist/user/{userId}", name="domain_add_to_wishlist")
     */
    public function addToWishlistAction($id, $userId) {
        if(!empty($this->getUser())) {
            $body['user'] = $userId;
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
        $wineList = 0;
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response =  $api->get($this->get('router')->generate('api_get_domain_all_informations', array('id'=>$id, 'language'=>$request->getLocale())));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');

        $characteristicService = $this->container->get('winefing.characteristic_service');
        $domain->setCharacteristicValuesByCategory($characteristicService->getByCharacteristicCategory($domain->getCharacteristicValues()));
        $properties = new ArrayCollection();
        foreach($domain->getProperties() as $property) {
            $property->setCharacteristicValuesByCategory($characteristicService->getByCharacteristicCategory($property->getCharacteristicValues()));
            $rentals = new ArrayCollection();
            foreach($property->getRentals() as $rental) {
                $rental->setCharacteristicValuesByCategory($characteristicService->getByCharacteristicCategory($rental->getCharacteristicValues()));
                $rentals[]= $rental;
            }
            $property->setRentals($rentals);
            $properties[] = $property;
        }
        $domain->setProperties($properties);
        if ($this->getUser()) {
            $response =  $api->get($this->get('router')->generate('api_get_domain_wine_list', array('userId'=>$this->getUser()->getId(), 'domainId'=>$id)));
            if($response->getStatusCode() != StatusCodeEnum::empty_response) {
                $wineList = 1;
            }
        }
        return $this->render('user/domain/card.html.twig', array('domain'=>$domain, 'wineList'=>$wineList));
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
     * @Route("/host/domain/{id}/edit", name="domain_edit")
     */
    public function getAction($id, $nav = 'presentation', Request $request) {

        //check if the user can access to the edit property view
        if($this->getUser()->isHost()) {
            $domain = $this->getDomainByUser($this->getUser()->getId());
            if($domain->getId() != $id) {
                throw $this->createAccessDeniedException('You cannot access this page!');
            }
        } else {
            $this->container->setParameter('domain_id', $id);
        }
        //get the domain
        $domain = $this->getDomain($id);
        $this->getDoctrine()->getEntityManager()->persist($domain->getWineRegion());

        //create the form
        $domainForm = $this->createForm(DomainType::class, $domain, array('language'=>$request->getLocale()));
        if($this->getUser()->isHost()) {
            $domainForm->remove('tags');
        }
        $domainForm->handleRequest($request);

        //handle the domainForm submition
        if($domainForm->isSubmitted() && $domainForm->isValid()) {
                $body = $request->request->get('domain');
                $body["id"] = $domain->getId();
                $this->submit($body);
        }
        //create the address form
        $addressForm = $this->createForm(AddressType::class, $domain->getAddress());
        $addressForm->handleRequest($request);

        //handle the address submition
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
                    return $this->redirect($this->generateUrl('domain_edit', array('id'=>$domain->getId(), 'nav' => 'address')));
                }
            }
        }

        //create the characteristicCategories array
        $characteristicCategories = $this->getCharacteristicCategory($domain, $request->getLocale());

        //handle the characteristicValue form submition
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($media)) {
                $media["domain"] = $domain->getId();
                $this->submitPictures($media);
                return $this->redirect($this->generateUrl('domain_edit', array('id'=>$domain->getId(),'nav' => 'pictures')));
            } elseif (!empty($characteristicValueForm)) {
                $characteristicValueForm["domain"] = $domain->getId();
                $this->submitCharacteristicValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('domain_edit', array('id'=>$domain->getId(), 'nav' => 'informations')));
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
     * @Route("/domain/{id}/upload/picture", name="domain_upload_picture")
     */
    public function domainUploadPicture($id, Request $request) {
        $media = array();
        $logger = $this->get('logger');
        $logger->info($request->files->get('file'));
        ($request->files->get('file'));
        $media['media'] = $request->files->get('file');
        $media["domain"] = $id;
        return new Response($this->submitPictures($media));
    }
    /**
     * @Route("/domain/delete/picture/{id}", name="domain_delete_picture")
     */
    public function domainDeletePicture($id) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_media', array('id'=>$id, "directoryUpload"=>"domain_directory_upload")));
        return new Response();
    }
    public function getDomainByUser($userId) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $domain;
    }
    public function getDomain($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain', array('id' => $id)));
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