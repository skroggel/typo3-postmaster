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

use TYPO3\CMS\Core\Localization\LanguageService;

/**
 * FrontendLocalization
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @see \TYPO3\CMS\Extbase\Utility\LocalizationUtility
 */
class FrontendLocalizationUtility extends \TYPO3\CMS\Extbase\Utility\LocalizationUtility
{

    /**
     * @return \TYPO3\CMS\Core\Localization\LanguageService
     */
    protected static function getLanguageService(): LanguageService
    {
        // for usage in CLI-context
        if (! $GLOBALS['LANG']) {

            /** @var \TYPO3\CMS\Core\Localization\LanguageService $languageService */
            $languageService = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LanguageService::class);
            $GLOBALS['LANG'] = $languageService;
        }

        return $GLOBALS['LANG'];
    }
}
