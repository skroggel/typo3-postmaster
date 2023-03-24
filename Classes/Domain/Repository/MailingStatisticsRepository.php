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

use Madj2k\Postmaster\Utility\QueueMailUtility;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * MailingStatisticsRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailingStatisticsRepository extends AbstractRepository
{

    /**
     * initializeObject
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
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
        $constraints = [
            $query->greaterThanOrEqual('status', QueueMailUtility::STATUS_SENDING)
        ];

        if ($type > -1) {
            $constraints[] = $query->equals('type', $type);
        }

        if ($fromTime) {
            $constraints[] = $query->greaterThanOrEqual('tstampFavSending', $fromTime);
        }

        if ($toTime) {
            $constraints[] = $query->lessThanOrEqual('tstampFavSending', $toTime);
        }

        $query->matching(
            $query->logicalAnd($constraints)
        );

        $query->setOrderings(
            array(
                'status' => QueryInterface::ORDER_ASCENDING,
                'tstampFavSending' => QueryInterface::ORDER_DESCENDING,
                'tstampRealSending' => QueryInterface::ORDER_DESCENDING,
            )
        );

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
            ->getQueryBuilderForTable('tx_postmaster_domain_model_mailingstatistics');

        return $queryBuilder
            ->delete('tx_postmaster_domain_model_mailingstatistics')
            ->where(
                $queryBuilder->expr()->eq(
                    'queue_mail',
                    $queryBuilder->createNamedParameter($queueMail->getUid(), \PDO::PARAM_INT))
            )
            ->execute();

    }
}
