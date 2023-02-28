<?php

namespace Madj2k\Postmaster\Domain\Model;

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

/**
 * MailingStatistics
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailingStatistics extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    protected ?QueueMail $queueMail = null;


    /**
     * @var int
     */
    protected int $queueMailUid = 0;


    /**
     * @var string
     */
    protected string $subject = '';


    /**
     * @var int
     */
    protected int $status = 0;


    /**
     * @var int
     */
    protected int $type = 0;


    /**
     * @var int
     */
    protected int $totalRecipients = 0;


    /**
     * @var int
     */
    protected int $totalSent = 0;


    /**
     * @var int
     */
    protected int $delivered = 0;


    /**
     * @var int
     */
    protected int $failed = 0;


    /**
     * @var int
     */
    protected int $deferred = 0;


    /**
     * @var int
     */
    protected int $bounced = 0;


    /**
     * @var int
     */
    protected int $tstampFavSending = 0;


    /**
     * @var int
     */
    protected int $tstampRealSending = 0;


    /**
     * @var int
     */
    protected int $tstampFinishedSending = 0;


    /**
     * Returns the queueMail
     *
     * @return \Madj2k\Postmaster\Domain\Model\QueueMail
     */
    public function getQueueMail():? QueueMail
    {
        return $this->queueMail;
    }


    /**
     * Sets the queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail(\Madj2k\Postmaster\Domain\Model\QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
        $this->setQueueMailUid($queueMail->getUid());
    }


    /**
     * Returns the queueMailUid
     *
     * @return int
     */
    public function getQueueMailUid(): int
    {
        return $this->queueMailUid;
    }


    /**
     * Sets the queueMail
     *
     * @param int $queueMailUid
     * @return void
     */
    public function setQueueMailUid(int $queueMailUid): void
    {
        $this->queueMailUid = $queueMailUid;
    }


    /**
     * Returns the subject
     *
     * @return string $subject
     */
    public function getSubject(): string
    {
        return $this->subject;
    }


    /**
     * Sets the subject
     *
     * @param string $subject
     * @return void
     */
    public function setSubject(string $subject): void
    {
        $this->subject = $subject;
    }


    /**
     * Returns the status
     *
     * @return int $status
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Sets the status
     *
     * @param int $status
     * @return void
     */
    public function setStatus(int $status): void
    {
        $this->status = $status;
    }


    /**
     * Returns the type
     *
     * @return int $type
     */
    public function getType(): int
    {
        return $this->type;
    }

    /**
     * Sets the type
     *
     * @param int $type
     * @return void
     */
    public function setType(int $type): void
    {
        $this->type = $type;
    }


    /**
     * Returns the totalRecipients
     *
     * @return int $totalRecipients
     */
    public function getTotalRecipients(): int
    {
        return $this->totalRecipients;
    }


    /**
     * Sets the totalRecipients
     *
     * @param int $totalRecipients
     * @return void
     */
    public function setTotalRecipients(int $totalRecipients): void
    {
        $this->totalRecipients = $totalRecipients;
    }


    /**
     * Returns the totalSent
     *
     * @return int $totalSent
     */
    public function getTotalSent(): int
    {
        return $this->totalSent;
    }


    /**
     * Sets the totalSent
     *
     * @param int $totalSent
     * @return void
     */
    public function setTotalSent(int $totalSent): void
    {
        $this->totalSent = $totalSent;
    }


    /**
     * Returns the delivered
     *
     * @return int $delivered
     */
    public function getDelivered(): int
    {
        return $this->delivered;
    }


    /**
     * Sets the delivered
     *
     * @param int $delivered
     * @return void
     */
    public function setDelivered(int $delivered): void
    {
        $this->delivered = $delivered;
    }


    /**
     * Returns the failed
     *
     * @return int $failed
     */
    public function getFailed(): int
    {
        return $this->failed;
    }


    /**
     * Sets the failed
     *
     * @param int $failed
     * @return void
     */
    public function setFailed(int $failed): void
    {
        $this->failed = $failed;
    }


    /**
     * Returns the deferred
     *
     * @return int $deferred
     */
    public function getDeferred(): int
    {
        return $this->deferred;
    }


    /**
     * Sets the deferred
     *
     * @param int $deferred
     * @return void
     */
    public function setDeferred(int $deferred): void
    {
        $this->deferred = $deferred;
    }


    /**
     * Returns the bounced
     *
     * @return int $bounced
     */
    public function getBounced(): int
    {
        return $this->bounced;
    }


    /**
     * Sets the bounced
     *
     * @param int $bounced
     * @return void
     */
    public function setBounced(int $bounced): void
    {
        $this->bounced = $bounced;
    }


    /**
     * Returns the tstampFavSending
     *
     * @return int
     */
    public function getTstampFavSending(): int
    {
        return $this->tstampFavSending;
    }


    /**
     * Sets the tstampFavSending
     *
     * @param int $tstampFavSending
     * @return void
     */
    public function setTstampFavSending(int $tstampFavSending): void
    {
        $this->tstampFavSending = $tstampFavSending;
    }


    /**
     * Returns the tstampRealSending
     *
     * @return int $tstampRealSending
     */
    public function getTstampRealSending(): int
    {
        return $this->tstampRealSending;
    }


    /**
     * Sets the tstampRealSending
     *
     * @param int $tstampRealSending
     * @return void
     */
    public function setTstampRealSending(int $tstampRealSending): void
    {
        $this->tstampRealSending = $tstampRealSending;
    }


    /**
     * Returns the tstampFinishedSending
     *
     * @return int $tstampFinishedSending
     */
    public function getTstampFinishedSending(): int
    {
        return $this->tstampFinishedSending;
    }


    /**
     * Sets the tstampFinishedSending
     *
     * @param int $tstampFinishedSending
     * @return void
     */
    public function setTstampFinishedSending (int $tstampFinishedSending): void
    {
        $this->tstampFinishedSending = $tstampFinishedSending;
    }

}
