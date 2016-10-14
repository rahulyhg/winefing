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
use Winefing\ApiBundle\Entity\ArticleCategory;
use Winefing\ApiBundle\Entity\ArticleCategoryTr;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Winefing\ApiBundle\WinefingApiBundle;


class ArticleCategoryController extends Controller implements ClassResourceInterface
{
    public function cgetAction() {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategories = $repository->findAll();
        $serializer = $this->container->get('winefing.serializer_controller');
        $json = $serializer->serialize($articleCategories);
        return new Response($json);
    }

    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $articleCategory = new ArticleCategory();
        $articleCategory->setDescription($request->request->get('description'));
        if(!empty($request->request->get('categoryPere'))){
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
            $articleCategoryPere = $repository->findOneById($request->request->get('categoryPere'));
            $articleCategory->setCategoryPere($articleCategoryPere);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($articleCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($articleCategory);
        $em->flush();
        $serializer = $this->container->get("winefing.serializer_controller");
        $json = $serializer->serialize($articleCategory);
        return new Response($json);
    }

    /**
     * Create an articleCategory from the submitted data.
     *
     *
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategory = $repository->findOneById($request->request->get('id'));
        $articleCategory->setDescription($request->request->get('description'));
        if(!empty($request->request->get('categoryPere'))){
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
            $articleCategoryPere = $repository->findOneById($request->request->get('categoryPere'));
            $articleCategory->setCategoryPere($articleCategoryPere);
        } else {
            $articleCategory->setCategoryPere(NUll);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($articleCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($articleCategory);
        $em->flush();
        $serializer = $this->container->get("winefing.serializer_controller");
        $json = $serializer->serialize($articleCategory);
        return new Response($json);
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategory = $repository->findOneById($id);
        if(count($articleCategory->getArticles()) > 0) {
            throw new BadRequestHttpException("You can't delete this category because there is some article related.");
        } else {
            $em = $this->getDoctrine()->getManager();
            $em->remove($articleCategory);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }
}