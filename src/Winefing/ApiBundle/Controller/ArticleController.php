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
use Winefing\ApiBundle\Entity\MediaFormatEnum;
use Winefing\ApiBundle\Entity\Article;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\Annotations\Get;

class ArticleController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all the object for the admin part",
     *  views = { "index", "blog" },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Article"
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     }
     * )
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
            $tradFr = $repositoryArticleTr->findOneByArticleIdLanguageId($article->getId(), LanguageEnum::Français);
            if(!empty($tradFr)) {
                $article->setTitle($tradFr[0]["title"]);
            } else {
                $tradEn = $repositoryArticleTr->findOneByArticleIdLanguageId($article->getId(), LanguageEnum::English);
                if(!empty($tradEn)) {
                    $article->setTitle($tradEn[0]["title"]);
                }
            }
        }
        $serializer = $this->container->get("jms_serializer");
        $json = $serializer->serialize($articles, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all the object by language",
     *  views = { "index", "blog" },
     *  requirements={
     *     {
     *          "name"="language", "dataType"="integer", "required"=true, "description"="language code"
     *      }
     *  },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Article"
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     }
     * )
     * @Get("article/{id}/language/{language}")
     */
    public function cgetByLanguage($language)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $articles = $repository->findAll();
        foreach ($articles as $article) {
            $article->setTr($language);
        }
        $serializer = $this->container->get("jms_serializer");
        $json = $serializer->serialize($articles, 'json', SerializationContext::create()->setGroups(array('default')));
        return new Response($json);
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","blog" },
     *  description="New object.",
     *  input="AppBundle\Form\ArticleType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Article",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('jms_serializer');
        $article = new Article();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $article->setUser($user);
        $article->resetArticleCategories();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategories = $request->request->get('articleCategories');
        foreach($articleCategories as $articleCategory) {
            $article->addArticleCategory($repository->findOneById($articleCategory));
        }
        $article->resetTags();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tags = $request->request->get('tags');
        foreach($tags as $tag) {
            $article->addTag($repository->findOneById($tag));
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($article);
        $em->flush();
        return new Response($serializer->serialize($article, SerializationContext::create()->setGroups(array('id'))));
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","blog" },
     *  description="New object.",
     *  input="AppBundle\Form\ArticleType",
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Article",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $article->setUser($user);
        $article->resetArticleCategories();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategories = $request->request->get('articleCategories');
        foreach($articleCategories as $articleCategory) {
            $article->addArticleCategory($repository->findOneById($articleCategory));
        }
        $article->resetTags();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tags = $request->request->get('tags');
        foreach($tags as $tag) {
            $article->addTag($repository->findOneById($tag));
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new HttpException(400, $errorsString);
        }
        $em->persist($article);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","blog" },
     *  description="Post picture article.",
     *  parameters={
     *     {
     *          "name"="picture", "dataType"="file", "required"=true
     *      },
     *     {
     *          "name"="article", "dataType"="integer", "required"=true, "description"="article id"
     *      }
     *  },
     *  output= {
     *      "class"="Winefing\ApiBundle\Entity\Article",
     *      "groups"={"default"}
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         400="Returned when the entity is not valid"
     *     }
     * )
     */
    public function postFileAction(Request $request) {
        $mediaFormat = $this->container->get('winefing.media_format_controller');
        $uploadedFile = $request->files->get('picture');
        $fileName = md5(uniqid()) . '.' . $uploadedFile->getClientOriginalExtension();
        $extentionCorrect = $mediaFormat->checkFormat($uploadedFile->getClientOriginalExtension(), MediaFormatEnum::Image);
        if($extentionCorrect != 1) {
            throw new BadRequestHttpException($extentionCorrect);
        }
        $em = $this->getDoctrine()->getManager();
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
        $article->setPicture($fileName);
        $em->persist($article);
        $em->flush();
    }
    /**
     * @ApiDoc(
     *  resource=true,
     *  views = { "index","blog" },
     *  description="Delete an article, only if translation are not related.",
     *  statusCodes={
     *         204="Returned when no content",
     *         422="The object can't be deleted."
     *     },
     *  requirements={
     *     {
     *          "name"="id", "dataType"="integer", "required"=true, "description"="article id"
     *      }
     *     },
     *
     * )
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($article->getArtileTrs())) {
            throw new HttpException(422,"You can't delete this article because some translation are related.");
        }
        $em->remove($article);
        $em->flush();
    }

}