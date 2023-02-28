<?php

namespace Madj2k\Postmaster\Validation;
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

use Madj2k\Postmaster\Domain\Model\QueueMail;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * QueueMailValidator
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write tests
 */
class QueueMailValidator implements \TYPO3\CMS\Core\SingletonInterface
{

    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * validateQueueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return bool
     */
    public function validate(QueueMail $queueMail): bool
    {

        $valid = true;
        if (!$queueMail->getFromName()) {
            $this->getLogger()->log(LogLevel::ERROR,
                sprintf(
                    'No fromName is set (queueMail with uid %s).',
                    $queueMail->getUid()
                )
            );
            $valid = false;
        }

        if (!$queueMail->getFromAddress()) {
            $this->getLogger()->log(
                LogLevel::ERROR,
                sprintf(
                    'No fromAddress is set (queueMail with uid %s).',
                    $queueMail->getUid()
                )
            );
            $valid = false;
        }

        if (!$queueMail->getSubject()) {
            $this->getLogger()->log(
                LogLevel::WARNING,
                sprintf(
                    'No Subject is set (queueMail with uid %s).',
                    $queueMail->getUid()
                )
            );
        }

        return $valid;
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
