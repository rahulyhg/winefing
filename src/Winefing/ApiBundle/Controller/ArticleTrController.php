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
use Winefing\ApiBundle\Entity\Article;
use Winefing\ApiBundle\Entity\ArticleTr;
use Winefing\ApiBundle\Entity\ArticleCategoryTr;
use Winefing\ApiBundle\Entity\ArticleCategory;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;



class ArticleTrController extends Controller implements ClassResourceInterface
{
    public function postAction(Request $request) {
        $serializer = $this->container->get('winefing.serializer_controller');
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($request->request->get("article"));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $language = $repository->findOneById($request->request->get("language"));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
        if(!empty($repository->findOneByArticleIdLanguageId($article->getId(), $language->getId()))){
            throw new \BadMethodCallException('A traduction of '.$language->getName().' already exist for this article.');
        }

        $articleTr = new ArticleTr();
        $articleTr->setLanguage($language);
        $articleTr->setTitle($request->request->get("title"));
        $articleTr->setShortDescription($request->request->get("shortDescription"));
        $articleTr->setContent($request->request->get("content"));
        $articleTr->setActivated($request->request->get("activated"));
        $articleTr->getArticle()->setDescription($articleTr->getTitle());

        $articleCategories = $article["articleCategories"];
        $articleTr->getArticle()->resetArticleCategories();
        foreach($articleCategories as $articleCategory) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
            $articleTr->getArticle()->addArticleCategory($repository->findOneById($articleCategory));
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($articleTr);
        if (count($errors) > 0) {
            $errorsString = (string)$errors;
            return new Response(400, $errorsString);
        }
        $em->persist($articleTr);
        $em->flush();
        $json = $serializer->serialize($articleTr);
        return new Response($json);
    }
    public function putAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
        $articleTr = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $articleTr->setLanguage($repository->findOneById($request->request->get("language")));
        $articleTr->setTitle($request->request->get("title"));
        $articleTr->setShortDescription($request->request->get("shortDescription"));
        $articleTr->setContent($request->request->get("content"));
        $articleTr->setActivated($request->request->get("activated"));

        $article = $request->request->all()["article"];
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $art = $repository->findOneById($article["id"]);
        if(!empty($art)) {
            $articleTr->setArticle($art);
        } else {
            $articleTr->setArticle(new Article());
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($article["user"]);
        $articleTr->getArticle()->setUser($user);
        $articleTr->getArticle()->setDescription($articleTr->getTitle());

        $articleCategories = $article["articleCategories"];
        $articleTr->getArticle()->resetArticleCategories();
        foreach($articleCategories as $articleCategory) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
            $articleTr->getArticle()->addArticleCategory($repository->findOneById($articleCategory));
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($articleTr);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($articleTr);
        $em->flush();
        $json = $serializer->serialize($articleTr);
        return new Response($json);
    }
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
        $articleTr = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(count($articleTr->getArticle()->getArticleTrs())>1) {
            $em->remove($articleTr);
        } else {
            $em->remove($articleTr->getArticle());
            $em->remove($articleTr);
        }
        $em->flush();
        return new Response(json_encode([200, "The Article is well deleted."]));
    }

    public function putActivatedAction(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
        $articleTr = $repository->findOneById($request->request->get("id"));
        $articleTr->setActivated($request->request->get("activated"));
        $em = $this->getDoctrine()->getManager();
        $em->flush();
        return new Response(json_encode([200, "success"]));
    }
}