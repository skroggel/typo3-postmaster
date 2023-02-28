<?php

namespace Madj2k\Postmaster\Cache;

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
 * MailCache
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailCache extends AbstractCache
{

    /**
     * Returns the plaintextBody
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @return string $plaintextBody
     * @throws \Madj2k\Postmaster\Exception
     */
    public function getPlaintextBody(
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
    ): string {

        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'plaintext');
        return $this->getContent($cacheIdentifier);
    }


    /**
     * Sets the plaintextBody
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @param string $plaintextBody
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     */
    public function setPlaintextBody(
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient,
        string $plaintextBody
    ): void {

        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'plaintext');
        $this->setContent($cacheIdentifier, $plaintextBody);
    }


    /**
     * Returns the htmlBody
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @return string $htmlBody
     * @throws \Madj2k\Postmaster\Exception
     */
    public function getHtmlBody(\Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient): string
    {
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'html');
        return $this->getContent($cacheIdentifier);
    }


    /**
     * Sets the htmlBody
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @param string                                     $htmlBody
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     */
    public function setHtmlBody(
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient,
        string $htmlBody): void {

        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'html');
        $this->setContent($cacheIdentifier, $htmlBody);
    }


    /**
     * Returns the calendarBody
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @return string $calendarBody
     * @throws \Madj2k\Postmaster\Exception
     */
    public function getCalendarBody(\Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient): string
    {
        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'calendar');
        return $this->getContent($cacheIdentifier);
    }


    /**
     * Sets the calendarBody
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @param string $calendarBody
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     */
    public function setCalendarBody(
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient,
        string $calendarBody
    ): void {

        $cacheIdentifier = $this->getIdentifier($queueRecipient, 'calendar');
        $this->setContent($cacheIdentifier, $calendarBody);
    }



    /**
     * Returns cacheIdentifier
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @param string $property
     * @return string
     * @throws \Madj2k\Postmaster\Exception
     */
    public function getIdentifier(
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient,
        string $property
    ) : string {

        if ($queueRecipient->_isNew()) {
            throw new \Madj2k\Postmaster\Exception (
                'The queueRecipient-object has to be persisted before it can be used.',
                1634308452
            );
        }

        return 'MailCache_' . intval($queueRecipient->getUid()) . '_' . $property;
    }
}
