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
     * @Route("/rental/new/{step}/{id}", name="rental_new")
     */
    public function newAction($step, $id ='', Request $request) {
        $return = array();
        if(!empty($id)) {
            $rental = $this->getRental($id);
            $this->getDoctrine()->getEntityManager()->merge($rental);
            $this->getDoctrine()->getEntityManager()->persist($rental->getProperty());
        } else {
            $rental = new Rental();
        }
        switch($step) {
            case "step-1" :
                $rentalForm = $this->createForm(RentalType::class, $rental);
                $return['rentalForm'] = $rentalForm->createView();
                $rentalForm->handleRequest($request);
                if ($rentalForm->isSubmitted()) {
                    if($rentalForm->isValid()) {
                        $rentalForm = $request->request->all()['rental'];
                        $rentalForm["id"] = $rental->getId();
                        $rental = $this->submit($rentalForm);
                        return $this->redirect($this->generateUrl('property_new', array('step' => 'step-2', 'id'=> $rental->getId())) . '#step-2');
                    }
                }
                break;
            case "step-2" :
                $this->setMissingCharacteristicsAction($rental);
                $characteristicCategories = $this->getCharacteristicCategories($rental);
                $return['characteristicCategories'] = $characteristicCategories->createView();
                if ($request->isMethod('POST')) {
                    $characteristicValueForm = $request->request->get("characteristicValueForm");
                    if (!empty($characteristicValueForm)) {
                        $characteristicValueForm["rental"] = $rental->getId();
                        $this->submitCharacteristicValues($characteristicValueForm);
                        return $this->redirect($this->generateUrl('property_new', array('step' => 'step-3', 'id' => $rental->getId())) . '#step-3');
                    }
                }
                break;
            case 'step-3':
                if ($request->isMethod('POST')) {
                    $media = $request->files->get('media');
                    if (!empty($media)) {
                        $media["property"] = $rental->getId();
                        $this->submitPictures($media);
                        return $this->redirect($this->generateUrl('rental_edit', array('id' => $rental->getId())) . '#presentation');
                    }
                }
                break;
        }
        return $this->render('host/rental/new.html.twig', $return);
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
        if(empty($rental["property"])) {
            return $this->redirect($this->generateUrl('property_new'));
        }
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