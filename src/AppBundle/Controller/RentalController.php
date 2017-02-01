<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\AddressType;
use AppBundle\Form\AddressUserType;
use AppBundle\Form\RentalType;
use PaiementBundle\Entity\CreditCard;
use PaiementBundle\Form\CreditCardType;
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
use Winefing\ApiBundle\Entity\CharacteristicrentalValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Rental;
use Winefing\ApiBundle\Entity\CharacteristicValue;
use Winefing\ApiBundle\Entity\ScopeEnum;
use Winefing\ApiBundle\Entity\StatusCodeEnum;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;


class RentalController extends Controller
{
    const DATE_FORMAT = 'd-m-Y';
    /**
     * @Route("/rentals", name="rentals")
     *
     */
    public function cgetForUserAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rentals'));
        $rentals = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Rental>', 'json');
        return $this->render('user/rental/research.html.twig', array('rentals' => $rentals));
    }
    /**
     * @Route("users/rental/{id}", name="rental")
     *
     */
    public function getOneAction($id, Request $request) {
        $rental = $this->getRental($id);
        $rentalPromotions = $this->getRentalPromotions($id);
        $rentalPromotionsArray = $this->formateDate($rental, $rentalPromotions);
        if($request->isMethod('POST')) {
            $this->get('session')->set('rental', $id);
            $this->get('session')->set('startDate', $request->request->get('start'));
            $this->get('session')->set('endDate', $request->request->get('end'));
            return $this->redirectToRoute('rental_paiement_billing_address');
        }
        return $this->render('user/rental/singleCard.html.twig', array('rental' => $rental, 'rentalPromotions'=>$rentalPromotionsArray));
    }
    public function getRentalPromotions($rentalId) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental_promotions_by_rental', array('rentalId'=>$rentalId)));
        $rentalPromotions = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\RentalPromotion>', 'json');
        return $rentalPromotions;
    }
    public function formateDate($rental, $rentalPromotions) {
        $rentalPromotionsArray = array();
        foreach($rentalPromotions as $rentalPromotion) {
            $rentalPromotionArray = array();
            $rentalPromotionArray["price"] = $rental->getPrice() * ((100-$rentalPromotion->getReduction())/100);
            $rentalPromotionArray['startDate'] = strtotime(date_format($rentalPromotion->getStartDate(), $this::DATE_FORMAT));
            $rentalPromotionArray['endDate'] = strtotime(date_format($rentalPromotion->getEndDate(), $this::DATE_FORMAT));
            array_push($rentalPromotionsArray, $rentalPromotionArray);
        }
        return $rentalPromotionsArray;
    }
    /**
     * @Route("host/rentals", name="host_rentals")
     *
     */
    public function cgetAction() {
        $userId = $this->getUser()->getId();
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rentals_by_user', array('userId' => $userId)));
        $rentals = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Rental>', 'json');
        return $this->render('host/rental/index.html.twig', array('rentals' => $rentals));
    }

    /**
     * @Route("host/rental/edit/{id}", name="rental_edit")
     */
    public function putAction($id ='', Request $request) {
        $rental = $this->getRental($id);
        $this->getDoctrine()->getEntityManager()->persist($rental->getProperty());

        $characteristicCategories = $this->getCharacteristicCategory($rental, $request->getLocale());

        //create the form
        $rentalForm =  $this->createForm(RentalType::class, $rental, array('user'=>$this->getUser()->getId()));
        $rentalForm->get('property')->setData($rental->getProperty());
        $rentalForm->add('description', TextareaType::class, array('label'=> false,'attr'=> array('maxlength'=>"255", 'required'=> false)));


        $rentalForm->handleRequest($request);
        if ($rentalForm->isSubmitted()) {
            if($rentalForm->isValid()) {
                $rentalForm = $request->request->all()['rental'];
                $rentalForm["id"] = $rental->getId();
                $rentalForm["property"] = $rental->getProperty()->getId();
                $rental = $this->submit($rentalForm);
                return $this->redirect($this->generateUrl('rental_edit', array('id'=> $rental->getId())). '#presentation');
            }
        }
        if ($request->isMethod('POST')) {
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            $media = $request->files->get('media');
            if (!empty($media)) {
                $media["property"] = $rental->getId();
                $this->submitPictures($media);
                return $this->redirect($this->generateUrl('rental_edit', array('id' => $rental->getId())) . '#presentation');
            } elseif (!empty($characteristicValueForm)) {
                $characteristicValueForm["rental"] = $rental->getId();
                $this->submitCharacteristicValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('rental_edit', array('id' => $rental->getId())) . '#informations');
            }
        }

        return $this->render('host/rental/edit.html.twig', array(
            'rentalForm' => $rentalForm->createView(),
            'characteristicCategories' => $characteristicCategories,
            'medias' => $rental->getMedias()
        ));
    }

    /**
     * @Route("host/rental/new/{property}", name="rental_new")
     */
    public function newAction($property = '', Request $request) {
        $rental = new Rental();
        $rental->setPrice(1);
        $rental->setMinimumRentalPeriod(1);
        $rental->setPeopleNumber(2);

        $options['user'] = $this->getUser()->getId();
        $rentalForm = $this->createForm(RentalType::class, $rental, $options);

        if($property) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
            $property = $repository->findOneById($property);
            $rentalForm->get('property')->setData($property);
        }
        $return['rentalForm'] = $rentalForm->createView();
        $rentalForm->handleRequest($request);
        if ($rentalForm->isSubmitted()) {
            if($rentalForm->isValid()) {
                $rentalForm = $request->request->all()['rental'];
                if(empty($rentalForm["property"])) {
                    return $this->redirect($this->generateUrl('property_new'));
                }
                $rentalForm["id"] = $rental->getId();
                $rental = $this->submit($rentalForm);
                $rentalId = $rental->getId();
                return $this->redirect($this->generateUrl('rental_characteristics', array('idRental'=> $rentalId)));
            }
        }
        return $this->render('host/rental/new/rental.html.twig', $return);
    }
    /**
     * @Route("host/rental/{idRental}/characteristics", name="rental_characteristics")
     */
    public function putPropertyCharacteristics($idRental, Request $request){
        $rental = $this->getRental($idRental);
        $characteristicCategories = $this->getCharacteristicCategory($rental, $request->getLocale());
        $return['characteristicCategories'] = $characteristicCategories;
        if ($request->isMethod('POST')) {
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($characteristicValueForm)) {
                $characteristicValueForm["rental"] = $rental->getId();
                $this->submitCharacteristicValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('rental_pictures', array('idRental'=> $idRental)));
            }
        }
        return $this->render('host/rental/new/information.html.twig', $return);
    }
    /**
     * @Route("host/rental/{idRental}/pictures", name="rental_pictures")
     */
    public function putPropertyPictures($idRental, Request $request) {
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            var_dump($media['medias'][0]);
            if (count($media['medias']) > 0 || $media['medias'][0] != 'null') {
                $media["rental"] = $idRental;
                $this->submitPictures($media);
                return $this->redirect($this->generateUrl('rental_edit', array('id' => $idRental)));
            }
        }
        return $this->render('host/rental/new/picture.html.twig');
    }
    public function getRental($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental', array('id' => $id)));
        $rental = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');
        return $rental;
    }


    public function submit($rental)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        if(empty($rental["id"])) {
            $response = $api->post($this->get('router')->generate('api_post_rental'), $rental);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_rental'), $rental);
        }
        $rental = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');
        return $rental;
    }

    public function submitPictureAction(Request $request)
    {
        $route = $request->request->all()["media"]["redirectRoute"];
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["rental"] = $request->request->all()["media"]["rental"];
        foreach($request->files->all()["media"]["medias"] as $media) {
            $uploadDirectory["upload_directory"] = $this->getParameter('rental_directory_upload');
            $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media);
            $media = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Media', "json");
            $body["media"] = $media->getId();
            $api->put($this->get('router')->generate('api_put_media_rental'), $body);
        }
        return $this->redirect($route);
    }

    public function submitCharacteristicValues($characteristicValueForm) {
        $characteristicService = $this->container->get('winefing.characteristic_service');
        $characteristicService->submitCharacteristicValues($characteristicValueForm, ScopeEnum::Rental);
    }

    /**
     * @Route("/delete/rental/{id}", name="rental_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_rental', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The rental is well deleted.");
        return $this->redirectToRoute('rental_user');
    }
    public function submitPictures($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["rental"] = $media["rental"];
        foreach($media["medias"] as $media) {
            $uploadDirectory["upload_directory"] = $this->getParameter('rental_directory_upload');
            $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media);
            $media = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Media', "json");
            $body["media"] = $media->getId();
            $api->put($this->get('router')->generate('api_put_media_rental'), $body);
        }
    }
    public function getCharacteristicCategory($rental, $language) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_characteristic_values_all_by_scope', array('id'=>$rental->getId(), 'scope'=>ScopeEnum::Rental, 'language'=>$language)));
        $characteristicValues = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\CharacteristicValue>', "json");
        $characteristicService = $this->container->get('winefing.characteristic_service');
        return $characteristicService->getByCharacteristicCategory($characteristicValues);
    }
}