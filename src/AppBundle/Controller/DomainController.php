<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */
namespace AppBundle\Controller;
use AppBundle\Form\AddressType;
use AppBundle\Form\DomainType;
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
use Winefing\ApiBundle\Entity\CharacteristicDomainValue;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\CharacteristicValue;
use Winefing\ApiBundle\Entity\ScopeEnum;


class DomainController extends Controller
{
    /**
     * @Route("/domain/{id}/wishlist", name="domain_add_to_wishlist")
     */
    public function addToWishlistAction($id) {
        if(!empty($this->getUser())) {
            $body['user'] = $this->getUser()->getId();
            $body['domain'] = $id;
            $api = $this->container->get('winefing.api_controller');
            $api->patch($this->get('router')->generate('api_patch_user_domain'), $body);
        } else {
            $error = '%error.not_connecteed%';
            throw new \Exception($error);
        }
        return new Response();
    }

    /**
     * @Route("/domains", name="domains")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response =  $api->get($this->get('router')->generate('api_get_domains'));
        $domains = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Domain>', 'json');
        return $this->render('user/domain/index.html.twig', array(
                'domains' => $domains
        ));
    }
    /**
     * @Route("/domain/edit", name="domain_edit")
     */
    public function getAction(Request $request) {
        $domain = $this->getDomain();
        $this->getDoctrine()->getEntityManager()->persist($domain->getWineRegion());
        $domainForm = $this->createForm(DomainType::class, $domain);
        $domainForm->handleRequest($request);
        if($domainForm->isSubmitted() && $domainForm->isValid()) {
            $domainForm = $request->request->get('domain');
            $domainForm["id"] = $domain->getId();
            $this->submit($domainForm);
            return $this->redirect($this->generateUrl('domain_edit', array('id' => $domain->getId())) . '#presentation');
        }
        $addressForm = $this->createForm(AddressType::class, $domain->getAddress());
        $addressForm->handleRequest($request);
        if($addressForm->isSubmitted() && $addressForm->isValid()) {
            $addressForm = $request->request->get('address');
            $addressForm["id"] = $domain->getAddress()->getId();
            $addressForm["domain"] = $domain->getId();
            $this->submitAddress($addressForm);
            return $this->redirect($this->generateUrl('domain_edit', array('id' => $domain->getId())) . '#address');
        }
        $this->setMissingCharacteristicsAction($domain);
        $characteristicCategories = $this->getCharacteristicCategory($domain);
        if ($request->isMethod('POST')) {
            $media = $request->files->get('media');
            $characteristicValueForm = $request->request->get("characteristicValueForm");
            if (!empty($media)) {
                $media["domain"] = $domain->getId();
                $this->submitPictures($media);
                return $this->redirect($this->generateUrl('domain_edit', array('id' => $domain->getId())) . '#medias');
            } elseif (!empty($characteristicValueForm)) {
                $characteristicValueForm["domain"] = $domain->getId();
                $this->submitCharacteristicPropertyValues($characteristicValueForm);
                return $this->redirect($this->generateUrl('domain_edit', array('id' => $domain->getId())) . '#informations');
            }
        }
        return $this->render('host/domain/edit.html.twig', array(
            'domainForm' => $domainForm->createView(),
            'addressForm'=>$addressForm->createView(),
            'characteristicCategories' => $characteristicCategories,
            'medias' => $domain->getMedias())
        );
    }
    public function getDomain() {
        $userId = $this->getUser()->getId();
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $response = $api->get($this->get('_router')->generate('api_get_domain_by_user', array('userId' => $userId)));
        $domain = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Domain', 'json');
        return $domain;
    }
    public function submit($domain)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_domain'), $domain);
    }
    public function submitPictures($media)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $body["domain"] = $media["domain"];
        foreach($media["medias"] as $media) {
            $uploadDirectory["upload_directory"] = $this->getParameter('domain_directory_upload');
            $response = $api->file($this->get('router')->generate('api_post_media'), $uploadDirectory, $media);
            $media = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Media', "json");
            $body["media"] = $media->getId();
            $api->put($this->get('router')->generate('api_put_media_domain'), $body);
        }
    }

    public function submitAddress($address) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $domain = $address['domain'];
        if(empty($address["id"])) {
            $response =  $api->post($this->get('router')->generate('api_post_address'), $address);
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
            $body["address"] = $address->getId();
            $body["domain"] = $domain;
            $api->patch($this->get('router')->generate('api_patch_property_address'), $body);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_address'), $address);
            $address = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Address', 'json');
        }
        return $address;
    }

    public function submitCharacteristicDomainValues(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $domain = $request->request->all()["characteristicValue"]["domain"];
        foreach($request->request->all()["characteristicValue"]["characteristicValue"] as $characteristicValue) {
            if (empty($characteristicValue["id"])) {
                $response = $api->post($this->get('router')->generate('api_post_characteristic_value'), $characteristicValue);
                $characteristicValue = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\CharacteristicValue', 'json');
                $characteristicDomainValue["domain"] = $domain;
                $characteristicDomainValue["characteristicValue"] = $characteristicValue->getId();
                $api->put($this->get('router')->generate('api_put_characteristic_value_domain'), $characteristicDomainValue);
            } else {
                $api->put($this->get('router')->generate('api_put_characteristic_value'), $characteristicValue);
            }
        }
        return $this->redirect($this->generateUrl('domain_user'). '#informations');
    }

    /**
     * @Route("/delete/domain/{id}", name="domain_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_domain', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The domain is well deleted.");
        return $this->redirectToRoute('domain_user');
    }

    public function setMissingCharacteristicsAction($domain)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $response = $api->get($this->get('_router')->generate('api_get_characteristics_missing', array('id' => $domain->getId(), 'scope'=> ScopeEnum::Domain)));
        $missingCharacteristics = $serializer->decode($response->getBody()->getContents());

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        foreach ($missingCharacteristics as $characteristic) {
            $characteristicValue = new CharacteristicValue();
            $characteristic = $repository->findOneById($characteristic["id"]);
            $characteristicValue->setCharacteristic($characteristic);
            $domain->addCharacteristicValue($characteristicValue);
        }
    }

    public function getCharacteristicCategory($domain) {
        $list = array();
        foreach($domain->getCharacteristicValues() as $characteristicValue) {
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