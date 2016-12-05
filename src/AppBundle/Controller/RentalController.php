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
use Winefing\ApiBundle\Entity\CharacteristicValue;


class RentalController extends Controller
{
    /**
     * @Route("/rentals", name="rentals")
     *
     */
    public function cgetAction() {
        $userId = 57;
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
    public function putAction($id ='') {

        $rental = $this->getRental($id);
        $this->getDoctrine()->getEntityManager()->persist($rental->getProperty());

        $this->setMissingCharacteristicsAction($rental);
        $characteristicCategories = $this->getCharacteristicCategories($rental);

        $rentalForm =  $this->createForm(RentalType::class, $rental);
        $mediaPath = $this->getMediaPath();

        return $this->render('host/rental/edit.html.twig', array(
            'rentalForm' => $rentalForm->createView(),
            'characteristicCategories' => $characteristicCategories,
            'medias' => $rental->getMedias(),
            'mediaPath' => $mediaPath
        ));
        return new Response();
    }
    public function getMediaPath() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_rentals_media_path'));
        $mediaPath = $serializer->decode($response->getBody()->getContents());
        return $mediaPath;
    }

    /**
     * @Route("/rental/new/{id}", name="rental_new")
     */
    public function newAction($id ='') {
        if(!empty($id)) {
            $rental = $this->getRental($id);
            $this->getDoctrine()->getEntityManager()->merge($rental);
            $this->getDoctrine()->getEntityManager()->persist($rental->getProperty());
        } else {
            $rental = new Rental();
        }
        $rentalForm = $this->createForm(RentalType::class, $rental);
        $this->setMissingCharacteristicsAction($rental);
        $characteristicCategories = $this->getCharacteristicCategories($rental);
        return $this->render('host/rental/new.html.twig', array(
            'rentalForm' => $rentalForm->createView(),
            'characteristicCategories' => $characteristicCategories));
    }
    public function getRental($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_rental', array('id' => $id)));
        $rental = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');
        return $rental;
    }

    /**
     * @Route("/submit/rental", name="rental_submit")
     */
    public function submitAction(Request $request)
    {
        if(empty($request->request->all()["rental"]["property"])) {
            return $this->redirect($this->generateUrl('property_new'));
        }
        $route = $request->request->all()["rental"]["redirectRoute"];
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        if(empty($request->request->all()["rental"]["id"])) {
            $response = $api->post($this->get('router')->generate('api_post_rental'), $request->request->all()["rental"]);
            $rental = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Rental', 'json');
            return $this->redirect($this->generateUrl('rental_new', array('id'=> $rental->getId())). '#step-2');
        } else {
            $api->put($this->get('router')->generate('api_put_rental'), $request->request->all()["rental"]);
        }
        return $this->redirect($route);
    }
    /**
     * @Route("/submit/pictures/rental", name="rental_pictures_submit")
     */
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
    /**
     * @Route("/submit/characteristic/rental/values", name="characteristic_rental_value_submit")
     */
    public function submitCharacteristicRentalValues(Request $request) {
        $route = $request->request->all()["characteristicValueForm"]["redirectRoute"];
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $rental = $request->request->all()["characteristicValueForm"]["rental"];
        foreach($request->request->all()["characteristicValueForm"]["characteristicValue"] as $characteristicValue) {
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
        return $this->redirect($route);
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