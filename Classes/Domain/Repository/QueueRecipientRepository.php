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

use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\Utility\QueueRecipientUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * QueueRecipientRepository
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueRecipientRepository extends AbstractRepository
{

    /**
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * findAllByQueueMailWithStatusWaiting
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * comment: implicitly tested
     */
    public function findAllByQueueMailWithStatusWaiting(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail,
        int $limit = 25
    ): QueryResultInterface {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', intval($queueMail->getUid())),
                $query->equals('status', QueueRecipientUtility::STATUS_WAITING)
            )
        );

        if ($limit > 0) {
            $query->setLimit(intval($limit));
        }

        return $query->execute();
    }


    /**
     *  findOneByUidAndQueueMail
     *
     * @param int $uid
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient|null
     * comment: implicitly tested
     */
    public function findOneByUidAndQueueMail(
        int $uid,
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): ?QueueRecipient {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('uid', intval($uid)),
                $query->equals('queueMail', intval($queueMail->getUid()))
            )
        );

        return $query->execute()->getFirst();
    }

    /**
     * findByEmailAndQueueMail
     *
     * @param string $email
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient|null
     * comment: implicitly tested
     */
    public function findOneByEmailAndQueueMail(
        string $email,
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): ?QueueRecipient {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('email', $email),
                $query->equals('queueMail', $queueMail)
            )
        );

        return $query->execute()->getFirst();
    }


    /**
     * countTotalRecipientsByQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function countTotalRecipientsByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->greaterThanOrEqual('status', QueueRecipientUtility::STATUS_WAITING)
            )
        );

        return $query->execute()->count();
    }


    /**
     * countTotalSentByQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * comment: implicitly tested
     */
    public function countTotalSentByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->greaterThanOrEqual('status', QueueRecipientUtility::STATUS_FINISHED),
                $query->logicalNot(
                    $query->equals('status', QueueRecipientUtility::STATUS_DEFERRED)
                )
            )
        );

        return $query->execute()->count();
    }


    /**
     * countDeliveredByQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * comment: implicitly tested
     */
    public function countDeliveredByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_FINISHED)
            )
        );

        return $query->execute()->count();
    }


    /**
     * countFailedByQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * comment: implicitly tested
     */
    public function countFailedByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_ERROR)
            )
        );

        return $query->execute()->count();
    }


    /**
     * countDeferredByQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * comment: implicitly tested
     */
    public function countDeferredByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_DEFERRED)
            )
        );

        return $query->execute()->count();
    }


    /**
     * countBouncedByQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * comment: implicitly tested
     */
    public function countBouncedByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('queueMail', $queueMail),
                $query->equals('status', QueueRecipientUtility::STATUS_BOUNCED)
            )
        );

        return $query->execute()->count();
    }


    /**
     * findAllLastBounced
     *
     * @param int $limit
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @todo rework
     * @todo write tests
     */
    public function findAllLastBounced(int $limit = 100): QueryResultInterface
    {

        $query = $this->createQuery();
        $query->statement('
            SELECT tx_postmaster_domain_model_queuerecipient.* FROM tx_postmaster_domain_model_queuerecipient
            LEFT JOIN tx_postmaster_domain_model_queuemail
                ON tx_postmaster_domain_model_queuerecipient.queue_mail = tx_postmaster_domain_model_queuemail.uid
            LEFT JOIN tx_postmaster_domain_model_bouncemail
                ON tx_postmaster_domain_model_bouncemail.email = tx_postmaster_domain_model_queuerecipient.email
                AND tx_postmaster_domain_model_bouncemail.crdate > tx_postmaster_domain_model_queuerecipient.crdate
                AND tx_postmaster_domain_model_bouncemail.status = 0
            WHERE tx_postmaster_domain_model_bouncemail.type = "hard"
            AND tx_postmaster_domain_model_queuerecipient.status = 4
            AND tx_postmaster_domain_model_queuemail.status IN (3,4)
            AND tx_postmaster_domain_model_queuemail.type > 0
            AND tx_postmaster_domain_model_queuerecipient.tstamp = (
                SELECT MAX(recipient_sub.tstamp) FROM tx_postmaster_domain_model_queuerecipient as recipient_sub WHERE
                recipient_sub.status = 4 AND
                recipient_sub.email = tx_postmaster_domain_model_queuerecipient.email
            )
            ORDER BY tx_postmaster_domain_model_queuerecipient.uid
            LIMIT ' . intval ($limit) . '
        ');

        return $query->execute();
    }


    /**
     * deleteByQueueMail
     * We use a straight-forward approach here because it may be a lot of data to delete!
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     * comment: implicitly tested
     */
    public function deleteByQueueMail(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('tx_postmaster_domain_model_queuerecipient');

        return $queryBuilder
            ->delete('tx_postmaster_domain_model_queuerecipient')
            ->where(
                $queryBuilder->expr()->eq(
                    'queue_mail',
                    $queryBuilder->createNamedParameter($queueMail->getUid(), \PDO::PARAM_INT))
            )
            ->execute();
    }

}
