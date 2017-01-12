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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Winefing\ApiBundle\Entity\Box;
use FOS\RestBundle\Request\ParamFetcher;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\BoxItem;
use Winefing\ApiBundle\Entity\CreditCard;
use Winefing\ApiBundle\Entity\DayPrice;
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\StatusOrderEnum;


class DayPriceController extends Controller implements ClassResourceInterface
{
    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $dayPrice = new DayPrice();
        $dayPrice->setDate(new \DateTime($request->request->get('date')));
        $dayPrice->setPrice($request->request->get('price'));

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $dayPrice->setRentalOrder($repository->findOneById($request->request->get('rentalOrder')));

        $validator = $this->get('validator');
        $errors = $validator->validate($dayPrice);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($dayPrice);
        $em->flush();
        return new Response($serializer->serialize($dayPrice, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
}