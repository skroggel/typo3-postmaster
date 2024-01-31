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
 * Link
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated This class is only kept for backwards compatibility with old mails
 */
class Link extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    protected ?QueueMail $queueMail = null;


    /**
     * @var int
     */
    protected int $crdate = 0;


    /**
     * @var string
     */
    protected string $hash = '';


    /**
     * @var string
     */
    protected string $url = '';


    /**
     * Returns the queueMail
     *
     * @return \Madj2k\Postmaster\Domain\Model\QueueMail
     */
    public function getQueueMail(): ?QueueMail
    {
        return $this->queueMail;
    }


    /**
     * Sets the queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     */
    public function setQueueMail(QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
    }


    /**
     * Returns the crdate
     *
     * @return int
     */
    public function getCrdate(): int
    {
        return $this->crdate;
    }


    /**
     * Sets the crdate
     *
     * @param int $crdate
     * @return void
     */
    public function setCrdate(int $crdate): void
    {
        $this->crdate = $crdate;
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

}
