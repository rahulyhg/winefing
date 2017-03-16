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
                $this->validRentalOrder($api, $id);
                break;
            case StatusOrderEnum::refuse :
                $this->setRentalOrderStatus($api, $id, StatusOrderEnum::refuse);
                break;
            case StatusOrderEnum::cancel :
                $this->cancelRentalOrder($api, $id);
                break;
            default :
                throw new \Exception('Status non valide');
        }
        return $this->redirect($request->query->get('url'));
    }
    public function validRentalOrder($api, $id) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');

        //edit the rental status order
        $this->setRentalOrderStatus($api, $id, StatusOrderEnum::validate);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:RentalOrder');
        $rentalOrder = $repository->findOneById($id);

        if($rentalOrder->getLemonWay()) {
            //validate the paiement initiated
            $lemonWay->moneyInValidate($rentalOrder->getLemonWay()->getTransactionId());

            //edit the rental status order
            $this->setRentalOrderStatus($api, $id, StatusOrderEnum::pay);
        }
    }
    public function cancelRentalOrder($api, $id) {
        $lemonWay = $this->container->get('winefing.lemonway_controller');
        $rentalOrder = $this->getRentalOrder($id);
        $host = $rentalOrder->getRental()->getProperty()->getDomain()->getUser();
        $today = new \DateTime();
        $diff = date_diff($today, $rentalOrder->getStartDate())->format('%R%a');
        if($diff > 0 || $rentalOrder->getInvoiceInformation()->getStatus() == StatusOrderEnum::cancel || $rentalOrder->getInvoiceInformation()->getStatus() == StatusOrderEnum::refuse) {
            if($rentalOrder->getInvoiceInformation()->getStatus() == StatusOrderEnum::initiate || !$rentalOrder->getLemonWay()) {
                $this->setRentalOrderStatus($api, $id, StatusOrderEnum::cancel);
            } else {
                if($diff > 21) {
                    //set the amount to refund it
                    $amount = $rentalOrder->getLemonWay()->getAmountTot() - $rentalOrder->getClientComission();
                    $lemonWay->refundMoneyIn($rentalOrder->getLemonWay()->getTransactionId(), $amount);
                } else {
                    //cancel the lemonWay
                    if($diff < 7) {
                        //pay back the all amount for the host
                        $total = $rentalOrder->getAmount() - $rentalOrder->getInvoiceHost()->getTotalTTC();
                    } else {
                        //pay back 50% of the amount to the host
                        $total = round((($rentalOrder->getAmount() - $rentalOrder->getInvoiceHost()->getTotalTTC())/2), 2);
                    }
                    //execute the pay to pay, from the winefing wallet to the host wallet
                    $lemonWay->sendPayment($host, $total);
                }
                $this->setRentalOrderStatus($api, $id, StatusOrderEnum::cancel);
            }
        } else {
            $this->addFlash('error', $this->get('translator')->trans('error.can_cancel_rental_order'));
        }
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