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
use Winefing\ApiBundle\Entity\Characteristic;
use Winefing\ApiBundle\Entity\CharacteristicTr;
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class CharacteristicTrController extends Controller implements ClassResourceInterface
{

    /**
     * Create a characteristicTr from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $characteristicTr = new CharacteristicTr();

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('characteristic'));
        $characteristicTr->setCharacteristic($characteristic);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $characteristicTr->setLanguage($repository->findOneById($request->request->get("language")));
        $characteristicTr->setName($request->request->get("name"));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristicTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristicTr);
        $em->flush();
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($characteristicTr, 'json');
        return new Response($json);
    }

    /**
     * Update a characteristicTr from the submitted data.<br/>
     * @param Request $request
     * @return Response
     */
    function putAction(Request $request){
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicTr');
        $characteristicTr =  $repository->findOneById($request->request->get("id"));
        $characteristicTr->setName($request->request->get("name"));

        $validator = $this->get('validator');
        $errors = $validator->validate($characteristicTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($characteristicTr);
        $em->flush();
        $encoder = new JsonEncoder();
        $normalizer = new ObjectNormalizer();
        $normalizer->setCircularReferenceHandler(function ($object) {
            return $object->getId();
        });
        $serializer = new Serializer(array($normalizer), array($encoder));
        $json = $serializer->serialize($characteristicTr, 'json');
        return ($json);
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        $em->remove($characteristic);
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}