<?php

namespace Madj2k\Postmaster\Controller;

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

use Madj2k\CoreExtended\Transfer\CsvExporter;
use Madj2k\CoreExtended\Utility\CsvUtility;
use Madj2k\CoreExtended\Utility\GeneralUtility;
use Madj2k\Postmaster\Domain\Repository\BounceMailRepository;
use Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use Madj2k\Postmaster\Persistence\Cleaner;
use Madj2k\Postmaster\Utility\TimePeriodUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * BackendController
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BackendController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected MailingStatisticsRepository $mailingStatisticsRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ClickStatisticsRepository $clickStatisticsRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\BounceMailRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected BounceMailRepository $bounceMailRepository;


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
     * @var \Madj2k\Postmaster\Persistence\Cleaner
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected Cleaner $cleaner;


    /**
     * Shows statistics
     *
     * @param int $timeFrame
     * @param int $mailType
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function statisticsAction(int $timeFrame = 0, int $mailType = -1)
    {

        $period = TimePeriodUtility::getTimePeriod($timeFrame);
        $mailingStatisticsList = $this->mailingStatisticsRepository->findByTstampFavSendingAndType(
            $period['from'],
            $period['to'],
            $mailType
        );

        $mailTypeList = [];
        if (is_array($this->settings['types'])) {
            foreach ($this->settings['types'] as $key => $value)
                $mailTypeList[$key] = ucFirst($value);
        }
        asort($mailTypeList);


        $this->view->assignMultiple(
            [
                'mailingStatisticsList' => $mailingStatisticsList,
                'timeFrame' => $timeFrame,
                'mailTypeList' => $mailTypeList,
                'mailType' => $mailType,
            ]
        );
    }


    /**
     * Shows clickStatistics
     *
     * @param int $queueMailUid
     * @return void
     */
    public function clickStatisticsAction(int $queueMailUid)
    {
        $this->view->assignMultiple(
            array(
                'clickedLinks' => $this->clickStatisticsRepository->findByQueueMailUid($queueMailUid),
                'queueMailStatistics' => $this->mailingStatisticsRepository->findOneByQueueMailUid($queueMailUid),
            )
        );
    }


    /**
     * Lists all e-mails in queue
     *
     * @param int $timeFrame
     * @param int $mailType
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\InvalidQueryException
     */
    public function listAction(int $timeFrame = 0, int $mailType = -1)
    {

        $period = TimePeriodUtility::getTimePeriod($timeFrame);
        $queueMailList = $this->queueMailRepository->findByTstampFavSendingAndType(
            $period['from'],
            $period['to'],
            $mailType
        );

        $mailTypeList = [];
        if (is_array($this->settings['types'])) {
            foreach ($this->settings['types'] as $key => $value)
                $mailTypeList[$key] = ucFirst($value);
        }
        asort($mailTypeList);

        $this->view->assignMultiple(
            array(
                'queueMailList'  => $queueMailList,
                'timeFrame'    => $timeFrame,
                'mailTypeList' => $mailTypeList,
                'mailType'     => $mailType,
            )
        );
    }


    /**
     * Downloads bounced adresses
     *
     * @param int $queueMailUid
     * @return void
     * @throws \Madj2k\CoreExtended\Exception
     */
    public function downloadBouncedAction(int $queueMailUid)
    {
        $bouncedMails = $this->bounceMailRepository->findByQueueMailUid($queueMailUid);

        /** @var \Madj2k\CoreExtended\Transfer\CsvExporter $dataMapper */
        $csvExporter = $this->objectManager->get(CsvExporter::class);
        $csvExporter->export($bouncedMails,'', ';', ['body', 'bodyFull']);

        exit();
    }


    /**
     * Pauses given queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function pauseAction(\Madj2k\Postmaster\Domain\Model\QueueMail $queueMail)
    {
        $queueMail->setStatus(1);
        $this->queueMailRepository->update($queueMail);

        $this->redirect('list');
    }


    /**
     * Continues given queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException*
     */
    public function continueAction(\Madj2k\Postmaster\Domain\Model\QueueMail $queueMail)
    {
        $queueMail->setStatus(2);
        $this->queueMailRepository->update($queueMail);

        $this->redirect('list');
    }


    /**
     * Resets given queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function resetAction(\Madj2k\Postmaster\Domain\Model\QueueMail $queueMail)
    {

        // set mail-values
        $queueMail->setStatus(2);
        if ($mailingStatistics = $queueMail->getMailingStatistics()) {
            $mailingStatistics->setTstampRealSending(0);
            $mailingStatistics->setTstampFinishedSending(0);
            $this->mailingStatisticsRepository->update($mailingStatistics);
        }
        $this->queueMailRepository->update($queueMail);

        // reset status of all recipients
        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $recipient */
        foreach ($this->queueRecipientRepository->findByQueueMail($queueMail) as $recipient) {
            $recipient->setStatus(2);
            $this->queueRecipientRepository->update($recipient);
        }

        // reset statistics by queueMail
        $this->cleaner->deleteStatistics($queueMail, true);

        $this->redirect('list');
    }


    /**
     * Deletes given queueMail and it's corresponding data
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\UnsupportedRequestTypeException
     * @throws \TYPO3\CMS\Extbase\Mvc\Exception\StopActionException
     */
    public function deleteAction(\Madj2k\Postmaster\Domain\Model\QueueMail $queueMail)
    {
        $this->cleaner->deleteStatistics($queueMail);
        $this->cleaner->deleteQueueRecipients($queueMail);
        $this->cleaner->deleteQueueMail($queueMail);

        $this->redirect('list');
    }

}
