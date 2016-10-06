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



class ArticleController extends Controller implements ClassResourceInterface
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

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $articles = $repository->findAll();

        $json = $serializer->serialize($articles, 'json');

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
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $article = $repository->findOneById($request->request->get('id'));
        //var_dump($request->request->all()["articleCategories"]);
        if (empty($article)) {
            $article = new Article();
            $new = true;
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $user = $repository->findOneById($request->request->get('user'));
        $article->setUser($user);
        $article->setDescription($request->request->get('description'));
        $articleCategories = $request->request->all()["articleCategories"];
        foreach($articleCategories as $articleCategory) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
            $article->addArticleCategory($repository->findOneById($articleCategory));
        }
        $articleTrs = $request->request->all()["articleTrs"];
        foreach ($articleTrs as $tr) {
            if(empty($tr["id"])) {
                $articleTr = new ArticleTr();
            } else {
                $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
                $articleTr =  $repository->findOneById($tr["id"]);
            }
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $articleTr->setLanguage($repository->findOneById($tr["language"]));
            $articleTr->setTitle($tr["title"]);
            $articleTr->setShortDescription($tr["shortDescription"]);
            $articleTr->setContent($tr["content"]);
            $articleTr->setActivated($tr["activated"]);
            $article->addArticleTr($articleTr);
        }
        $validator = $this->get('validator');
        $errors = $validator->validate($article);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        } else {
            if($new) {
                $em->merge($article);
            }
            $em->flush();
        }
        return new Response(json_encode([200, "The characteristic is well created."]));
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