<?php
namespace Madj2k\Postmaster\Statistics;

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

use Madj2k\Postmaster\Domain\Model\MailingStatistics;
use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * MailingStatisticsAnalyser
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailingStatisticsAnalyser
{

    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected PersistenceManager $persistenceManager;


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
     * @var \Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected MailingStatisticsRepository $mailingStatisticsRepository;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * analyse
     *
     * @param int $daysAfterSendingFinished Defines how many days after sending has been started the statistics should be updated (default: 30 days)
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function analyse (int $daysAfterSendingFinished = 30): void
    {

        // migrate statistics
        if ($queueMailsMigrate = $this->queueMailRepository->findByMissingMailingStatistics()) {

            /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
            foreach ($queueMailsMigrate as $queueMail) {
                $this->analyseQueueMail($queueMail);

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Statistic migration for queueMail with uid %s finished successfully.',
                        $queueMail->getUid()
                    )
                );
            }
        } else {
            $this->getLogger()->log(
                LogLevel::DEBUG,
                'No statistic migration needed.'
            );
        }

        // now process statistics according to given time
        $queueMails = $this->queueMailRepository->findByTstampRealSending($daysAfterSendingFinished);
        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        foreach ($queueMails as $queueMail) {
            $this->analyseQueueMail($queueMail);

            $this->getLogger()->log(
                LogLevel::DEBUG,
                sprintf(
                    'Statistical analysis for queueMail with uid %s finished successfully.',
                    $queueMail->getUid()
                )
            );
        }

    }


    /**
     * analyseQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function analyseQueueMail (QueueMail $queueMail): void
    {

        // add statistics-object if not yet existent
        if (! $mailingStatistics = $queueMail->getMailingStatistics()) {

            /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
            $mailingStatistics = GeneralUtility::makeInstance(MailingStatistics::class);
            $mailingStatistics->setQueueMail($queueMail);

            // migration
            $mailingStatistics->setTstampFavSending($queueMail->getTstampFavSending());
            $mailingStatistics->setTstampRealSending($queueMail->getTstampRealSending());
            $mailingStatistics->setTstampFinishedSending($queueMail->getTstampSendFinish());
            $mailingStatistics->setSubject($queueMail->getSubject());
            $mailingStatistics->setType($queueMail->getType());

            $queueMail->setMailingStatistics($mailingStatistics);
        }

        // set current values
        $mailingStatistics->setStatus($queueMail->getStatus());

        $mailingStatistics->setTotalRecipients(
            $this->queueRecipientRepository->countTotalRecipientsByQueueMail($queueMail)
        );
        $mailingStatistics->setTotalSent(
            $this->queueRecipientRepository->countTotalSentByQueueMail($queueMail)
        );
        $mailingStatistics->setDelivered(
            $this->queueRecipientRepository->countDeliveredByQueueMail($queueMail)
        );
        $mailingStatistics->setFailed(
            $this->queueRecipientRepository->countFailedByQueueMail($queueMail)
        );
        $mailingStatistics->setDeferred(
            $this->queueRecipientRepository->countDeferredByQueueMail($queueMail)
        );
        $mailingStatistics->setBounced(
            $this->queueRecipientRepository->countBouncedByQueueMail($queueMail)
        );

        // persist changes
        $this->queueMailRepository->update($queueMail);
        if ($mailingStatistics->_isNew()) {
            $this->mailingStatisticsRepository->add($mailingStatistics);
        } else {
            $this->mailingStatisticsRepository->update($mailingStatistics);
        }

        $this->persistenceManager->persistAll();

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {

        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }


}
