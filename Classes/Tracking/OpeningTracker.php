<?php

namespace Madj2k\Postmaster\Tracking;

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
use Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use Madj2k\Postmaster\Utility\StatisticsUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * OpeningTracker
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OpeningTracker
{

    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository|null
     */
    protected ?QueueMailRepository $queueMailRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository|null
     */
    protected ?QueueRecipientRepository $queueRecipientRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository|null
     */
    protected ?OpeningStatisticsRepository $openingStatisticsRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    protected ?PersistenceManager $persistenceManager = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * @param \Madj2k\Postmaster\Domain\Repository\QueueMailRepository $queueMailRepository
     * @param \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository $queueRecipientRepository
     * @param \Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository $openingStatisticsRepository
     * @param \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager $persistenceManager
     */
    public function __construct(
        QueueMailRepository $queueMailRepository,
        QueueRecipientRepository $queueRecipientRepository,
        OpeningStatisticsRepository $openingStatisticsRepository,
        PersistenceManager $persistenceManager
    ) {
        $this->queueMailRepository = $queueMailRepository;
        $this->queueRecipientRepository = $queueRecipientRepository;
        $this->openingStatisticsRepository = $openingStatisticsRepository;
        $this->persistenceManager = $persistenceManager;
    }


    /**
     * Tracks the opening of the email
     *
     * @param int $queueMailId
     * @param int $queueMailRecipientId
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \Madj2k\Postmaster\Exception
     */
    public function track(
        int $queueMailId = 0,
        int $queueMailRecipientId = 0
    ): bool {

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        if (
            ($queueMailId)
            && ($queueMailRecipientId)
            && ($queueMail = $this->queueMailRepository->findByUid($queueMailId))
            && ($queueRecipient = $this->queueRecipientRepository->findByUid($queueMailRecipientId))
        ) {

            $this->persistTrackingData($queueMail, $queueRecipient);
            return true;
        }

        return false;
    }


    /**
     * Persists tracking-data to database
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \Madj2k\Postmaster\Exception
     */
    protected function persistTrackingData (
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail,
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
    ): void {

        // generate hash from recipient
        $hash = StatisticsUtility::generateRecipientHash($queueRecipient);

        // check if this hash-value already exists for this queueMail
        if ($openingStatistic = $this->openingStatisticsRepository->findOneByHashAndQueueMail($hash, $queueMail)) {

            $openingStatistic->setCounter($openingStatistic->getCounter() +1);
            $this->openingStatisticsRepository->update($openingStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Updating openingStatistic with uid=%s for queueRecipient with uid %s and queueMail with uid %s.',
                    $openingStatistic->getUid(),
                    $queueRecipient->getUid(),
                    $queueMail->getUid()
                )
            );

        } else {

            /** @var \Madj2k\Postmaster\Domain\Model\OpeningStatistics $openingStatistic */
            $openingStatistic = GeneralUtility::makeInstance(OpeningStatistics::class);
            $openingStatistic->setQueueMail($queueMail);
            $openingStatistic->setQueueRecipient($queueRecipient);
            $openingStatistic->setHash($hash);
            $openingStatistic->setCounter(1);

            $this->openingStatisticsRepository->add($openingStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Adding new openingStatistic for queueRecipient with uid %s and queueMail with uid %s.',
                    $queueRecipient->getUid(),
                    $queueMail->getUid()
                )
            );
        }
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
