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
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Nelmio\ApiDocBundle\Annotation\ApiDoc;
use FOS\RestBundle\Controller\Annotations\RequestParam;
use FOS\RestBundle\Controller\Annotations\FileParam;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\Templating\Helper\AssetsHelper;
use JMS\Serializer\SerializationContext;
use FOS\RestBundle\Controller\Annotations\Get;
use Winefing\ApiBundle\Entity\DomainMediasPresentation;
use Winefing\ApiBundle\Entity\DomainStatistic;
use Winefing\ApiBundle\Entity\Pagination;


class PaginationController extends Controller implements ClassResourceInterface
{
    /**
     * @ApiDoc(
     *  resource=true,
     *  description="Get all the object by language",
     *  views = { "index", "blog" },
     *  requirements={
     *     {
     *          "name"="language", "dataType"="integer", "required"=true, "description"="language code"
     *      }
     *  },
     * output= {
     *      "class"="Winefing\ApiBundle\Entity\Article"
     *     },
     *  statusCodes={
     *         200="Returned when successful",
     *         204={
     *           "Returned when no content",
     *         }
     *     }
     * )
     */
    public function getArticlesAction($language, $maxPerPage, Request $request)
    {
        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Article');
        $page = 1;
        $params = $request->query->all();
        if(array_key_exists('page', $params)) {
            $page = $params["page"];
        }
        $params["page"] = $page;

        $paginator = $repository->findWithParams($maxPerPage, $language, $params);
        $articles = $paginator->getIterator();
        foreach ($articles as $article) {
            $article->setTr($language);
        }
        $total = ceil($paginator->count() / $maxPerPage);
        $pagination = new Pagination($page, $total);
        $pagination->setArticles($articles);

        $serializer = $this->container->get("jms_serializer");
        $json = $serializer->serialize($pagination, 'json', SerializationContext::create()->setGroups(array('articles', 'default', 'tags', 'user', 'articleCategories')));
        return new Response($json);
    }
    public function getDomainsAction($language, $maxPerPage, Request $request)
    {
        $serializer = $this->container->get('jms_serializer');
        $page = 1;
        $params = $request->query->all();
        if(array_key_exists('page', $params)) {
            $page = $params["page"];
        }
        $params["page"] = $page;

        $repository = $this->getDoctrine()->getRepository('WinefingApiBundle:Domain');
        $paginator = $repository->findWithCriterias($maxPerPage, $params);
        $domains = $paginator->getIterator();
        foreach($domains as $domain) {
            $domainMediasPresentation = new DomainMediasPresentation($domain);
            $domain->setDomainMediasPresentation($domainMediasPresentation);
            $domainStatistic = new DomainStatistic($domain);
            $domain->setDomainStatistic($domainStatistic);
            $domain->setTr($language);
        }
        $total = ceil($paginator->count() / $maxPerPage);
        $pagination = new Pagination($page, $total);
        $pagination->setDomains($domains);
        $json = $serializer->serialize($pagination, 'json', SerializationContext::create()->setGroups(array('default', 'domains', 'domainMediasPresentation', 'stat')));
        return new Response($json);
    }
}