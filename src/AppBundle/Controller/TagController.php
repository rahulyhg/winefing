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
        $response = $api->get('http://104.47.146.137/winefing/web/app_dev.php/api/tags');
        $serializer = $this->container->get('winefing.serializer_controller');
        $tags = $serializer->decode($response->getBody()->getContents());
        return $this->render('admin/tag/index.html.twig', array("tags" => $tags));
    }

    /**
     * @Route("/tag/newForm/{id}", name="tag_new_form")
     */
    public function newFormAction($id = '') {
        if(empty($id)) {
            $tag = new Tag();
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Language');
            $languages = $repository->findAll();
            foreach($languages as $language) {
                $tagTr = new TagTr();
                $tagTr->setLanguage($language);
                $tag->addTagTr($tagTr);
            }
        }
        else {
            $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Tag');
            $tag = $repository->findOneById($id);
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
            $response = $api->post('http://104.47.146.137/winefing/web/app_dev.php/api/tags', $tag, null);
            $tag = $serializer->decode($response->getBody()->getContents());
        }
        foreach($tagTrs as $tagTr) {
            $tagTr["tag"] = $tag["id"];
            if(empty($tagTr["id"])) {
                $api->post('http://104.47.146.137/winefing/web/app_dev.php/api/tags/trs', $tagTr, null);
            } else {
                $api->put('http://104.47.146.137/winefing/web/app_dev.php/api/tag/tr', $tagTr, null);
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
        $api->delete('http://104.47.146.137/winefing/web/app_dev.php/api/tags/'.$id);
        $request->getSession()
            ->getFlashBag()
            ->add('success', "The tag is well deleted.");
        return $this->redirectToRoute('tags');


    }
}