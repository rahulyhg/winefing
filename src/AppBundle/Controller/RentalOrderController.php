<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\ArticleCategoryType;
use AppBundle\Form\ArticleType;
use AppBundle\Form\BoxType;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Winefing\ApiBundle\Entity\Article;
use Winefing\ApiBundle\Entity\ArticleCategory;
use Winefing\ApiBundle\Entity\ArticleCategoryTr;
use Winefing\ApiBundle\Entity\ArticleTr;
use Winefing\ApiBundle\Entity\Box;
use Winefing\ApiBundle\Entity\BoxTr;
use Winefing\ApiBundle\Entity\StatusOrderEnum;


class RentalOrderController extends Controller
{
    /**
     * @Route("host/domain/{id}/orders", name="host_rental_orders")
     */
    public function cgetByDomainAction($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $rentalOrders = $this->getRentalOrders($api, $serializer, $id);
        return $this->render('host/rentalOrder/index.html.twig', array('rentalOrders'=>$rentalOrders));
    }
    /**
     * @Route("host/rental/order/{id}/status/{status}", name="rental_order_status")
     */
    public function patchStatusAction($id, $status, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        switch ($status) {
            case StatusOrderEnum::validate :
                $this->validRentalOrder($api, $serializer, $id);
                break;
            case StatusOrderEnum::refuse :
                $this->setRentalOrderStatus($api, $id, StatusOrderEnum::refuse);
                break;
            default :
                throw new \Exception('Status non valide');
        }
        return $this->redirectToRoute('host_rental_orders', array('id'=>$this->get('session')->get('domainId')));
    }
    public function validRentalOrder($api, $serializer, $id) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($id);

        //validate the paiement initiated
        var_dump($lemonWay->moneyInValidate($rentalOrder->getLemonWayTransactionId()));

        //edit the rental status order
        $this->setRentalOrderStatus($api, $id, StatusOrderEnum::pay);
    }
    public function getRentalOrders($api, $serializer, $domainId) {
        $response = $api->get($this->get('_router')->generate('api_get_rental_order_by_domain', array('domain'=>$domainId)));
        $rentalOrders = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\RentalOrder>', 'json');
        return $rentalOrders;
    }

    /**
     * The api route is not working.. why ?
     * @return mixed
     */
    public function getRentalOrder($id) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($id);
        return $rentalOrder;
    }
    public function setRentalOrderStatus($api, $id, $status) {
        $rt["id"] =  $id;
        $rt["status"] =  $status;
        $api->patch($this->get('_router')->generate('api_patch_rental_order_status'),  $rt);
    }

    /**
     * @Route("rental/order/{id}/bill", name="rental_order_bill")
     */
    public function getBillAction($id) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $rentalOrder = $this->getRentalOrder($id);
        return $this->render('user/rental/invoice.html.twig', array('rentalOrder'=>$rentalOrder));
    }
}