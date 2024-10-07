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

use Madj2k\Postmaster\Domain\Model\ClickStatistics;
use Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\LinkRepository;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use Madj2k\Postmaster\Utility\StatisticsUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * ClickTracker
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClickTracker
{

    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository
     */
    protected ?QueueMailRepository $queueMailRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository
     */
    protected ?QueueRecipientRepository $queueRecipientRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\LinkRepository
     */
    protected ?LinkRepository $linkRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository
     */
    protected ?ClickStatisticsRepository $clickStatisticsRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     */
    protected ?PersistenceManager $persistenceManager = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * @param QueueMailRepository       $queueMailRepository
     * @param QueueRecipientRepository  $queueRecipientRepository
     * @param LinkRepository            $linkRepository
     * @param ClickStatisticsRepository $clickStatisticsRepository
     * @param PersistenceManager        $persistenceManager
     */
    public function __construct(
        QueueMailRepository $queueMailRepository,
        QueueRecipientRepository $queueRecipientRepository,
        LinkRepository $linkRepository,
        ClickStatisticsRepository $clickStatisticsRepository,
        PersistenceManager $persistenceManager
    ) {
        $this->queueMailRepository = $queueMailRepository;
        $this->queueRecipientRepository = $queueRecipientRepository;
        $this->linkRepository = $linkRepository;
        $this->clickStatisticsRepository = $clickStatisticsRepository;
        $this->persistenceManager = $persistenceManager;
    }


    /**
     * Tracks the opening of a link
     *
     * @param int $queueMailId
     * @param string $string
     * @return bool
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function track(
        int $queueMailId = 0,
        string $string = ''
    ): bool {

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        if (
            ($queueMailId)
            && ($queueMail = $this->queueMailRepository->findByUid($queueMailId))
            && ($string)
        ) {
            $this->persistTrackingData($queueMail, $string);
            return true;
        }

        return false;
    }


    /**
     * Get the redirect url with all relevant parameters
     *
     * @param string $url
     * @param int $queueMailId
     * @param int $queueMailRecipientId
     * @return string
     */
    public function getRedirectUrl (
        string $url,
        int $queueMailId = 0,
        int $queueMailRecipientId = 0
    ): string {

        // decode url (just to be sure)
        $url = urldecode($url);

        // additional params
        $additionalParams = [];

        // check for queueMail
        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        if ($queueMail = $this->queueMailRepository->findByUid($queueMailId)) {

            // set queueMail as additional param
            $additionalParams[] = 'tx_postmaster[mid]=' . $queueMail->getUid();

            // check additionally for corresponding queueRecipient
            /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueMailRecipient */
            if (
                ($queueMailRecipientId)
                && ($queueMailRecipient = $this->queueRecipientRepository->findOneByUidAndQueueMail($queueMailRecipientId, $queueMail))
            ) {
                $additionalParams[] = 'tx_postmaster[uid]=' . $queueMailRecipient->getUid();
            }
        }

        return StatisticsUtility::addParamsToUrl($url, $additionalParams);
    }



    /**
     * Get getUrl by Hash
     *
     * @param string $hash
     * @return string
     * @deprecated
     */
    public function getPlainUrlByHash(string $hash): string {

        /** @var \Madj2k\Postmaster\Domain\Model\Link $link */
        if ($link = $this->linkRepository->findOneByHash($hash)) {
            return $link->getUrl();
        }

        return '';
    }


    /**
     * Persists tracking-data to database
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @param string $url
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    protected function persistTrackingData (
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail,
        string $url
    ): void {

        // decode url (just to be sure)
        $url = urldecode($url);

        // generate hash from url
        $hash = StatisticsUtility::generateLinkHash($url);

        // check if this hash-value already exists for this queueMail
        if ($clickStatistic = $this->clickStatisticsRepository->findOneByHashAndQueueMail($hash, $queueMail)) {

            $clickStatistic->setCounter($clickStatistic->getCounter() +1);
            $this->clickStatisticsRepository->update($clickStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Updating clickStatistic with uid %s for url %s of queueMail with uid %s.',
                    $clickStatistic->getUid(),
                    $url,
                    $queueMail->getUid()
                )
            );

        } else {

            /** @var \Madj2k\Postmaster\Domain\Model\ClickStatistics $clickStatistic */
            $clickStatistic = GeneralUtility::makeInstance(ClickStatistics::class);
            $clickStatistic->setQueueMail($queueMail);
            $clickStatistic->setHash($hash);
            $clickStatistic->setUrl($url);
            $clickStatistic->setCounter(1);

            $this->clickStatisticsRepository->add($clickStatistic);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Adding new clickStatistic for url %s of queueMail with uid %s.',
                    $url,
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
