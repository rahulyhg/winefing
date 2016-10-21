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
use Winefing\ApiBundle\Entity\LanguageEnum;
use Winefing\ApiBundle\Entity\Article;
use Winefing\ApiBundle\Entity\ArticleTr;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;

class ArticleController extends Controller implements ClassResourceInterface
{
    /**
     * Liste de toute les articles possible en base
     * @return Response
     */
    public function cgetAction()
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $articles = $repository->findAll();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $repositoryArticleTr = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
        foreach ($articles as $article) {
            $languageId = array();
            foreach($article->getArticleTrs() as $articleTr) {
                $languageId[] = $articleTr->getLanguage()->getId();
            }
            $missingLanguages = $repository->findMissingLanguages($languageId);
            $article->setMissingLanguages(new ArrayCollection($missingLanguages));
            $title = $repositoryArticleTr->findTitleByArticleIdAndLanguageCode($article->getId(), LanguageEnum::Français);
            if(empty($title)) {
                $title = $repositoryArticleTr->findTitleByArticleIdAndLanguageCode($article->getId(), LanguageEnum::English);
            }
            $article->setTitle($title);
        }
        $serializer = $this->container->get("winefing.serializer_controller");
        $json = $serializer->serialize($articles);
        return new Response($json);
    }
    /**
     * Create or update a characteristicCategory from the submitted data.<br/>
     *
     *
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $article = new Article();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $article->setUser($user);
        $article->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($article);
        $em->flush();
        return new Response($serializer->serialize($article));
    }
    public function putArticleCategory(Request $request) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($request->request->get('article'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategory = $repository->findOneById($request->request->get('id'));
        if($article->getArticleCategories()->contains($articleCategory)) {

        }

    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $article->setUser($user);
        $article->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($article);
        $em->flush();
        return new Response($serializer->serialize($article));
    }

    public function postFileAction(Request $request) {
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $uploadedFile = $request->files->get('picture');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Image);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($request->request->get('article'));
        if(empty($article)) {
            throw new BadRequestHttpException('The articleId is mandatory');
        }
        if (!empty($article->getPicture()) && !empty($uploadedFile)) {
            unlink($this->getParameter('article_directory_upload') . $article->getPicture());
        }
        $uploadedFile->move(
            $this->getParameter('article_directory_upload'),
            $fileName
        );
        $em->persist($article);
        $em->flush();
        return new Response($serializer->serialize($article));
    }

    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Characteristic');
        $article = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($article->getArtileTrs())) {
            throw new BadRequestHttpException("You can't delete this article because some translation are related.");
        } else {
            $em->remove($article);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

}