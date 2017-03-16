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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use Winefing\ApiBundle\Entity\DayPrice;
use Winefing\ApiBundle\Entity\RentalOrder;
use Winefing\ApiBundle\Entity\RentalOrderGift;
use Winefing\ApiBundle\Entity\StatusOrderEnum;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;


class RentalOrderGiftController extends Controller implements ClassResourceInterface
{

    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $rentalOrderGift = new RentalOrderGift();

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrderGift->setRentalOrder($repository->findOneById($request->request->get('rentalOrder')));
        $rentalOrderGift->setMessage($request->request->get('message'));
        $rentalOrderGift->setSignature($request->request->get('signature'));
        $rentalOrderGift->setPrice($this->getParameter('rental_order_gift_price'));
        $validator = $this->get('validator');
        $errors = $validator->validate($rentalOrderGift);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($rentalOrderGift);
        $em->flush();
        return new Response($serializer->serialize($rentalOrderGift, 'json', SerializationContext::create()->setGroups(array('id'))));
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CreditCard');
        $creditCard = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($creditCard);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}