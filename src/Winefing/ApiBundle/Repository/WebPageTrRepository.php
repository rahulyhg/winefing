<?php

namespace Winefing\ApiBundle\Repository;

/**
 * WebPageTrRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class WebPageTrRepository extends \Doctrine\ORM\EntityRepository
{
    function findOneByWebPageIdAndLanguageCode($webPageId, $languageCode) {
        $query = $this->createQueryBuilder('webPageTr')
            ->select('webPageTr.title')
            ->join('webPageTr.webPage', 'webPage')
            ->join('webPageTr.language', 'language')
            ->where('webPage.id = :webPageId and language.code = :languageCode')
            ->setParameter('webPageId', $webPageId)
            ->setParameter('languageCode', $languageCode)
            ->setMaxResults(1)
            ->getQuery();
        return $query->getResult();
    }
}
