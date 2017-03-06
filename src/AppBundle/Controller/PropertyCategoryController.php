<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\PropertyCategoryType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Doctrine\Common\Collections\ArrayCollection;
use Symfony\Component\Form\Extension\Core\Type\TextareaType;
use Winefing\ApiBundle\Entity\PropertyCategory;
use Winefing\ApiBundle\Entity\PropertyCategoryTr;

class PropertyCategoryController extends Controller
{
    /**
     * @Route("/propertyCategories", name="propertyCategories")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('jms_serializer');
        $response = $api->get($this->get('_router')->generate('api_get_property_categories'));
        $propertyCategories = $serializer->deserialize($response->getBody()->getContents(), 'ArrayCollection<Winefing\ApiBundle\Entity\PropertyCategory>', 'json');
        return $this->render('admin/propertyCategory/index.html.twig', array("propertyCategories" => $propertyCategories));
    }

    /**
     * @Route("/property-category/newForm/{id}", name="propertyCategory_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:PropertyCategory');
        $propertyCategory = $repository->findOneById($id);
        if(empty($propertyCategory)) {
            $propertyCategory = new PropertyCategory();
        }
        $languagesId = array();
        if($propertyCategory->getPropertyCategoryTrs() != null) {
            foreach ($propertyCategory->getPropertyCategoryTrs() as $propertyCategoryTr) {
                $languagesId[] = $propertyCategoryTr->getLanguage()->getId();
            }
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $propertyCategoryTr = new PropertyCategoryTr();
            $propertyCategoryTr->setLanguage($language);
            $propertyCategory->addPropertyCategoryTr($propertyCategoryTr);
        }
        $form = $this->createForm(PropertyCategoryType::class, $propertyCategory, array('action' => $this->generateUrl('propertyCategory_submit'),
            'method' => 'POST'));
        return $this->render('admin/propertyCategory/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/propertyCategory/submit", name="propertyCategory_submit")
     */
    public function submitAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $propertyCategory = $request->request->all()["property_category"];
        $propertyCategoryTrs = $propertyCategory["propertyCategoryTrs"];
        unset($propertyCategory["propertyCategoryTrs"]);
        if(empty($propertyCategory["id"])) {
            $response = $api->post($this->get('_router')->generate('api_post_property_category'), $propertyCategory);
            $propertyCategory = $serializer->decode($response->getBody()->getContents());
        }
        foreach($propertyCategoryTrs as $propertyCategoryTr) {
            $propertyCategoryTr["propertyCategory"] = $propertyCategory["id"];
            if(empty($propertyCategoryTr["id"])) {
                $api->post($this->get('_router')->generate('api_post_propertycategory_tr'), $propertyCategoryTr);
            } else {
                $api->put($this->get('_router')->generate('api_put_propertycategory_tr'), $propertyCategoryTr);

            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The propertyCategory is well modified/created.");
        return $this->redirectToRoute('propertyCategories');

    }
    /**
     * @Route("/propertyCategory/delete/{id}", name="propertyCategory_delete")
     */
    public function deleteAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Property');
        if(!empty($repository->findOneByPropertyCategory($id))) {
            $request->getSession()
                ->getFlashBag()
                ->add('error', "You can't delete this property because some property are related.");
            return $this->redirectToRoute('propertyCategories');
        }
        $api->delete($this->get('_router')->generate('api_delete_property_category', array('id' => $id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The propertyCategory is well deleted.");
        return $this->redirectToRoute('propertyCategories');


    }
}