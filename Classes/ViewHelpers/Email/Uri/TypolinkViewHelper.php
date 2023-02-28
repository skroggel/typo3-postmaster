<?php
namespace Madj2k\Postmaster\ViewHelpers\Email\Uri;

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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use Madj2k\Postmaster\Utility\EmailTypolinkUtility;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class TypolinkViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TypolinkViewHelper extends AbstractViewHelper
{

    /**
     * Initialize arguments
     *
     * @return void
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('pageUid', 'int', 'pageUid for FE-configuration - DEPRECATED', false, null);
        $this->registerArgument('parameter', 'string', 'stdWrap.typolink style parameter string', true);
        $this->registerArgument('additionalParams', 'string', 'stdWrap.typolink additionalParams', false, '');
    }


    /**
     * Render typolinks
     **
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     * @throws \Madj2k\Postmaster\Exception
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ){

        $parameter = $arguments['parameter'];
        $additionalParams = $arguments['additionalParams'];

        // log deprecated attribute
        if ($arguments['pageUid']) {
            trigger_error(
                __CLASS__ . ': Argument "pageUid" on postmaster:email.uri.typolink is deprecated and has no effect any more.',
                E_USER_DEPRECATED
            );
        }

        $content = '';
        if ($parameter) {
            $content = EmailTypolinkUtility::getTypolinkUrl($parameter, $additionalParams);
        }

        return $content;
    }

}




