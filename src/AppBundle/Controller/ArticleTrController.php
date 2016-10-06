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
    public function newFormAction($id = '', $articleId ='', $languageId = '', Request $request) {
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
            $language = $repository->findOneById($articleId);
            $articleTr->setLanguage($language);
        }
        $form = $this->createForm(ArticleTrType::class, $articleTr, array('action' => $this->generateUrl('articleTr_post'), 'method'=> 'POST'));
        return $this->render('admin/blog/form/articleTr.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/post/articleTr/", name="articleTr_post")
     */
    public function postAction(Request $request){
        $api = $this->container->get('winefing.api_controller');
        $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/articles/trs", $request->request->all()["article_tr"], null);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article is well created/modified.");
        return $this->redirectToRoute('article');
    }

    /**
     * @Route("/delete/articleTr/{id}", name="articleTr_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $client = new Client();
        $response = $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/articles/' . $id.'/tr');
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article is well deleted.");
        return $this->redirectToRoute('article');
    }
    /**
     * @Route("/activated/articleTr", name="articleTr_activated")
     */
    public function putActivatedAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->put('http://104.47.146.137/winefing/web/app_dev.php/api/article/tr/activated', $request->request->all());
        return new Response(json_encode([200, "success"]));
    }
}