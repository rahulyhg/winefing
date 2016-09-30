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



class CharacteristicController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toute les formats possible en base
     * @return Response
     */
    public function cgetAction($scope)
    {
        $encoders = array(new JsonEncoder());
        $normalizers = array(new ObjectNormalizer());
        $serializer = new Serializer($normalizers, $encoders);

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristics = $repository->findAll();

        $json = $serializer->serialize($characteristics, 'json');

        return new Response($json);
    }
    /**
     * Create or update a characteristicCategory from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $new = false;
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $characteristic = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicChategory = $repository->findOneById($request->request->all()['characteristicCategory']);

        if (empty($characteristic)) {
            $characteristic = new Characteristic();
            $new = true;
        }
        $characteristic->setDescription($request->request->get('description'));
        if($request->request->get('activated') == null){
            $characteristic->setActivated(0);
        } else {
            $characteristic->setActivated($request->request->get('activated'));
        }
        $characteristic->setChacarteristicCategory($characteristicChategory);
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Format');
        $characteristic->setFormat($repository->findOneById($request->request->all()['format']));

        $characteristicTrs = $request->request->all()["characteristicTrs"];
        foreach ($characteristicTrs as $tr) {
            if(empty($tr["id"])) {
                $characteristicTr = new CharacteristicTr();
            } else {
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicTr');
                $characteristicTr =  $repository->findOneById($tr["id"]);
            }
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $characteristicTr->setLanguage($repository->findOneById($tr["language"]));
            $characteristicTr->setName($tr["name"]);
            $characteristic->addCharacteristicTr($characteristicTr);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($characteristic);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        } else {
            if($new) {
                $em->merge($characteristic);
            }
            $em->flush();
        }
        return new Response(json_encode([200, "The characteristic is well created."]));
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