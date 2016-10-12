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


class ArticleCategoryTrController extends Controller implements ClassResourceInterface
{
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $articleCategoryTr = new ArticleCategoryTr();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $articleCategoryTr->setLanguage($repository->findOneById($request->request->get("language")));
        $articleCategoryTr->setName($request->request->get("name"));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategoryTr->setArticleCategory($repository->findOneById($request->request->get("articleCategoryTr")));
        $validator = $this->get('validator');
        $errors = $validator->validate($articleCategoryTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($articleCategoryTr);
        $em->flush();
        return new Response(json_encode([200, "The format is well created."]));
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
        $articleCategoryTrs = $request->request->all()["articleCategoryTrs"];
        foreach ($articleCategoryTrs as $tr) {
            if(empty($tr["id"])) {
                $articleCategoryTr = new ArticleCategoryTr();
            } else {
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategoryTr');
                $articleCategoryTr = $repository->findOneById($tr["id"]);
            }
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $articleCategoryTr->setLanguage($repository->findOneById($tr["language"]));
            $articleCategoryTr->setName($tr["name"]);
            $articleCategory->addArticleCategoryTr($articleCategoryTr);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($articleCategory);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        } else {
            $em->flush();
        }
        return new Response(json_encode([200, "The article's category is well updated."]));
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