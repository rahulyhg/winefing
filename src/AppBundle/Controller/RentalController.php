<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\RentalType;
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
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\ScopeEnum;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use JMS\Serializer\SerializationContext;


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
//        if($request->isMethod('POST')) {
//            $this->get('session')->set('rental', $id);
//            $this->get('session')->set('startDate', $request->request->get('start'));
//            $this->get('session')->set('endDate', $request->request->get('end'));
//            return $this->redirectToRoute('rental_paiement_billing_address');
//        }
        return $this->render('user/rental/singleCard.html.twig', array('rental' => $rental, 'rentalPromotions'=>$rentalPromotionsArray));
    }
    /**
     * @Route("users/rental/{id}/paiement", name="rental_paiement_date")
     *
     */
    public function rentalPaiement($id, Request $request) {
        //start to set in session a new rentalOrder
        $rentalOrder = new RentalOrder();
        $rentalOrder->setStartDate(new \DateTime($request->request->get('start')));
        $rentalOrder->setEndDate(new \DateTime($request->request->get('end')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $rental->setMediaPresentation();
        $rentalOrder->setRental($rental);
        $serializer = $this->container->get('jms_serializer');
        $this->get('session')->set('rentalOrder', $serializer->serialize($rentalOrder, 'json', SerializationContext::create()->setGroups(array('default', 'rental'))));
        return $this->redirectToRoute('rental_paiement_billing_address');
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
     * @Route("host/domain/{id}/rentals", name="host_rentals")
     *
     */
    public function cgetAction($id) {
        //check if the user can access to the edit property view
        if($this->getUser()->isHost()) {
            $domain = $this->getDomainByUser($this->getUser()->getId());
            if($domain->getId() != $id) {
                throw $this->createAccessDeniedException('You cannot access this page!');
            }
        } else {
            $this->container->setParameter('domain_id', $id);
        }
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rentals_by_domain', array('domainId' => $id)));
        $rentals = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Rental>', 'json');
        return $this->render('host/rental/index.html.twig', array('rentals' => $rentals));
    }

    /**
     * @Route("host/rental/{id}/edit/{nav}", name="rental_edit")
     */
    public function putAction($id ='', $nav = 'presentation', Request $request) {
        $rental = $this->getRental($id);
        $this->getDoctrine()->getEntityManager()->persist($rental->getProperty());

        $characteristicCategories = $this->getCharacteristicCategory($rental, $request->getLocale());

        //create the form
        $rentalForm =  $this->createForm(RentalType::class, $rental, array('user'=>$this->getUser()->getId()));
        $rentalForm->get('property')->setData($rental->getProperty());
        $rentalForm->get('price')->setData($rental->getPrice());

        $rentalForm->handleRequest($request);
        if ($rentalForm->isSubmitted()) {
            if($rentalForm->isValid()) {
                $nav = 'presentation';
                $rentalEdit = $request->request->all()['rental'];
                $rentalEdit["id"] = $rental->getId();
                $rentalEdit["property"] = $rental->getProperty()->getId();
                $rental = $this->submit($rentalEdit);
                $request->getSession()
                    ->getFlashBag()
                    ->add('rentalSuccess', $this->get('translator')->trans('success.generic_edit_form'));
                return $this->redirect($this->generateUrl('rental_edit', array('id' => $rental->getId(), 'nav' => $nav)));
            } else {
                $request->getSession()
                    ->getFlashBag()
                    ->add('rentalError', $this->get('translator')->trans('error.generic_form_error'));
            }
        }
        if ($request->isMethod('POST')) {
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($characteristicValueForm)) {
                $characteristicValueForm["rental"] = $rental->getId();
                $this->submitCharacteristicValues($characteristicValueForm);
                $nav = 'informations';
                $request->getSession()
                    ->getFlashBag()
                    ->add('informationsSuccess', $this->get('translator')->trans('success.generic_edit_form'));
                return $this->redirect($this->generateUrl('rental_edit', array('id' => $rental->getId(), 'nav' => $nav)));
            }
        }
        $serializer = $this->container->get('jms_serializer');
        return $this->render('host/rental/edit.html.twig', array(
            'rentalForm' => $rentalForm->createView(),
            'characteristicCategories' => $characteristicCategories,
            'medias' => $serializer->serialize($rental->getMedias(), 'json'),
            'nav' => $nav
        ));
    }

    /**
     * @Route("host/rental/new/{property}", name="rental_new")
     */
    public function newAction($property = '', Request $request) {
        $rental = new Rental();
        $rental->setPrice(50.00);
        $rental->setMinimumRentalPeriod(1);
        $rental->setPeopleNumber(2);

        $options['user'] = $this->getUser()->getId();
        $rentalForm = $this->createForm(RentalType::class, $rental, $options);
        $rentalForm->remove('description');

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
                return $this->redirect($this->generateUrl('rental_picture', array('idRental'=> $idRental)));
            }
        }
        return $this->render('host/rental/new/information.html.twig', $return);
    }
    /**
     * @Route("host/rental/{idRental}/picture", name="rental_picture")
     */
    public function putPropertyPictures($idRental, Request $request) {
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            if ($request->files->get('media')['media']) {
                $media["rental"] = $idRental;
                $media["presentation"] = 1;
                $this->submitPicture($media);
                return $this->redirect($this->generateUrl('rental_edit', array('id'=> $idRental)));
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

    /**
     * Submit a picture : call the post api route to save the and upload the picture, and then the route allowing to create the link between the picture and the rental
     * @param array
     */
    public function submitPicture($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["rental"] = $media["rental"];
        $media["upload_directory"] = $this->getParameter('rental_directory_upload');
        $response = $api->file($this->get('router')->generate('api_post_media'),
            $media,
            $media['media']);
        $jsonResponse = $response->getBody()->getContents();
        $media = $serializer->deserialize($jsonResponse, 'Winefing\ApiBundle\Entity\Media', "json");
        $body["media"] = $media->getId();
        $api->put($this->get('router')->generate('api_put_media_rental'), $body);
        return $jsonResponse;
    }
    public function getCharacteristicCategory($rental, $language) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('router')->generate('api_get_characteristic_values_all_by_scope', array('id'=>$rental->getId(), 'scope'=>ScopeEnum::Rental, 'language'=>$language)));
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
    /**
     * @Route("/rental/delete/picture/{id}", name="rental_delete_picture")
     */
    public function domainDeletePicture($id) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_media', array('id'=>$id, "directoryUpload"=>"rental_directory_upload")));
        return new Response();
    }
    /**
     * @Route("/rental/{id}/upload/picture", name="rental_upload_picture")
     */
    public function rentalUploadPicture($id, Request $request) {
        $media = array();
        ($request->files->get('file'));
        $media['media'] = $request->files->get('file');
        $media["rental"] = $id;
        return new Response($this->submitPicture($media));
    }
}