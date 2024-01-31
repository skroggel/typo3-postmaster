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

use Madj2k\Postmaster\Domain\Model\OpeningStatistics;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;
use TYPO3\CMS\Extbase\Persistence\QueryInterface;

/**
 * OpeningStatisticsRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OpeningStatisticsRepository extends AbstractRepository
{

    /** @var array $defaultOrderings */
    protected $defaultOrderings = [
        'counter' => QueryInterface::ORDER_DESCENDING,
    ];


    /**
     * initializeObject
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
     * findOneByHashAndQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\queueMail $queueMail
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @return \Madj2k\Postmaster\Domain\Model\OpeningStatistics
     * comment: implicitly tested
     */
    public function findOneByHashAndQueueMail(
        string $hash,
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): ?OpeningStatistics {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('hash', $hash),
                $query->equals('queueMail', $queueMail)
            )
        );

        return $query->execute()->getFirst();
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
            ->getQueryBuilderForTable('tx_postmaster_domain_model_openingstatistics');

        return $queryBuilder
            ->delete('tx_postmaster_domain_model_openingstatistics')
            ->where(
                $queryBuilder->expr()->eq(
                    'queue_mail',
                    $queryBuilder->createNamedParameter($queueMail->getUid(), \PDO::PARAM_INT))
            )
            ->execute();
    }
}
