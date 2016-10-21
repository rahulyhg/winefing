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




class WebPageController extends Controller
{
    /**
     * @Route("/web/pages", name="web_pages")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_web_pages'));
        $webPages = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_languages_picture_path'));
        $languagePicturePath = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/webPage/index.html.twig', array("webPages" => $webPages, 'languagePicturePath' => $languagePicturePath));
    }
}