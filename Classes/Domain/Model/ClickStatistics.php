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
 * ClickStatistics
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClickStatistics extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
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
    protected string $hash = '';


    /**
     * @var string
     */
    protected string $url = '';


    /**
     * @var int
     */
    protected int $counter = 0;


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
     * Returns the hash
     *
     * @return string $hash
     */
    public function getHash(): string
    {
        return $this->hash;
    }

    /**
     * Sets the hash
     *
     * @param string $hash
     * @return void
     */
    public function setHash(string $hash): void
    {
        $this->hash = $hash;
    }


    /**
     * Returns the url
     *
     * @return string $url
     */
    public function getUrl(): string
    {
        return $this->url;
    }


    /**
     * Sets the url
     *
     * @param string $url
     * @return void
     */
    public function setUrl(string $url): void
    {
        $this->url = $url;
    }


    /**
     * Returns the counter
     *
     * @return int $counter
     */
    public function getCounter(): int
    {
        return $this->counter;
    }


    /**
     * Sets the counter
     *
     * @param int $counter
     * @return void
     */
    public function setCounter(int $counter): void
    {
        $this->counter = $counter;
    }
}
