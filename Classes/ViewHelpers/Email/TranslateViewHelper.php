<?php
namespace Madj2k\Postmaster\ViewHelpers\Email;

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

use Madj2k\Postmaster\Utility\FrontendLocalizationUtility;
use TYPO3\CMS\Core\Localization\LanguageService;
use TYPO3\CMS\Extbase\Utility\LocalizationUtility;

/**
 * Class TranslateViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{

    /**
     * Wrapper call to static LocalizationUtility
     *
     * @param string $id Translation Key
     * @param string $extensionName UpperCamelCased extension key (for example BlogExample)
     * @param array $arguments Arguments to be replaced in the resulting string
     * @param string $languageKey Language key to use for this translation
     * @param string[] $alternativeLanguageKeys Alternative language keys if no translation does exist
     * @return string|null
     */
    protected static function translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys): ?string
    {
        return FrontendLocalizationUtility::translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys);
    }
}


