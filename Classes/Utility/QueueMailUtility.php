<?php
namespace Madj2k\Postmaster\Utility;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * QueueMailUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueMailUtility
{

    /**
     * @var string
     */
    const STATUS_DRAFT = 1;

    /**
     * @var string
     */
    const STATUS_WAITING = 2;

    /**
     * @var string
     */
    const STATUS_SENDING = 3;

    /**
     * @var string
     */
    const STATUS_FINISHED = 4;

    /**
     * @var string
     */
    const STATUS_DEFERRED = 97;

    /**
     * @var string
     */
    const STATUS_ERROR = 99;


    /**
     * Get a QueueMail-object with all initial properties set
     *
     * @param int $storagePid
     * @return \Madj2k\Postmaster\Domain\Model\QueueMail
     */
    public static function initQueueMail (
        int $storagePid = 0
    ): QueueMail {

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail*/
        $queueMail = GeneralUtility::makeInstance(QueueMail::class);

        $queueMail->setPid($storagePid);
        $queueMail->setSettingsPid(intval($GLOBALS['TSFE']->id));

        // set defaults
        $queueMail->setFromName($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']);
        $queueMail->setFromAddress($GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']);
        $queueMail->setReplyToAddress(
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToAddress'] ?:
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']
        );
        $queueMail->setReplyToName(
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReplyToName'] ?:
                $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromName']
        );
        $queueMail->setReturnPath(
            $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailReturnAddress'] ?:
                $GLOBALS['TYPO3_CONF_VARS']['MAIL']['defaultMailFromAddress']
        );

        return $queueMail;
    }


}
