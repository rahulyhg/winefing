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
use Winefing\ApiBundle\Entity\Address;
use Winefing\ApiBundle\Entity\InvoiceInformation;
use Winefing\ApiBundle\Entity\StatusOrderEnum;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\Get;


class InvoiceInformationController extends Controller implements ClassResourceInterface
{

    /**
     * Create or update a language from the submitted data.<br/>
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $invoiceInformation = new InvoiceInformation();

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $invoiceInformation->setUser($repository->findOneById($request->request->get('user')));

        //set billing address
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $address = $repository->findOneById($request->request->get('billingAddress'));
        $invoiceInformation->setBillingAddress($address);


        //set delivering address
        $address = $repository->findOneById($request->request->get('deliveringAddress'));
        if($address instanceof Address) {
            $invoiceInformation->setBillingAddress($address);
        }

        //set billing name
        $invoiceInformation->setBillingName($request->request->get('billingName'));

        //set company informations
        $invoiceInformation->setCompanyName($this->getParameter('companyName'));
        $invoiceInformation->setRcs($this->getParameter('rcs'));
        $invoiceInformation->setSiren($this->getParameter('siren'));
        $invoiceInformation->setSiret($this->getParameter('siret'));
        $invoiceInformation->setRcsCity($this->getParameter('rcsCity'));
        $invoiceInformation->setLegalForm($this->getParameter('legalForm'));
        $invoiceInformation->setStreetWinefing($this->getParameter('streetWinefing'));
        $invoiceInformation->setPostalCodeWinefing($this->getParameter('postalCodeWinefing'));
        $invoiceInformation->setCityWinefing($this->getParameter('cityWinefing'));
        $invoiceInformation->setTvaNumber($this->getParameter('tvaNumber'));

        $invoiceInformation->setStatus($request->request->get('status'));

        $validator = $this->get('validator');
        $errors = $validator->validate($invoiceInformation);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($invoiceInformation);
        $em->flush();
        return new Response($serializer->serialize($invoiceInformation, 'json', SerializationContext::create()->setGroups(array('id','default'))));
    }
    public function patchStatusAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:InvoiceInformation');
        $invoiceInformation = $repository->findOneById($request->request->get('id'));
        $invoiceInformation->setStatus($request->request->get('status'));

        $validator = $this->get('validator');
        $errors = $validator->validate($invoiceInformation);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            throw new HttpException(400, $errorsString);
        }
        $em->persist($invoiceInformation);
        $em->flush();
    }

    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","rental"},
     *  description="Return the rental order by user",
     *  statusCodes={
     *         200="Returned when successful",
     *         204="Returned when no content"
     *     }
     *
     * )
     */
    public function getByUserAction($user) {
        $serializer = $this->container->get('jms_serializer');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:InvoiceInformation');
        $invoiceInformations = $repository->findWithUser($user);
        $json = $serializer->serialize($invoiceInformations, 'json', SerializationContext::create()->setGroups(array('id', 'default')));
        return new Response($json);
    }
}