<?php
namespace Madj2k\Postmaster\Persistence;

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

use Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * A class to cleanup the database
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @api
 */
class Cleaner
{

    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected QueueMailRepository $queueMailRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected QueueRecipientRepository $queueRecipientRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected OpeningStatisticsRepository $openingStatisticsRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ClickStatisticsRepository $clickStatisticsRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected MailingStatisticsRepository $mailingStatisticsRepository;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * cleanup
     *
     * @param int $daysAfterSendingFinished  Defines how many days after its sending has been finished an queueMail will be
     *     deleted (default: 30 days)
     * @param array $types Defines which types of mails the cleanup should look for (Default: only type "0")
     * @param bool $includingStatistics
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function cleanup(
        int $daysAfterSendingFinished = 30,
        array $types = [],
        bool $includingStatistics = false
    ): bool {

        // check if migration of statistics is done completely
        if (count($this->queueMailRepository->findByMissingMailingStatistics())) {
            $this->getLogger()->log(
                LogLevel::WARNING,
                'Statistic migration not yet complete. Please check if the ' .
                 'cronjob for the statistic analysis is activated. Aborting cleanup.'
            );
            return false;
        }

        // do cleanup
        if (
            ($queueMails = $this->queueMailRepository->findByTstampFinishedSendingAndTypes(
                $daysAfterSendingFinished,
                $types
            ))
            && (count($queueMails))
        ) {

            /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
            foreach ($queueMails as $queueMail) {

                if ($includingStatistics) {
                    $this->deleteStatistics($queueMail);
                }
                $this->deleteQueueRecipients($queueMail);
                $this->deleteQueueMail($queueMail);

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Cleanup for queueMail with uid %s finished successfully.',
                        $queueMail->getUid()
                    )
                );
            }

            return true;
        }

        return false;
    }


    /**
     * delete queueMail by queueMail-object
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     */
    public function deleteQueueMail (
         \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $result = $this->queueMailRepository->deleteByQueueMail($queueMail);

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Deleted queueMail with uid %s.',
                $queueMail->getUid()
            )
        );

        return $result;
    }


    /**
     * delete queueRecipients by queueMail-object
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return int
     */
    public function deleteQueueRecipients(
         \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
    ): int {

        $result = $this->queueRecipientRepository->deleteByQueueMail($queueMail);

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Deleted %s queueRecipients of queueMail with uid %s.',
                $result,
                $queueMail->getUid()
            )
        );

        return $result;
    }


    /**
     * delete statistics by queueMail-object
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @param bool $keepMailingStatistics
     * @return int
     */
    public function deleteStatistics(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail,
        bool $keepMailingStatistics = false
    ): int {

        $result = 0;
        if (! $keepMailingStatistics) {
            $result += $this->mailingStatisticsRepository->deleteByQueueMail($queueMail);
        }
        $result += $this->openingStatisticsRepository->deleteByQueueMail($queueMail);
        $result += $this->clickStatisticsRepository->deleteByQueueMail($queueMail);

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Deleted %s statistic-datasets of queueMail with uid %s.',
                $result,
                $queueMail->getUid()
            )
        );

        return $result;
    }


    /**
     * Returns logger instance
     *
     * @return Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
