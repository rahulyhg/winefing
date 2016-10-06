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




class ArticleCategoryController extends Controller
{
    /**
     * @Route("/articleCategory/", name="articleCategory")
     */
    public function cgetAction() {
        $client = new Client();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategories = $repository->findAll();
//        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategoryTr');
//        $flag = true;
//        foreach ($articleCategories as $articleCategory) {
//            $currentCategory = $articleCategory;
//            $hierarchie = $repository->findFrenchName($articleCategory->getId());
//            while($flag) {
//                $articleCategory = $articleCategory->getCategoryPere();
//                if($articleCategory == NULL || $currentCategory == $hierarchie){
//                    $flag = false;
//                } else {
//                    $frenchName = $repository->findFrenchName($articleCategory->getId());
//                    $hierarchie = $frenchName[0];
//                }
//            }
////            $hierarchie = "";
////            do {
////                $articleCategory = $articleCategory->getCategoryPere();
////                //$hierarchie = $repository->findFrenchName($articleCategory->getId());
////            } while(!empty($articleCategory->getCategoryPere()));
////            $articleCategory->setHierarchie($hierarchie);
//        }
        return $this->render('admin/blog/articleCategory.html.twig', array("articleCategories" => $articleCategories)
        );
    }

    /**
     * @Route("/articleCategory/newForm/{id}", name="articleCategory_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategory = $repository->findOneById($id);
        if(empty($articleCategory)) {
            $articleCategory = new ArticleCategory();
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $languages = $repository->findAll();
            foreach($languages as $language) {
                $articleCategoryTr = new ArticleCategoryTr();
                $articleCategoryTr->setLanguage($language);
                $articleCategory->addArticleCategoryTr($articleCategoryTr);
            }
            $action = $this->generateUrl('articleCategory_post');
            $method = 'POST';
        } else {
            $action = $this->generateUrl('articleCategory_put');
            $method = 'PUT';
        }
        $form = $this->createForm(ArticleCategoryType::class, $articleCategory, array(
            'action' => $action,
            'method' => $method));
        return $this->render('admin/blog/form/articleCategory.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/articleCategory/post", name="articleCategory_post")
     */
    public function postAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->post("http://104.47.146.137/winefing/web/app_dev.php/api/articles/categories", $request->request->all()["article_category"], null);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article's category is well created.");
        return new Response();
        //return $this->redirectToRoute('articleCategory');
    }

    /**
     * @Route("/articleCategory/put", name="articleCategory_put")
     */
    public function putAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $api->put("http://104.47.146.137/winefing/web/app_dev.php/api/article/category", $request->request->all()["article_category"], null);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article's category is well modified.");
        return $this->redirectToRoute('articleCategory');
    }

    /**
     * @Route("/articleCategory/delete/{id}", name="articleCategory_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $client = new Client();
        try {
            $client->request('DELETE', 'http://104.47.146.137/winefing/web/app_dev.php/api/articles/'.$id.'/category');
        } catch (\Exception $e) {
            error_log($e->getMessage());
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article's category is well deleted.");
        return $this->redirectToRoute('articleCategory');
    }
}