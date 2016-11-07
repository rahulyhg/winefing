<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\ArticleCategoryType;
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
use AppBundle\Form\ArticleTrType;


class ArticleTrController extends Controller
{
    /**
     * @Route("/articleTr/{id}/{articleId}/{languageId}", name="articleTr_new_form")
     */
    public function newFormAction($id = '', $articleId ='', $languageId = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleTr');
        $articleTr = $repository->findOneById($id);
        if(empty($articleTr)) {
            $articleTr = new ArticleTr();
        }
        if(!empty($articleId)) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
            $article = $repository->findOneById($articleId);
            $articleTr->setArticle($article);
        }
        if(!empty($languageId)) {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $language = $repository->findOneById($languageId);
            $articleTr->setLanguage($language);
        }
        $form = $this->createForm(ArticleTrType::class, $articleTr, array('action' => $this->generateUrl('articleTr_submit'), 'method'=> 'POST'));
        return $this->render('admin/blog/form/articleTr.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/submit/articleTr/", name="articleTr_submit")
     */
    public function submitAction(Request $request){
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get("winefing.serializer_controller");
        $article = $request->request->all()["article_tr"]["article"];
        if(empty($article["id"])) {
            $response = $api->post($this->get('router')->generate('api_post_article'), $article);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_article'), $article);
        }
        $article = $serializer->decode($response->getBody()->getContents());
        $picture = $request->files->all()["article_tr"]["article"]["picture"];
        $param["article"] = $article["id"];
        if(!empty($picture)) {
            $api->file($this->get('router')->generate('api_post_article_file'), $param, $picture);
        }
        $articleTr = $request->request->all()["article_tr"];
        unset($articleTr["article"]);
        $articleTr["article"] = $article["id"];
        if(empty($articleTr["id"])) {
            $api->post($this->get('router')->generate('api_post_article_tr'), $articleTr);
        } else {
            $api->put($this->get('router')->generate('api_put_article_tr'), $articleTr);
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article is well created/modified.");
        return $this->redirectToRoute('articles');
    }

    /**
     * @Route("/delete/articleTr/{id}", name="articleTr_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('router')->generate('api_delete_article_tr', array('id' => $id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article is well deleted.");
        return $this->redirectToRoute('articles');
    }
    /**
     * @Route("/activated/articleTr", name="articleTr_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put($this->get('router')->generate('api_put_article_tr_activated'), $request->request->all());
        return new Response(json_encode([200, "success"]));
    }
}