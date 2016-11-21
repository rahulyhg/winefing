<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\AddressType;
use AppBundle\Form\rentalType;
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


class RentalController extends Controller
{
    /**
     * @Route("/rental/{userId}", name="rental_user")
     */
    public function getAction($userId = 57) {
//        $api = $this->container->get('winefing.api_controller');
//        $serializer = $this->container->get('winefing.serializer_controller');
//        $response = $response = $api->get($this->get('_router')->generate('api_get_rental_by_user', array('userId' => $userId)));
//        $rental = $serializer->decode($response->getBody()->getContents());
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
//        $rental = $repository->findOneById($rental["id"]);
//        $rental = $this->getDoctrine()->getEntityManager()->merge($rental);
//        $this->setMissingCharacteristicsAction($rental);
//        $characteristicCategories = $this->getCharacteristicCategory($rental);
//        $rentalForm = $this->createForm(rentalType::class, $rental);
//        $addressForm = $this->createForm(AddressType::class, $rental->getAddress(), array('action' => $this->generateUrl('rental_address_submit')));
//        $response = $api->get($this->get('_router')->generate('api_get_rentals_picture_path'));
//        $picturePath = $serializer->decode($response->getBody()->getContents());
//        return $this->render('host/rental/index.html.twig', array(
//            'rentalForm' => $rentalForm->createView(),
//            'addressForm'=>$addressForm->createView(),
//            'rental' => $rental,
//            'picturePath' => $picturePath,
//            'characteristicCategories' => $characteristicCategories)
//        );
                return $this->render('host/rental/new.html.twig');
    }

    /**
     * @Route("/submit/rental", name="rental_submit")
     */
    public function submitAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response =  $api->put($this->get('router')->generate('api_put_rental'), $request->request->all()['rental']);
        $serializer->decode($response->getBody()->getContents());
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The rental is well modified.");
        return $this->redirectToRoute('rental_user');
    }
    /**
     * @Route("/submit/pictures/rental", name="rental_pictures_submit")
     */
    public function submitPictureAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $rental["rental"] = $request->request->all()["rental"];
        var_dump($request->files->all());
        foreach($request->files->all()["medias"] as $media) {
            var_dump("o");
            $api->file($this->get('router')->generate('api_post_rental_picture'), $rental, $media);
        }
        return $this->redirect($this->generateUrl('rental_user'). '#pictures');
    }
    /**
     * @Route("/submit/address/rental", name="rental_address_submit")
     */
    public function submitAddressAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $rental["rental"] = $request->request->all()["rental"];
        var_dump($request->files->all());
        foreach($request->files->all()["medias"] as $media) {
            var_dump("o");
            $api->file($this->get('router')->generate('api_post_rental_picture'), $rental, $media);
        }
        return $this->redirect($this->generateUrl('rental_user'). '#pictures');
    }
    /**
     * @Route("/submit/characteristicrentalValues", name="characteristic_rental_value_submit")
     */
    public function submitCharacteristicrentalValues(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $rental = $request->request->all()["characteristicrental"]["rental"];
        foreach($request->request->all()["characteristicrental"]["characteristicrentalValue"] as $characteristicrentalValue) {
            $characteristicrentalValue["rental"] = $rental;
            if (empty($characteristicrentalValue["id"])) {
                $api->post($this->get('router')->generate('api_post_characteristicrental_value'), $characteristicrentalValue);
            } else {
                $api->put($this->get('router')->generate('api_put_characteristicrental_value'), $characteristicrentalValue);
            }
        }
        return $this->redirect($this->generateUrl('rental_user'). '#informations');
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
     * @Route("/address/rental/{userId}", name="rental_address")
     */
    public function getAddressAction($userId = 57) {
        return $this->render('host/rental/test.html.twig');
    }
    /**
     * @Route("/pictures/rental/{userId}", name="rental_pictures")
     */
    public function getPictureAction($userId = 57) {
        $api = $this->container->get('winefing.api_controller');
//        $response = $api->get($this->get('_router')->generate('api_get_rental_by_user', array('userId' => $userId)));
//        $serializer = $this->container->get('jms_serializer');
//        $rental = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\rental', 'json');
//        $address = $rental->getAddress();
//        $rentalForm = $this->createForm(AddressType::class, $address, array(
//            'action' => $this->generateUrl('rental_submit')));
        return $this->render('host/rental/picture.html.twig');
    }

    public function setMissingCharacteristicsAction($rental)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_rental_missing_characteristics', array('rentalId' => $rental->getId())));
        $missingCharacteristics = $serializer->decode($response->getBody()->getContents());

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicrentalValue = new CharacteristicrentalValue();
            $characteristic = $repository->findOneById($characteristic["id"]);
            $characteristicrentalValue->setCharacteristic($characteristic);
            $characteristicrentalValue->setrental($rental);
            $rental->addCharacteristicrentalValue($characteristicrentalValue);
        }
    }

    public function getCharacteristicCategory($rental) {
        $list = array();
        foreach($rental->getCharacteristicrentalValues() as $characteristicrentalValue) {
            $list[$characteristicrentalValue
                    ->getCharacteristic()
                    ->getCharacteristicCategory()
                    ->getCharacteristicCategoryTrs()[0]
                    ->getName()][]
                = $characteristicrentalValue;
        }
        return $list;
    }
}