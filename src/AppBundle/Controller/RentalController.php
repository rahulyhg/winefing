<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
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
use Winefing\ApiBundle\Entity\CharacteristicrentalValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Rental;
use Winefing\ApiBundle\Entity\CharacteristicValue;


class RentalController extends Controller
{
    const DATE_FORMAT = 'd-m-Y';
    /**
     * @Route("users/rentals", name="rentals_users")
     *
     */
    public function cgetForUserAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rentals'));
        $rentals = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Rental>', 'json');
        $mediaPath = $this->getMediaPath();
        return $this->render('user/rental/research.html.twig', array('rentals' => $rentals, 'mediaPath'=>$mediaPath));
    }

    /**
     * @param $order
     * @return mixed
     * @Route("users/rental/paiement", name="rental_paiement")
     */
    public function paiement(Request $request){
        $rental = $this->getRental($request->request->get('rental'));
        var_dump($request->request->all());
        $prices = $this->getPricesRentalAndPeriod($rental->getId(), $request->request->get('start'), $request->request->get('end'));
        $creditCard = new CreditCard();
        var_dump($prices);
        $creditCardForm = $this->createForm(CreditCardType::class, $creditCard);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        $date = strtotime($request->request->get('start'));
        while($date < strtotime($request->request->get('end'))){
            $rentalPromotion = $repository->findPromotionByDate($date, $rental->getId());
            var_dump($rentalPromotion);
            if(empty($rentalPromotion) || $rentalPromotion == NULL) {
                $price = $rental->getPrice();
            } else {
                $price =  $rental->getPrice() * ((100-$rentalPromotion->getReduction())/100);
            }
            $allDates[$date] = $price;
            $date = strtotime('+1 days', $date);
        }

        return $this->render('user/rental/paiement.html.twig', ['creditCardForm'=>$creditCardForm->createView()]);
    }

    /**
     * For each of the period of location, this function return an array with the date and the price associated.
     * @param $rental
     * @param $start
     * @param $end
     * @return array[date] = $price
     */
    public function getPricesRentalAndPeriod($rental, $start, $end) {
        $serializer = $this->container->get('winefing.serializer_controller');
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_prices_by_date', array('rental'=>$rental, 'start'=>$start, 'end'=>$end)));
        $prices = $serializer->decode($response->getBody()->getContents(),'json');
        return $prices;

    }
    /**
     * @Route("users/rental/{id}", name="rental")
     *
     */
    public function getOneAction($id, Request $request) {
        $rental = $this->getRental($id);
        $serializer = $this->container->get('jms_serializer');
        $rentalPromotions = $this->getRentalPromotions($id);
        $rentalPromotionsArray = $this->formateDate($rental, $rentalPromotions);
        var_dump($rentalPromotionsArray);
        if($request->isMethod('POST')) {
            $order['rental'] = $id;
            $order['startDate'] = $request->request->get('start');
            $order['endDate'] = $request->request->get('end');
            return $this->redirectToRoute('rental_paiement', array('request'=> $order), 307);
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
     * @Route("/rentals", name="rentals")
     *
     */
    public function cgetAction() {
        $userId = $this->getUser()->getId();
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rentals_by_user', array('userId' => $userId)));
        $rentals = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Rental>', 'json');
        $mediaPath = $this->getMediaPath();
        return $this->render('host/rental/index.html.twig', array('rentals' => $rentals, 'mediaPath'=>$mediaPath));
    }

    /**
     * @Route("/rental/edit/{id}", name="rental_edit")
     */
    public function putAction($id ='', Request $request) {
        $rental = $this->getRental($id);
        $this->getDoctrine()->getEntityManager()->persist($rental->getProperty());

        $this->setMissingCharacteristicsAction($rental);
        $characteristicCategories = $this->getCharacteristicCategories($rental);
        $mediaPath = $this->getMediaPath();

        $rentalForm =  $this->createForm(RentalType::class, $rental);
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
            'medias' => $rental->getMedias(),
            'mediaPath' => $mediaPath
        ));
    }
    public function getMediaPath() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rental_media_path'));
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $mediaPath;
    }

    /**
     * @Route("/rental/new", name="rental_new")
     */
    public function newAction(Request $request) {
        $rental = new Rental();
        $rental->setPrice(1.1);
        $options['user'] = $this->getUser()->getId();
        $rentalForm = $this->createForm(RentalType::class, $rental, $options);
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
     * @Route("/rental/{idRental}/characteristics", name="rental_characteristics")
     */
    public function putPropertyCharacteristics($idRental, Request $request){
        $rental = $this->getRental($idRental);
        $this->setMissingCharacteristicsAction($rental);
        $characteristicCategories = $this->getCharacteristicCategories($rental);
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
     * @Route("/rental/{idRental}/pictures", name="rental_pictures")
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
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $rental = $characteristicValueForm["rental"];
        foreach($characteristicValueForm["characteristicValue"] as $characteristicValue) {
            if (empty($characteristicValue["id"])) {
                $response = $api->post($this->get('router')->generate('api_post_characteristic_value'), $characteristicValue);
                $characteristicValue = $serializer->decode($response->getBody()->getContents());
                $characteristicValueProperty["rental"] = $rental;
                $characteristicValueProperty["characteristicValue"] = $characteristicValue["id"];
                $api->put($this->get('router')->generate('api_put_characteristic_value_rental'), $characteristicValueProperty);
            } else {
                $api->put($this->get('router')->generate('api_put_characteristic_value'), $characteristicValue);
            }
        }
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

    public function setMissingCharacteristicsAction($rental)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_rental_missing_characteristics', array('rentalId' => $rental->getId())));
        $missingCharacteristics = $serializer->decode($response->getBody()->getContents());

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicValue = new CharacteristicValue();
            $characteristic = $repository->findOneById($characteristic["id"]);
            $characteristicValue->setCharacteristic($characteristic);
            $rental->addCharacteristicValue($characteristicValue);
        }
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
    public function getCharacteristicCategories($rental) {
        $list = array();
        foreach($rental->getCharacteristicValues() as $characteristicValue) {
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