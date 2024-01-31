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
 * BounceMail
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BounceMail extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var string
     */
    protected string $status = '';


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
    protected string $type = '';


    /**
     * @var string
     */
    protected string $email = '';


    /**
     * @var string
     */
    protected string $subject = '';


    /**
     * @var int
     */
    protected int $ruleNumber = 0;


    /**
     * @var string
     */
    protected string $ruleCategory = '';


    /**
     * @var string
     */
    protected string $header = '';


    /**
     * @var string
     */
    protected string $body = '';


    /**
     * @var string
     */
    protected string $headerFull = '';


    /**
     * @var string
     */
    protected string $bodyFull = '';


    /**
     * Returns the status
     *
     * @return string $status
     */
    public function getStatus(): string
    {
        return $this->status;
    }


    /**
     * Sets the status
     *
     * @param string $status
     * @return void
     */
    public function setStatus(string $status)
    {
        $this->status = $status;
    }


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
     * Returns the type
     *
     * @return string $type
     */
    public function getType(): string
    {
        return $this->type;
    }


    /**
     * Sets the type
     *
     * @param string $type
     * @return void
     */
    public function setType(string $type)
    {
        $this->type = $type;
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
    public function setEmail(string $email)
    {
        $this->email = EmailValidator::cleanUpEmail($email);
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
    public function setSubject(string $subject)
    {
        $this->subject = $subject;
    }


    /**
     * Returns the ruleNumber
     *
     * @return int $ruleNumber
     */
    public function getRuleNumber(): int
    {
        return $this->ruleNumber;
    }


    /**
     * Sets the ruleNumber
     *
     * @param int $ruleNumber
     * @return void
     */
    public function setRuleNumber(int $ruleNumber)
    {
        $this->ruleNumber = $ruleNumber;
    }


    /**
     * Returns the ruleCategory
     *
     * @return string $ruleCategory
     */
    public function getRuleCategory(): string
    {
        return $this->ruleCategory;
    }


    /**
     * Sets the ruleCategory
     *
     * @param string $ruleCategory
     * @return void
     */
    public function setRuleCategory(string $ruleCategory)
    {
        $this->ruleCategory = $ruleCategory;
    }


    /**
     * Returns the header
     *
     * @return array $header
     */
    public function getHeader(): array
    {
        $unserializeResult = unserialize($this->header);
        return is_array($unserializeResult) ? $unserializeResult  : [];
    }


    /**
     * Sets the header
     *
     * @param array $header
     * @return void
     */
    public function setHeader(array $header)
    {
        $this->header = serialize($header);
    }


    /**
     * Returns the body
     *
     * @return string $body
     */
    public function getBody(): string
    {
        return $this->body;
    }


    /**
     * Sets the body
     *
     * @param string $body
     * @return void
     */
    public function setBody(string $body)
    {
        $this->body = $body;
    }


    /**
     * Returns the headerFull
     *
     * @return string $headerFull
     */
    public function getHeaderFull(): string
    {
        return $this->headerFull;
    }


    /**
     * Sets the headerFull
     *
     * @param string $headerFull
     * @return void
     */
    public function setHeaderFull(string $headerFull)
    {
        $this->headerFull = $headerFull;
    }


    /**
     * Returns the bodyFull
     *
     * @return string $bodyFull
     */
    public function getBodyFull(): string
    {
        return $this->bodyFull;
    }


    /**
     * Sets the bodyFull
     *
     * @param string $bodyFull
     * @return void
     */
    public function setBodyFull(string $bodyFull)
    {
        $this->bodyFull = $bodyFull;
    }

}
