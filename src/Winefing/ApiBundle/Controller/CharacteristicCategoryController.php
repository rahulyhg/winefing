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
use Winefing\ApiBundle\Entity\CharacteristicCategoryTr;
use Winefing\ApiBundle\Entity\CharacteristicCategory;
use Winefing\ApiBundle\Entity\Scope;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class CharacteristicCategoryController extends Controller implements ClassResourceInterface
{
    /**
     * Create or update a characteristicCategory from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $new = false;
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Scope');
        $scope = $repository->findOneById($request->request->get('scope'));

        if (empty($characteristicCategory)) {
            $characteristicCategory = new CharacteristicCategory();
            $characteristicCategory->setActivated(0);
            $new = true;
        }
        $characteristicCategory->setDescription($request->request->get('description'));
        $characteristicCategory->setScope($scope);

        $characteristicCategoryTrs = $request->request->all()["characteristicCategoryTrs"];
        foreach ($characteristicCategoryTrs as $tr) {
            if(empty($tr["id"])) {
                $characteristicCategoryTr = new CharacteristicCategoryTr();
            } else {
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategoryTr');
                $characteristicCategoryTr =  $repository->findOneById($tr["id"]);
            }
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $characteristicCategoryTr->setLanguage($repository->findOneById($tr["language"]));
            $characteristicCategoryTr->setName($tr["name"]);
            $characteristicCategory->addCharacteristicCategoryTr($characteristicCategoryTr);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($characteristicCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        } else {
            if($new) {
                $em->merge($characteristicCategory);
            }
            $em->flush();
        }
        return new Response(json_encode([200, "The format is well created."]));
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($id);
        if(count($characteristicCategory->getCharacteristics()) > 0) {
            throw new BadRequestHttpException("You can't delete this category because some characteristics are present.");
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($characteristicCategory);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:CharacteristicCategory');
        $characteristicCategory = $repository->findOneById($request->request->get("id"));
        $characteristicCategory->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}