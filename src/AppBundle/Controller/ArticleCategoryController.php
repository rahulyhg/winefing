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
     * Display all article's categories for the admin users.
     * @Route("/articleCategory/", name="articleCategory")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $response = $api->get($this->get('router')->generate('api_get_article_categories'));
        $serializer = $this->container->get('jms_serializer');
        $articleCategories = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\ArticleCategory>', 'json');
        return $this->render('admin/blog/articleCategory.html.twig', array("articleCategories" => $articleCategories)
        );
    }

    /**
     * Create a new form for the article category. If the id is empty -> create a new article cateroy, else, get the article category.
     * @Route("/articleCategory/newForm/{id}", name="articleCategory_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:ArticleCategory');
        $articleCategory = $repository->findOneById($id);
        if(empty($articleCategory)) {
            $articleCategory = new ArticleCategory();
        }
        $languagesId = array();
        if($articleCategory->getArticleCategoryTrs() != null) {
            foreach ($articleCategory->getArticleCategoryTrs() as $tr) {
                $languagesId[] = $tr->getLanguage()->getId();
            }
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $tr = new ArticleCategoryTr();
            $tr->setLanguage($language);
            $articleCategory->addArticleCategoryTr($tr);
        }
        $action = $this->generateUrl('articleCategory_submit');
        $method = 'POST';
        $form = $this->createForm(ArticleCategoryType::class, $articleCategory, array(
            'action' => $action,
            'method' => $method));
        return $this->render('admin/blog/form/articleCategory.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * Create a new article category.
     * @Route("/articleCategory/submit", name="articleCategory_submit")
     */
    public function postAction(Request $request)
    {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $articleCategory = $request->request->all()["article_category"];
        $articleCategoryTrs = $request->request->all()["article_category"]["articleCategoryTrs"];
        unset($articleCategory["articleCategoryTrs"]);
        if(empty($articleCategory["id"])) {
            $response = $api->post($this->get('router')->generate('api_post_article_category'), $articleCategory);
        } else {
            $response = $api->put($this->get('router')->generate('api_put_article_category'), $articleCategory);
        }
        $articleCategory = $serializer->decode($response->getBody()->getContents());
        $this->submitArticleCategoryTrs($api, $articleCategoryTrs, $articleCategory);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article's category is well created/modified.");
        return $this->redirectToRoute('articleCategory');
    }

    /**
     * Foreach traduction, create or update the traduction.
     * @param $articleCategoryTrs
     * @param $articleCategory
     */
    public function submitArticleCategoryTrs($api, $articleCategoryTrs, $articleCategory) {
        foreach($articleCategoryTrs as $articleCategoryTr) {
            if(empty($articleCategoryTr["id"])) {
                $articleCategoryTr["articleCategory"] = $articleCategory["id"];
                $api->post($this->get('router')->generate('api_post_articlecategory_tr'), $articleCategoryTr);
            } else {
                $api->put($this->get('router')->generate('api_put_articlecategory_tr'), $articleCategoryTr);
            }
        }
    }
    /**
     * Delete an article ctegory, and so the relation between the articleCategory and the article.
     * @Route("/articleCategory/delete/{id}", name="articleCategory_delete")
     */
    public function deleteAction($id, Request $request)
    {
        $api = $this->container->get("winefing.api_controller");
        $api->delete($this->get('router')->generate('api_delete_article_category', array('id'=>$id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The article's category is well deleted.");
        return $this->redirectToRoute('articleCategory');
    }
}