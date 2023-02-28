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

/**
 * Class TranslateViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated since TYPO3 v9.5 all relevant functions are already included in the core-class
 */
class TranslateViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\TranslateViewHelper
{

    /**
     * initializeArguments
     */
    public function initializeArguments()
    {
        parent::initializeArguments();
        trigger_error(__CLASS__ . ' is deprecated and will be removed soon', E_USER_DEPRECATED);
    }


    /**
     * Return array element by key.
     *
     * @param array   $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @throws \TYPO3\CMS\Fluid\Core\ViewHelper\Exception\InvalidVariableException
     * @return string
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): ?string {

        $key = $arguments['key'];
        $languageKey = $arguments['languageKey'];
        $id = $arguments['id'];
        $default = $arguments['default'];
        $extensionName = $arguments['extensionName'];
        $arguments = $arguments['arguments'];


        // Wrapper including a compatibility layer for TYPO3 Flow Translation
        if ($id === null) {
            $id = $key;
        }

        if ((string)$id === '') {
            throw new InvalidVariableException('An argument "key" or "id" has to be provided', 1351584844);
        }

        $request = $renderingContext->getControllerContext()->getRequest();
        $extensionName = $extensionName === null ? $request->getControllerExtensionName() : $extensionName;
        try {
            $value = FrontendLocalizationUtility::translate($id, $extensionName, $arguments, $languageKey);
        } catch (\InvalidArgumentException $e) {
            $value = null;
        }
        if ($value === null) {
            $value = $default !== null ? $default : $renderChildrenClosure();
            if (!empty($arguments)) {
                $value = vsprintf($value, $arguments);
            }
        }
        return $value;
    } */


    /**
     * Wrapper call to static LocalizationUtility
     *
     * @param string $id Translation Key
     * @param string $extensionName UpperCamelCased extension key (for example BlogExample)
     * @param array $arguments Arguments to be replaced in the resulting string
     * @param string $languageKey Language key to use for this translation
     * @param string[] $alternativeLanguageKeys Alternative language keys if no translation does exist
     * @return string|null

    protected static function translate($id, $extensionName, $arguments, $languageKey, $alternativeLanguageKeys): ?string
    {
        return FrontendLocalizationUtility::translate($id, $extensionName, $arguments, $languageKey);
    } */
}


