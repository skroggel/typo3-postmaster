<?php
namespace Madj2k\Postmaster\ViewHelpers\Email\Replace;

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

use Madj2k\Postmaster\Utility\EmailTypolinkUtility;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class RteLinks
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RteLinksViewHelper extends AbstractViewHelper
{

    use CompileWithContentArgumentAndRenderStatic;


    /**
     * @var bool
     */
    protected $escapeOutput = false;


    /**
     * Initialize arguments.
     *
     * @return void
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('value', 'string', 'String to work on');
        $this->registerArgument('plaintextFormat', 'boolean', 'Use plaintext-format for links. DEPRECATED.');
        $this->registerArgument('isPlaintext', 'boolean', 'Use plaintext-format for links.');
        $this->registerArgument('style', 'string', 'Style-attribute for links');
    }


    /**
     * Render typolinks
     **
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {

        $value = $renderChildrenClosure();
        $plaintextFormat = boolval($arguments['isPlaintext'] ?: $arguments['plaintextFormat']);
        $style = ($arguments['style'] ?: '');
        try {

            // log deprecated attribute
            if ($arguments['isPlaintext']) {
                trigger_error(
                    __CLASS__ . ': Argument "plaintextFormat" on postmaster:email.replace.rteLinks is deprecated. '.
                    'Use "isPlaintext" instead.',
                    E_USER_DEPRECATED
                );
            }

            // new version for TKE
            $value = preg_replace_callback(
                '/(<a([^>]+)href="([^"]+)"([^>]+)>([^<]+)<\/a>)/',
                function ($matches) use ($style, $plaintextFormat) {

                    if (count($matches) == 6) {
                        $attributes = trim($matches[2]) . ' ' . trim($matches[4]);
                        $parameter = $matches[3];
                        $linkText = $matches[5];
                        $url = EmailTypolinkUtility::getTypolinkUrl($parameter);
                        if ($plaintextFormat) {
                            return $linkText . ' [' . $url . ']';
                        } else {
                            $attributes = EmailTypolinkUtility::addStyleAttribute($attributes, $style);
                            return '<a href="' . $url . '" ' . trim($attributes) . '>' . $linkText . '</a>';
                        }
                    }
                    return $matches[0];
                },
                $value
            );

            // Old version for RTE
            // Plaintext replacement
            if ($plaintextFormat) {
                $value = preg_replace_callback(
                    '/(<link ([^>]+)>([^<]+)<\/link>)/',
                    function ($matches)  {
                        if (count($matches) == 4) {
                            $parameter = $matches[2];
                            $linkText = $matches[3];
                            $url = EmailTypolinkUtility::getTypolinkUrl($parameter);
                            return $linkText . ' [' . $url . ']';
                        }
                        return $matches[0];
                    },
                    $value
                );

            // HTML replacement
            } else {
                $value = preg_replace_callback(
                    '/(<link ([^>]+)>([^<]+)<\/link>)/',
                    function ($matches) use ($style) {
                        if (count($matches) == 4) {
                            $parameter = $matches[2];
                            $linkText = $matches[3];
                            return EmailTypolinkUtility::getTypolink($linkText, $parameter, '', $style);
                        }
                        return $matches[0];
                    },
                    $value
                );
            }

        } catch (\Exception $e) {

            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->log(
                LogLevel::ERROR,
                sprintf(
                    'Error while trying to replace links: %s',
                    $e->getMessage()
                )
            );
        }

        return $value;
    }
}
