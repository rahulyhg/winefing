<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Rental;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\Entity\ScopeEnum;
use FOS\RestBundle\Controller\Annotations\Get;
use JMS\Serializer\SerializationContext;


class RentalController extends Controller implements ClassResourceInterface
{
    /**
     * GET Route annotation.
     * @Get("api/property/{id}")
     */
    public function getAction($id)
    {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($id);
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('default', 'medias', 'characteristicValues', 'property')));
        return new Response($json);
    }

    public function getMissingCharacteristicsAction($rentalId) {
        $serializer = $this->container->get('jms_serializer');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($rentalId);

        $ids = array();
        foreach($rental->getCharacteristicValues() as $characteristicValue) {
            $ids[] = $characteristicValue->getCharacteristic()->getId();
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristics = $repository->findMissingCharacteristics($ids, ScopeEnum::Rental);
        return new Response($serializer->serialize($characteristics, 'json', SerializationContext::create()->setGroups(array('default'))));

    }

    public function getMediaPathAction()
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $webPath = $this->container->get('winefing.webpath_controller');
        $mediaPath = $webPath->getPath($this->getParameter('rental_directory'));
        return new Response($serializer->serialize($mediaPath));
    }

    public function cgetByUserAction($userId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findByUser($userId);
        foreach($rentals as $rental) {
            $rental->setMediaPresentation();
        }
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($rentals, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    public function cgetAction() {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rentals = $repository->findAll();
        foreach($rentals as $rental) {
            $rental->setMediaPresentation();
        }
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($rentals, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rental = new Rental();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $rental->setProperty($repository->findOneById($request->request->get('property')));
        $rental->setName($request->request->get('name'));
        $rental->setDescription($request->request->get('description'));
        $rental->setPeopleNumber($request->request->get('peopleNumber'));
        $rental->setMinimumRentalPeriod($request->request->get('minimumRentalPeriod'));
        $rental->setPrice($request->request->get('price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        $rental->setProperty($repository->findOneById($request->request->get('property')));
        $rental->setName($request->request->get('name'));
        $rental->setDescription($request->request->get('description'));
        $rental->setPeopleNumber($request->request->get('peopleNumber'));
        $rental->setMinimumRentalPeriod($request->request->get('minimumRentalPeriod'));
        $rental->setPrice($request->request->get('price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rental);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($rental);
        $em->flush();
        $json = $serializer->serialize($rental, 'json', SerializationContext::create()->setGroups(array('id')));
        return new Response($json);
    }

    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $webPage = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($webPage->getWebPageTrs())) {
            throw new BadRequestHttpException("You can't delete this webPage because some translation are related.");
        } else {
            $em->remove($webPage);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

    public function getPricesByDateAction($rental, $start, $end) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Rental');
        $rental = $repository->findOneById($rental);
        $allDates = array();
        $date = strtotime($start);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalPromotion');
        while($date < strtotime($end)){
            $rentalPromotion = $repository->findPromotionByDate($date, $rental->getId());
            if(empty($rentalPromotion) || $rentalPromotion == NULL) {
                $price = $rental->getPrice();
            } else {
                $price =  $rental->getPrice() * ((100-$rentalPromotion->getReduction())/100);
            }
            $allDates[$date] = $price;
            $date = strtotime('+1 days', $date);
        }
        $json = $serializer->serialize($allDates, 'json');
        return new Response($json);
    }

}