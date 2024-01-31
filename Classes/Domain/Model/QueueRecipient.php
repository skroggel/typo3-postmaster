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
use Madj2k\Postmaster\Validation\EmailValidator;

/**
 * QueueRecipient
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueRecipient extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    protected ?QueueMail $queueMail = null;


    /**
     * @var string
     */
    protected string $email = '';


    /**
     * @var string
     */
    protected string $title = '';


    /**
     * @var int
     */
    protected int $salutation = 99;


    /**
     * @var string
     */
    protected string $firstName = '';


    /**
     * @var string
     */
    protected string $lastName = '';


    /**
     * subject
     *
     * @var string
     */
    protected $subject = '';


    /**
     * @var string
     */
    protected string $marker = '';


    /**
     * @var array
     */
    protected array $markerUnserialized = [];


    /**
     * @var integer
     */
    protected int $status = 0;


    /**
     * @var string
     */
    protected string $languageCode = 'de';


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
    public function setQueueMail(\Madj2k\Postmaster\Domain\Model\QueueMail $queueMail): void
    {
        $this->queueMail = $queueMail;
    }


    /**
     * Returns the email
     *
     * @return string $email
     */
    public function getEmail(): string
    {
        return $this->email;
    }

    /**
     * Sets the email
     *
     * @param string $email
     * @return void
     */
    public function setEmail(string $email): void
    {
        $this->email = EmailValidator::cleanUpEmail($email);
    }

    /**
     * Returns the title
     *
     * @return string $title
     */
    public function getTitle(): string
    {
        return $this->title;
    }

    /**
     * Sets the title
     *
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->title = $title;
    }

    /**
     * Returns the salutation
     *
     * @return int $salutation
     */
    public function getSalutation(): int
    {
        return $this->salutation;
    }


    /**
     * Returns the salutation
     *
     * @return string $salutation
     */
    public function getSalutationText(): string
    {
        if ($this->getSalutation() < 99) {

            return \Madj2k\Postmaster\Utility\FrontendLocalizationUtility::translate(
                'tx_postmaster_domain_model_queuerecipient.salutation.I.' . $this->getSalutation(),
                'postmaster',
                array(),
                $this->getLanguageCode()
            );
        }

        return '';
    }


    /**
     * Sets the salutation
     *
     * @param int $salutation
     * @return void
     */
    public function setSalutation(int $salutation): void
    {
        $this->salutation = $salutation;
    }


    /**
     * Returns the firstName
     *
     * @return string $firstName
     */
    public function getFirstName(): string
    {
        return $this->firstName;
    }

    /**
     * Sets the firstName
     *
     * @param string $firstName
     * @return void
     */
    public function setFirstName(string $firstName): void
    {
        $this->firstName = $firstName;
    }

    /**
     * Returns the lastName
     *
     * @return string $lastName
     */
    public function getLastName(): string
    {
        return $this->lastName;
    }

    /**
     * Sets the lastName
     *
     * @param string $lastName
     * @return void
     */
    public function setLastName(string $lastName): void
    {
        $this->lastName = $lastName;
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
     * Returns the marker
     *
     * @return array $marker
     */
    public function getMarker(): array
    {
        if (
            $this->markerUnserialized
            && (is_array($this->markerUnserialized))
        ) {
            return $this->markerUnserialized;
        }

        if (
            $this->marker
            && ($unserialized = unserialize($this->marker))
            && (is_array($unserialized))
        ) {
            $this->markerUnserialized = $unserialized;
        }

        return ($this->markerUnserialized) ?: [];
    }


    /**
     * Sets the marker
     *
     * @param array $marker
     * @return void
     */
    public function setMarker(array $marker): void
    {
        $this->markerUnserialized = $marker;
        $this->marker = serialize($marker);
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
     * Returns the languageCode
     *
     * @return string $languageCode
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }


    /**
     * Sets the languageCode
     *
     * @param string $languageCode
     * @return void
     */
    public function setLanguageCode(string $languageCode): void
    {
        $this->languageCode = $languageCode;
    }


}
