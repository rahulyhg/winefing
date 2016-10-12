<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\ArticleCategoryType;
use AppBundle\Form\ArticleTrType;
use AppBundle\Form\ArticleType;
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
use Winefing\ApiBundle\Entity\Article;
use Winefing\ApiBundle\Entity\ArticleCategory;
use Winefing\ApiBundle\Entity\ArticleCategoryTr;
use Winefing\ApiBundle\Entity\ArticleTr;




class ArticleController extends Controller
{
    /**
     * @Route("/article/", name="article")
     */
    public function cgetAction() {
        $client = new Client();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $articles= $repository->findAll();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        foreach ($articles as $article) {
            $missingLanguages = $repository->findMissingLanguagesForArticle($article);
            $article->setMissingLanguages(new ArrayCollection($missingLanguages));
        }
        return $this->render('admin/blog/article.html.twig', array("articles" => $articles)
        );
    }
}