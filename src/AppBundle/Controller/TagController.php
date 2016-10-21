<?php
/**
 * Created by PhpStorm.
 * User: audreycarval
 * Date: 21/09/2016
 * Time: 09:39
 */

namespace AppBundle\Controller;
use AppBundle\Form\TagType;
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
use Winefing\ApiBundle\Entity\Tag;
use Winefing\ApiBundle\Entity\TagTr;

class TagController extends Controller
{
    /**
     * @Route("/tags", name="tags")
     */
    public function cgetAction() {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $response = $api->get($this->get('_router')->generate('api_get_languages_picture_path'));
        $languagePicturePath = $serializer->decode($response->getBody()->getContents());
        $response = $api->get($this->get('_router')->generate('api_get_tags'));
        $tags = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/tag/index.html.twig', array("tags" => $tags, 'languagePicturePath' => $languagePicturePath));
    }

    /**
     * @Route("/tag/newForm/{id}", name="tag_new_form")
     */
    public function newFormAction($id = '') {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
        $tag = $repository->findOneById($id);
        if(empty($tag)) {
            $tag = new Tag();
        }
        $languagesId = array();
        if($tag->getTagTrs() != null) {
            foreach ($tag->getTagTrs() as $tagTr) {
                $languagesId[] = $tagTr->getLanguage()->getId();
            }
        }
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
        $missingLanguages = $repository->findMissingLanguages($languagesId);
        foreach($missingLanguages as $language) {
            $tagTr = new TagTr();
            $tagTr->setLanguage($language);
            $tag->addTagTr($tagTr);
        }
        $form = $this->createForm(TagType::class, $tag, array('action' => $this->generateUrl('tag_submit'),
            'method' => 'POST'));
        return $this->render('admin/tag/form.html.twig', array(
            'form' => $form->createView()
        ));
    }
    /**
     * @Route("/tag/submit", name="tag_submit")
     */
    public function submitAction(Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $serializer = $this->container->get('winefing.serializer_controller');
        $tag = $request->request->all()["tag"];
        $tagTrs = $tag["tagTrs"];
        unset($tag["tagTrs"]);
        if(empty($tag["id"])) {
            $response = $api->post($this->get('_router')->generate('api_post_tag'), $tag);
            $tag = $serializer->decode($response->getBody()->getContents());
        }
        foreach($tagTrs as $tagTr) {
            $tagTr["tag"] = $tag["id"];
            if(empty($tagTr["id"])) {
                $api->post($this->get('_router')->generate('api_post_tag_tr'), $tagTr);
            } else {
                $api->put($this->get('_router')->generate('api_put_tag_tr'), $tagTr);

            }
        }
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The tag is well modified/created.");
        return $this->redirectToRoute('tags');

    }
    /**
     * @Route("/tag/delete/{id}", name="tag_delete")
     */
    public function deleteAction($id, Request $request) {
        $api = $this->container->get('winefing.api_controller');
        $api->delete($this->get('_router')->generate('api_delete_tag', array('id' => $id)));
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The tag is well deleted.");
        return $this->redirectToRoute('tags');


    }
}