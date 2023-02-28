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

use TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class PlaintextLineBreaksViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PlaintextLineBreaksViewHelper extends AbstractViewHelper
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
        $this->registerArgument('value', 'string', 'String to format');
        $this->registerArgument('keepLineBreaks', 'boolean', 'Convert line-breaks to \n. DEPRECATED.', false, false);
        $this->registerArgument('convertLineBreaks', 'boolean', 'Convert line-breaks to \n.', false, false);
    }

    /**
     * Handles line breaks and indents in plaintext mode
     *
     * @param array $arguments
     * @param \Closure $renderChildrenClosure
     * @param \TYPO3\CMS\Fluid\Core\Rendering\RenderingContextInterface $renderingContext
     * @return string
     */
    public static function renderStatic(
        array $arguments,
        \Closure $renderChildrenClosure,
        RenderingContextInterface $renderingContext
    ): string {

        $value = $renderChildrenClosure();
        $convertLineBreaks = (bool) ($arguments['convertLineBreaks'] ?: $arguments['keepLineBreaks']);

        // log deprecated attribute
        if ($arguments['keepLineBreaks']) {
            trigger_error(
                __CLASS__ . ': Argument "keepLineBreaks" on postmaster:email.plaintextLineBreaks is deprecated. '.
                'Use "convertLineBreaks" instead.',
                E_USER_DEPRECATED
            );
        }

        // convert line breaks to manual line breaks
        if ($convertLineBreaks) {
            $value = preg_replace( '/\r|\n/', '\n',  $value);
        }

        // replace real line breaks and indents
        $value = preg_replace("/\r|\n|\t|([ ]{2,})/", '', trim($value));

        // convert manual line breaks - only if no convertLineBreaks-attribute given!
        if (! $convertLineBreaks) {
            $value = str_replace('\n', "\n", $value);
        }

        return $value;
    }
}
