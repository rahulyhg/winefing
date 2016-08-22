<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace AppBundle\Controller;
use AppBundle\Entity\Language;
use AppBundle\Form\LanguageType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Form\FormBuilderInterface;


class LanguageController extends Controller
{
    /**
     * @Route("/language", name="language")
     */
    public function index(Request $request) {
        $repository = $this->getDoctrine()->getRepository('AppBundle:Language');
/*        $em = $this->getDoctrine()->getManager();
        $language = $repository->findOneById($request->request->get('language')["id"]);
        if(empty($language)) {
            $form = $this->createForm(LanguageType::class, new Language());
        } else {
            var_dump("exist");
            $form = $this->createForm(LanguageType::class, $language);
        }
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            if(!$language) {
                $em->persist($form->getData());
                $em->flush();
            } else {
                $language.setCode($request->request->get('code'));
                $language.setName($request->request->get('name'));
                $language.setPicture($request->request->get('picture'));
                $em->flush();
            }
        }*/

        $languages = $repository->findAll();
        return $this->render('admin/language/index.html.twig', array(
            'languages' => $languages
        ));
    }
    /**
     * @Route("/language/save", name="language_save")
     */
    public function save(Request $request) {
        $id = $request->request->get('language')["id"];
        if(!$id) {
            $this.update($id);
        } else {
            $this.create($request);
        };
        return new Response();
    }

    /**
     * @Route("/language/create", name="language_create")
     */
    public function createAction(Request $request) {
        $em = $this->getDoctrine()->getManager();
        $form = $this->createForm(LanguageType::class, new Language());

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($form->getData());
            $em->flush();
        }
        return $this->render('admin/language/form.html.twig', array(
            'form' => $form->createView()
        ));
    }

    /**
     * @Route("/language/update", name="language_update")
     */
    public function update($id) {
/*        $repository = $this->getDoctrine()->getRepository('AppBundle:Language');
        $em = $this->getDoctrine()->getManager();
        $language = $repository->findOneById($id);
        if($id) {
            $language.setName("llol");
            $em->flush();
        }*/
        return new Response();
    }
}