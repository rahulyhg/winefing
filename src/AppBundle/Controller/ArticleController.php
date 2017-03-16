<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
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
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Winefing\ApiBundle\Entity\LanguageEnum;
use Winefing\ApiBundle\Entity\Pagination;


class ArticleController extends Controller
{
    /**
     * @Route("admin/articles/", name="admin_articles")
     */
    public function cgetAdminAction() {
        $api = $this->container->get("winefing.api_controller");
        $serializer = $this->container->get("jms_serializer");
        $response = $api->get($this->get('router')->generate('api_get_articles'));
        $articles = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Article>', 'json');

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        foreach ($articles as $article) {
            $languageId = array();
            foreach($article->getArticleTrs() as $articleTr) {
                $languageId[] = $articleTr->getLanguage()->getId();
            }
            $missingLanguages = $repository->findMissingLanguages($languageId);
            $article->setMissingLanguages(new ArrayCollection($missingLanguages));
            $article->setTitle();
        }

        return $this->render('admin/blog/article.html.twig', array("articles" => $articles)
        );
    }
    /**
     * @Route("/articles/", name="articles")
     */
    public function cgetAction(Request $request) {
        $api = $this->container->get("winefing.api_controller");
        $serializer = $this->container->get("jms_serializer");
        $language = $request->getLocale();
        $params = $request->query->get("article_filter");
        $params['page'] = $request->query->get('page');

        //get the pagination article
        $response = $api->get($this->get('router')->generate('api_get_pagination_articles', array('language'=>$language, 'maxPerPage'=>$this->getParameter('maxperpage'))), $params);
        $pagination = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Pagination', 'json');
        $articles = $pagination->getArticles();

        //get the last articles
        $response = $api->get($this->get('router')->generate('api_get_pagination_articles', array('language'=>$language, 'maxPerPage'=>3)));
        $paginationLastArticles = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Pagination', 'json');
        $lastArticles = $paginationLastArticles->getArticles();

        //get the tags
        $response = $api->get($this->get('router')->generate('api_get_tags_articles', array('language'=>$language)));
        $tags = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Tag>', 'json');

        //get the articles categories
        $response = $api->get($this->get('router')->generate('api_get_article_categories_by_language', array('language'=>$language)));
        $articleCategories = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\ArticleCategory>', 'json');

        return $this->render('blog/index.html.twig', array("articles" => $articles, "lastArticles"=>$lastArticles, "articleCategories"=>$articleCategories, "tags"=>$tags, "total"=>$pagination->getTotal())
        );
    }
    /**
     * @Route("/article/{id}", name="article")
     */
    public function getAction($id, Request $request) {
        $api = $this->container->get("winefing.api_controller");
        $serializer = $this->container->get("jms_serializer");
        $language = $request->getLocale();

        //get the articles
        $response = $api->get($this->get('router')->generate('api_get_article_by_language', array('id'=> $id, 'language'=>$language)));
        $article = $serializer->deserialize($response->getBody()->getContents(), 'Winefing\ApiBundle\Entity\Article', 'json');

        //get the similar articals
        $response = $api->get($this->get('router')->generate('api_get_articles_similar', array('language'=>$language, 'article'=>$id)));
        $articles = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\Article>', 'json');

        return $this->render('blog/card.html.twig', array("article" => $article, "articles"=>$articles)
        );
    }
}