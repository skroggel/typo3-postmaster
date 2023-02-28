<?php

namespace Madj2k\Postmaster\Domain\Repository;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Madj2k\Postmaster\Mail\Mailer;
use Madj2k\Postmaster\Utility\QueueMailUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * QueueMailRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMailRepository extends AbstractRepository
{

    /**
     * @return void
     */
    public function initializeObject(): void
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findByStatusWaitingOrSending
     * ordered by tstampRealSending and sorting and then priority
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * comment: implicitly tested
     */
    public function findByStatusWaitingOrSending(int $limit = 100): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalOr(
                $query->equals('status', QueueMailUtility::STATUS_WAITING),
                $query->equals('status', QueueMailUtility::STATUS_SENDING)
            )
        )
        ->setOrderings(
            array(
                'priority' => QueryInterface::ORDER_ASCENDING,
                'pipeline' => QueryInterface::ORDER_ASCENDING,
                'mailingStatistics.tstampRealSending' => QueryInterface::ORDER_ASCENDING,
            )
        );

        if ($limit > 0) {
            $query->setLimit(intval($limit));
        }

        return $query->execute();
    }


    /**
     * findByMissingMailingStatistics
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findByMissingMailingStatistics(int $limit = 100): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->matching(
            $query->lessThanOrEqual('mailingStatistics', 0)
        );

        if ($limit > 0) {
            $query->setLimit($limit);
        }

        return $query->execute();
    }


    /**
     * findByTstampRealSending
     *
     * @param int $daysAfterSendingStarted
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findByTstampRealSending(
        int $daysAfterSendingStarted = 30
    ): QueryResultInterface {

        $timestamp = time() - intval($daysAfterSendingStarted * 24 * 60 * 60);
        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->greaterThanOrEqual('status', QueueMailUtility::STATUS_SENDING),
                $query->greaterThanOrEqual( 'mailingStatistics.tstampRealSending', $timestamp)
            )
        );

        return $query->execute();
    }


    /**
     * finds all queueMails by tstampFinishedSending and type
     *
     * @param int $daysAfterSendingFinished
     * @param array $types
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function findByTstampFinishedSendingAndTypes(
        int $daysAfterSendingFinished = 30,
        array $types = []
    ): QueryResultInterface {

        $timestamp = time() - intval($daysAfterSendingFinished * 24 * 60 * 60);
        $query = $this->createQuery();

        if (! $types) {
            $types[] = 0;
        }

        $constraints = [
            $query->greaterThanOrEqual('status', QueueMailUtility::STATUS_FINISHED),
            $query->logicalNot($query->equals('status', QueueMailUtility::STATUS_ERROR)),
            $query->in('type', $types)
        ];

        $query->matching(
            $query->logicalAnd(
                $query->lessThanOrEqual('mailingStatistics.tstampFinishedSending', $timestamp),
                $query->logicalAnd(
                    $constraints
                )
            )
        );

        return $query->execute();
    }


    /**
     * findByTstampFavSendingAndType
     *
     * @param int $fromTime
     * @param int $toTime
     * @param int $type
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @todo write tests
     */
    public function findByTstampFavSendingAndType(
        int $fromTime,
        int $toTime,
        int $type = -1
    ): QueryResultInterface {

        $query = $this->createQuery();
        $constraints = [];

        if ($type > -1) {
            $constraints[] = $query->equals('type', $type);
        }

        if ($fromTime) {
            $constraints[] = $query->greaterThanOrEqual('mailingStatistics.tstampFavSending', $fromTime);
        }

        if ($toTime) {
            $constraints[] = $query->lessThanOrEqual('mailingStatistics.tstampFavSending', $toTime);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        $query->setOrderings(
            [
                'status' => QueryInterface::ORDER_ASCENDING,
                'mailingStatistics.tstampFavSending' => QueryInterface::ORDER_DESCENDING,
                'mailingStatistics.tstampFavSending' => QueryInterface::ORDER_DESCENDING,
            ]
        );

        return $query->execute();
    }


    /**
     * deleteByQueueMail
     * We use a straight-forward approach here because it may be a lot of data to delete!
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * comment: implicitly tested
     * @return int
     */
    public function deleteByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_postmaster_domain_model_queuemail');

        return $queryBuilder
            ->delete('tx_postmaster_domain_model_queuemail')
            ->where(
                $queryBuilder->expr()->eq(
                    'uid',
                    $queryBuilder->createNamedParameter($queueMail->getUid(), \PDO::PARAM_INT))
            )
            ->execute();

    }
}
