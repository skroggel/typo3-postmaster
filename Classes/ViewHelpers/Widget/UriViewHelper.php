<?php
namespace Madj2k\Postmaster\ViewHelpers\Widget;
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

use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class UriViewHelper
 *
 * @author Christian Dilger <c.dilger@addorange.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write tests
 */
class UriViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Widget\UriViewHelper
{

    /**
     * Get the URI for a non-AJAX Request.
     *
     * Thanks to https://www.npostnik.de/typo3/pagination-widget-im-backend-anpassen/
     *
     * @param RenderingContextInterface $renderingContext
     * @param array $arguments
     * @return string the Widget URI
     */
    protected static function getWidgetUri(RenderingContextInterface $renderingContext, array $arguments): string
    {
        $controllerContext = $renderingContext->getControllerContext();
        $uriBuilder = $controllerContext->getUriBuilder();
        $argumentPrefix = $controllerContext->getRequest()->getArgumentPrefix();
        $cleanedArgumentPrefix = substr($argumentPrefix, 0, strpos($argumentPrefix, '['));
        $parameters = $arguments['arguments'] ?? [];

        if ($arguments['action'] ?? false) {
            $parameters['action'] = $arguments['action'];
        }
        if (($arguments['format'] ?? '') !== '') {
            $parameters['format'] = $arguments['format'];
        }

        $uriArguments = [$argumentPrefix => $parameters];
        if ($filterArguments = self::getFilterArguments($cleanedArgumentPrefix)) {
            $uriArguments[$cleanedArgumentPrefix] = $filterArguments;
        }

        return $uriBuilder->reset()
            ->setArguments($uriArguments)
            ->setSection($arguments['section'])
            ->setUseCacheHash($arguments['useCacheHash'])
            ->setAddQueryString(true)
            ->setAddQueryStringMethod($arguments['addQueryStringMethod'])
            ->setArgumentsToBeExcludedFromQueryString([$argumentPrefix, 'cHash'])
            ->setFormat($arguments['format'])
            ->build();
    }


    /**
     * Adds filter-arguments to normal ones
     *
     * @param string $argumentPrefix
     * @return array
     */
    protected static function getFilterArguments(string $argumentPrefix = ''): array
    {
        $moduleArguments = \TYPO3\CMS\Core\Utility\GeneralUtility::_GP($argumentPrefix);
        if (!$moduleArguments) {
            return [];
        }
;
        $arguments = [];
        foreach($moduleArguments as $key => $value) {
            if (strpos($key, '__') === false) {
                $arguments[$key] = $value;
            }
        }

        return $arguments;
    }
}
