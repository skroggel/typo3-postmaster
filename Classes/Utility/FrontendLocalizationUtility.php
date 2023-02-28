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

/**
 * FrontendLocalization
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility
 * @deprecated since TYPO3 v9.5 all relevant functions are already included in the core-class
 */
class FrontendLocalizationUtility extends \TYPO3\CMS\Extbase\Utility\LocalizationUtility
{

    /**
     * constructor
     */
    public function __construct() {
        trigger_error(
            __CLASS__ . ' is deprecated and will be removed soon. Use \TYPO3\CMS\Extbase\Utility\LocalizationUtility instead.',
            E_USER_DEPRECATED
        );
    }

}
