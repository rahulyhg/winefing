<?php
/**
 * Created by PhpStorm.
 * User: Audrey
 * Date: 10/08/2016
 * Time: 20:38
 */

namespace Winefing\ApiBundle\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use FOS\RestBundle\Routing\ClassResourceInterface;
use Doctrine\ORM\EntityManager;
use Doctrine\Common\Collections\ArrayCollection;
use Winefing\ApiBundle\Entity\Domain;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;


class DomainController extends Controller implements ClassResourceInterface
{

    public function getAction($id)
    {
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($id);
        $json = $serializer->serialize($domain);
        return new Response($json);
    }

    public function getByUserAction($userId) {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneByUser($userId);
        $serializer = $this->container->get('jms_serializer');
        $json = $serializer->serialize($domain, 'json');
        return new Response($json);
    }
    public function postAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $domain = new Domain();
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $domain->setWineRegion($repository->findOneById($request->request->get('wineRegion')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $domain->setAddress($repository->findOneById($request->request->get('address')));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:User');
        $domain->setUser($repository->findOneById($request->request->get('user')));
        $domain->setName($request->request->get('name'));
        $domain->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($domain);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($domain);
        $em->flush();
        $json = $serializer->serialize($domain);
        return new Response($json);
    }
    public function putAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:WineRegion');
        $domain->setWineRegion($repository->findOneById($request->request->get('wineRegion')));
        $domain->setName($request->request->get('name'));
        $domain->setDescription($request->request->get('description'));
        $validator = $this->get('validator');
        $errors = $validator->validate($domain);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($domain);
        $em->flush();
        $json = $serializer->serialize($domain);
        return new Response($json);
    }
    public function putAddressAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $serializer = $this->container->get('winefing.serializer_controller');
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $domain = $repository->findOneById($request->request->get('id'));
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Address');
        $domain->setAddress($repository->findOneById($request->request->get('address')));
        $validator = $this->get('validator');
        $errors = $validator->validate($domain);
        if (count($errors) > 0) {
            $errorsString = (string) $errors;
            return new Response(400, $errorsString);
        }
        $em->persist($domain);
        $em->flush();
        $json = $serializer->serialize($domain);
        return new Response($json);
    }

    /**
     * Delete a web page
     * @param $id
     * @return Response
     */
    public function deleteAction($id)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $webPage = $repository->findOneById($id);
        $em = $this->getDoctrine()->getManager();
        if(!empty($webPage->getWebPageTrs())) {
            throw new BadRequestHttpException("You can't delete this webPage because some translation are related.");
        } else {
            $em->remove($webPage);
            $em->flush();
        }
        return new Response(json_encode([200, "success"]));
    }

}