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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * QueueMail
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMail extends \TYPO3\CMS\Extbase\DomainObject\AbstractEntity
{

    /**
     * @var int
     */
    protected int $crdate = 0;


    /**
     * @var int
     */
    protected int $sorting = 0;


    /**
     * @var int
     */
    protected int $status = 1;


    /**
     * @var int
     */
    protected int $type = 0;


    /**
     * @var bool
     */
    protected bool $pipeline = false;


    /**
     * @var string
     */
    protected string $fromName = '';


    /**
     * @var string
     */
    protected string $fromAddress = '';


    /**
     * @var string
     */
    protected string $replyToName = '';


    /**
     * @var string
     */
    protected string $replyToAddress = '';


    /**
     * @var string
     */
    protected string $returnPath = '';


    /**
     * @var string
     */
    protected string $subject = '';


    /**
     * @var string
     */
    protected string $bodyText = '';


    /**
     * @var string
     */
    protected string $attachmentPaths = '';


    /**
     * @var string
     * @deprecated
     */
    protected string $attachment = '';


    /**
     * @var string
     * @deprecated
     */
    protected string $attachmentType = '';


    /**
     * @var string
     * @deprecated
     */
    protected string $attachmentName = '';


    /**
     * @var string
     */
    protected string $plaintextTemplate = '';


    /**
     * @var string
     */
    protected string $htmlTemplate = '';


    /**
     * @var string
     */
    protected string $calendarTemplate = '';


    /**
     * @var string
     */
    protected string $templatePaths = '';


    /**
     * @var string
     */
    protected string $layoutPaths = '';


    /**
     * @var string
     */
    protected string $partialPaths = '';


    /**
     * @var string
     */
    protected string $category = '';


    /**
     * @var string
     */
    protected string $campaignParameter = '';


    /**
     * @var int
     */
    protected int $priority = 3;


    /**
     * @var int
     */
    protected int $settingsPid = 0;


    /**
     * @var array
     */
    protected array $settings = [];


    /**
     * @var \Madj2k\Postmaster\Domain\Model\MailingStatistics|null
     */
    protected ?MailingStatistics $mailingStatistics = null;


    /**
     * @var int
     * @deprecated
     */
    protected int $tstampFavSending = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $tstampRealSending = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $tstampSendFinish = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $total = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $sent = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $successful = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $failed = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $deferred = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $bounced = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $opened = 0;


    /**
     * @var int
     * @deprecated
     */
    protected int $clicked = 0;


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
     * Returns the sorting
     *
     * @return int
     */
    public function getSorting(): int
    {
        return $this->sorting;
    }


    /**
     * Sets the sorting
     *
     * @param int $sorting
     * @return void
     */
    public function setSorting(int $sorting): void
    {
        $this->sorting = $sorting;
    }


    /**
     * Returns the status
     *
     * @return int
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
     * @return int
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
     * Returns the pipeline
     *
     * @return bool
     */
    public function getPipeline(): bool
    {
        return $this->pipeline;
    }


    /**
     * Sets the pipeline
     *
     * @param bool $pipeline
     * @return void
     */
    public function setPipeline(bool $pipeline): void
    {
        $this->pipeline = $pipeline;
    }


    /**
     * Returns the fromName
     *
     * @return string
     */
    public function getFromName(): string
    {
        return $this->fromName;
    }


    /**
     * Sets the fromName
     *
     * @param string $fromName
     * @return void
     */
    public function setFromName(string $fromName): void
    {
        $this->fromName = $fromName;
    }


    /**
     * Returns the fromAddress
     *
     * @return string
     */
    public function getFromAddress(): string
    {
        return $this->fromAddress;
    }


    /**
     * Sets the fromAddress
     *
     * @param string $fromAddress
     * @return void
     */
    public function setFromAddress(string $fromAddress): void
    {
        $this->fromAddress = EmailValidator::cleanUpEmail($fromAddress);
    }


    /**
     * Returns the replyToName
     *
     * @return string
     */
    public function getReplyToName(): string
    {
        return $this->replyToName;
    }


    /**
     * Sets the replyToName
     *
     * @param string $replyToName
     * @return void
     */
    public function setReplyToName(string $replyToName): void
    {
        $this->replyToName = $replyToName;
    }


    /**
     * Returns the replyToAddress
     *
     * @return string
     */
    public function getReplyToAddress(): string
    {
        return $this->replyToAddress;
    }


    /**
     * Sets the replyToAddress
     *
     * @param string $replyToAddress
     * @return void
     */
    public function setReplyToAddress(string $replyToAddress): void
    {
        $this->replyToAddress = EmailValidator::cleanUpEmail($replyToAddress);
    }


    /**
     * Sets the replyAddress
     *
     * @param string $replyAddress
     * @return void
     * @deprecated This method is deprecated. Please use setReplyToAddress() instead.
     */
    public function setReplyAddress(string $replyAddress): void
    {
        trigger_error(
            __CLASS__ . ': This method is deprecated. Please use setReplyToAddress() instead.',
            E_USER_DEPRECATED
        );
        $this->setReplyToAddress($replyAddress);
    }


    /**
     * Returns the returnPath
     *
     * @return string
     */
    public function getReturnPath(): string
    {
        return $this->returnPath;
    }


    /**
     * Sets the returnPath
     *
     * @param string $returnPath
     * @return void
     */
    public function setReturnPath(string $returnPath): void
    {
        $this->returnPath = EmailValidator::cleanUpEmail($returnPath);
    }


    /**
     * Returns the subject
     *
     * @return string
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
     * Returns the bodyText
     *
     * @return string
     */
    public function getBodyText(): string
    {
        return $this->bodyText;
    }


    /**
     * Sets the bodyText
     *
     * @param string $bodyText
     * @return void
     */
    public function setBodyText(string $bodyText): void
    {
        $this->bodyText = $bodyText;
    }


    /**
     * Returns the attachmentPath
     *
     * @return array
     */
    public function getAttachmentPaths(): array
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->attachmentPaths, true);
        return $paths;
    }


    /**
     * Sets the attachmentPaths
     *
     * @param array $attachmentPaths
     * @return void
     */
    public function setAttachmentPaths (array $attachmentPaths): void
    {
        $this->attachmentPaths = implode(',', $attachmentPaths);
    }


    /**
     * Adds an attachmentPath
     *
     * @param string $attachmentPath
     * @return void
     */
    public function addAttachmentPath(string $attachmentPath): void
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->attachmentPaths);
        $paths[] = $attachmentPath;
        $this->attachmentPaths = implode(',', $paths);
    }


    /**
     * Adds attachmentPaths
     *
     * @param array $attachmentPaths
     * @return void
     */
    public function addAttachmentPaths(array $attachmentPaths): void
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->attachmentPaths, true);
        $this->attachmentPaths = implode(',', array_merge($paths, $attachmentPaths));

    }


    /**
     * Returns the attachment
     *
     * @return string
     * @deprecated use $this->getAttachmentPath() instead
     */
    public function getAttachment(): string
    {
        return $this->attachment;
    }


    /**
     * Sets the attachment
     *
     * @param string $attachment
     * @return void
     * @deprecated use $this->setAttachmentPath() instead
     */
    public function setAttachment(string $attachment): void
    {
        $this->attachment = $attachment;
    }


    /**
     * Returns the attachment
     *
     * @return int $attachment
     * @deprecated
     */
    public function getAttachmentType(): int
    {
        return $this->attachmentType;
    }


    /**
     * Sets the attachmentType
     *
     * @param int $attachmentType
     * @return void
     * @deprecated
     */
    public function setAttachmentType(int $attachmentType): int
    {
        $this->attachmentType = $attachmentType;
    }


    /**
     * Returns the attachmentName
     *
     * @return string $attachmentName
     * @deprecated
     */
    public function getAttachmentName(): string
    {
        return $this->attachmentName;
    }


    /**
     * Sets the attachmentName
     *
     * @param string $attachmentName
     * @return void
     * @deprecated
     */
    public function setAttachmentName(string $attachmentName): void
    {
        $this->attachmentName = $attachmentName;
    }


    /**
     * Returns the plaintextTemplate
     *
     * @return string $plaintextTemplate
     */
    public function getPlaintextTemplate(): string
    {
        return $this->plaintextTemplate;
    }


    /**
     * Sets the plaintextTemplate
     *
     * @param string $plaintextTemplate
     * @return void
     */
    public function setPlaintextTemplate(string $plaintextTemplate): void
    {
        $this->plaintextTemplate = $plaintextTemplate;
    }


    /**
     * Returns the htmlTemplate
     *
     * @return string $htmlTemplate
     */
    public function getHtmlTemplate(): string
    {
        return $this->htmlTemplate;
    }


    /**
     * Sets the htmlTemplate
     *
     * @param string $htmlTemplate
     * @return void
     */
    public function setHtmlTemplate(string $htmlTemplate): void
    {
        $this->htmlTemplate = $htmlTemplate;
    }


    /**
     * Returns the calendarTemplate
     *
     * @return string $calendarTemplate
     */
    public function getCalendarTemplate(): string
    {
        return $this->calendarTemplate;
    }


    /**
     * Sets the calendarTemplate
     *
     * @param string $calendarTemplate
     * @return void
     */
    public function setCalendarTemplate(string $calendarTemplate): void
    {
        $this->calendarTemplate = $calendarTemplate;
    }


    /**
     * Returns the layoutPath
     *
     * @return array
     * @throws \Exception
     */
    public function getLayoutPaths(): array
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->layoutPaths, true);
        return $paths;
    }


    /**
     * Sets the layoutPaths
     *
     * @param array $layoutPaths
     * @return void
     */
    public function setLayoutPaths(array $layoutPaths): void
    {
        $this->layoutPaths = implode(',', $layoutPaths);
    }


    /**
     * Sets the layoutPath
     *
     * @param string $layoutPath
     * @return void
     * @deprecated use addLayoutPath or setLayoutPaths instead
     */
    public function setLayoutPath(string $layoutPath): void
    {
        $this->addLayoutPath($layoutPath);
    }


    /**
     * Adds an layoutPath
     *
     * @param string $layoutPath
     * @return void
     */
    public function addLayoutPath(string $layoutPath): void
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->layoutPaths);
        $paths[] = $layoutPath;
        $this->layoutPaths = implode(',', $paths);
    }


    /**
     * Adds layoutPaths
     *
     * @param array $layoutPaths
     * @return void
     */
    public function addLayoutPaths(array $layoutPaths): void
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->layoutPaths, true);
        $this->layoutPaths = implode(',', array_merge($paths, $layoutPaths));
    }


    /**
     * Returns the partialPath
     *
     * @return array
     * @throws \Exception
     */
    public function getPartialPaths(): array
    {
        $paths = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->partialPaths, true);
        return $paths;
    }


    /**
     * Sets the partialPaths
     *
     * @param array $partialPaths
     * @return void
     */
    public function setPartialPaths(array $partialPaths): void
    {
        $this->partialPaths = implode(',', $partialPaths);
    }


    /**
     * Sets the partialPath
     *
     * @param string $partialPath
     * @return void
     * @deprecated use addPartialPath or setPartialPaths instead
     */
    public function setPartialPath(string $partialPath): void
    {
        $this->addPartialPath($partialPath);
    }


    /**
     * Adds an partialPath
     *
     * @param string $partialPath
     * @return void
     */
    public function addPartialPath(string $partialPath): void
    {
        $paths = GeneralUtility::trimExplode(',', $this->partialPaths, true);
        $paths[] = $partialPath;
        $this->partialPaths = implode(',', $paths);
    }


    /**
     * Adds partialPaths
     *
     * @param array $partialPaths
     * @return void
     */
    public function addPartialPaths(array $partialPaths): void
    {
        if (is_array($partialPaths)) {
            $paths = GeneralUtility::trimExplode(',', $this->partialPaths, true);
            $this->partialPaths = implode(',', array_merge($paths, $partialPaths));
        }
    }


    /**
     * Returns the templatePath
     *
     * @return array
     * @throws \Exception
     */
    public function getTemplatePaths(): array
    {
        $paths = GeneralUtility::trimExplode(',', $this->templatePaths, true);
        return $paths;
    }


    /**
     * Sets the templatePaths
     *
     * @param array $templatePaths
     * @return void
     */
    public function setTemplatePaths(array $templatePaths): void
    {
        $this->templatePaths = implode(',', $templatePaths);
    }


    /**
     * Sets the templatePath
     *
     * @param string $templatePath
     * @return void
     * @deprecated use addTemplatePath or setTemplatePaths instead
     */
    public function setTemplatePath(string $templatePath): void
    {
        $this->addTemplatePath($templatePath);
    }


    /**
     * Adds an templatePath
     *
     * @param string $templatePath
     * @return void
     */
    public function addTemplatePath(string $templatePath): void
    {
        $paths = GeneralUtility::trimExplode(',', $this->templatePaths, true);
        $paths[] = $templatePath;
        $this->templatePaths = implode(',', $paths);
    }


    /**
     * Adds templatePaths
     *
     * @param array $templatePaths
     * @return void
     */
    public function addTemplatePaths(array $templatePaths): void
    {
        if (is_array($templatePaths)) {
            $paths = GeneralUtility::trimExplode(',', $this->templatePaths, true);
            $this->templatePaths = implode(',', array_merge($paths, $templatePaths));
        }
    }


    /**
     * Returns the category
     *
     * @return string $category
     */
    public function getCategory(): string
    {
        return $this->category;
    }


    /**
     * Sets the category
     *
     * @param string $category
     * @return void
     */
    public function setCategory(string $category): void
    {
        $this->category = $category;
    }


    /**
     * Returns the campaignParameter
     *
     * @return string
     */
    public function getCampaignParameter(): string
    {
        return $this->campaignParameter;
    }


    /**
     * Returns the exploded campaignParameter
     *
     * @return array
     */
    public function getCampaignParameterExploded(): array
    {

        // explode by ampersand
        $implodedFirst = explode('&', str_replace('?', '', $this->campaignParameter));

        // now explode by equal-sign
        $result = array();
        foreach ($implodedFirst as $entry) {

            $tempExplode = explode('=', $entry);
            if (
                (count($tempExplode) == 2)
                && (strlen(trim($tempExplode[0])) > 0)
            ) {
                $result [trim($tempExplode[0])] = trim($tempExplode[1]);
            }

        }

        return $result;
    }


    /**
     * Sets the campaignParameter
     *
     * @param string $campaignParameter
     * @return void
     */
    public function setCampaignParameter(string $campaignParameter): void
    {
        $this->campaignParameter = $campaignParameter;
    }


    /**
     * Returns the priority
     *
     * @return int $priority
     */
    public function getPriority(): int
    {
        return $this->priority;
    }


    /**
     * Sets the priority
     *
     * @param int $priority
     * @return void
     */
    public function setPriority(int $priority): void
    {
        $this->priority = $priority;
    }


    /**
     * Returns the settingsPid
     *
     * @return int $settingsPid
     */
    public function getSettingsPid(): int
    {
        return $this->settingsPid;
    }


    /**
     * Sets the settingsPid
     *
     * @param int $settingsPid
     * @return void
     */
    public function setSettingsPid(int $settingsPid): void
    {
        $this->settingsPid = $settingsPid;
    }

    /**
     * Returns the mailingStatistics
     *
     * @return \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics
     */
    public function getMailingStatistics():? MailingStatistics
    {
        return $this->mailingStatistics;
    }


    /**
     * Sets the mailingStatistics
     *
     * @param \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics
     * @return void
     */
    public function setMailingStatistics(MailingStatistics $mailingStatistics): void
    {
        $this->mailingStatistics = $mailingStatistics;
    }


    /**
     * Returns the tstampFavSending
     *
     * @return int $tstampFavSending
     * @deprecated
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
     * @deprecated
     */
    public function setTstampFavSending(int $tstampFavSending): void
    {
        $this->tstampFavSending = $tstampFavSending;
    }

    /**
     * Returns the tstampRealSending
     *
     * @return int $tstampRealSending
     * @deprecated
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
     * @deprecated
     */
    public function setTstampRealSending(int $tstampRealSending): void
    {
        $this->tstampRealSending = $tstampRealSending;
    }


    /**
     * Returns the tstampSendFinish
     *
     * @return int $tstampSendFinish
     * @deprecated
     */
    public function getTstampSendFinish(): int
    {
        return $this->tstampSendFinish;
    }


    /**
     * Sets the tstampSendFinish
     *
     * @param int $tstampSendFinish
     * @return void
     * @deprecated
     */
    public function setTstampSendFinish(int $tstampSendFinish): void
    {
        $this->tstampSendFinish = $tstampSendFinish;
    }


    /**
     * Returns the total
     *
     * @return int $total
     * @deprecated
     */
    public function getTotal(): int
    {
        return $this->total;
    }


    /**
     * Returns the sent
     *
     * @return int $sent
     * @deprecated
     */
    public function getSent(): int
    {
        return $this->sent;
    }


    /**
     * Returns the successful
     *
     * @return int $successful
     * @deprecated
     */
    public function getSuccessful(): int
    {
        return $this->successful;
    }


    /**
     * Returns the failed
     *
     * @return int $failed
     * @deprecated
     */
    public function getFailed(): int
    {
        return $this->failed;
    }


    /**
     * Returns the deferred
     *
     * @return int $deferred
     * @deprecated
     */
    public function getDeferred(): int
    {
        return $this->deferred;
    }


    /**
     * Returns the bounced
     *
     * @return int $bounced
     * @deprecated
     */
    public function getBounced(): int
    {
        return $this->bounced;
    }


    /**
     * Returns the opened
     *
     * @return int $opened
     * @deprecated
     */
    public function getOpened(): int
    {
        return $this->opened;
    }


    /**
     * Returns the clicked
     *
     * @return int $clicked
     * @deprecated
     */
    public function getClicked(): int
    {
        return $this->clicked;
    }

}
