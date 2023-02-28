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

use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\UriBuilder\EmailUriBuilder;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;

/**
 * Class ActionViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ActionViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\ActionViewHelper
{

    /**
     * The output must not be escaped.
     *
     * @var bool
     */
    protected $escapeOutput = false;


    /**
     * Initialize arguments
     *
     * @return void
     * @api
     */
    public function initializeArguments(): void
    {
        parent::initializeArguments();
        $this->registerArgument('queueMail', QueueMail::class, 'QueueMail-object for redirecting links');
        $this->registerArgument('queueRecipient', QueueRecipient::class, 'QueueRecipient-object of email');
    }


    /**
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

        $queueMail = $arguments['queueMail'];
        $queueRecipient = $arguments['queueRecipient'];
        $pageUid = $arguments['pageUid'];
        $pageType = $arguments['pageType'];
        $noCache = $arguments['noCache'];
        $noCacheHash = $arguments['noCacheHash'];
        $section = $arguments['section'];
        $format = $arguments['format'];
        $linkAccessRestrictedPages = $arguments['linkAccessRestrictedPages'];
        $additionalParams = $arguments['additionalParams'];
        $addQueryString = $arguments['addQueryString'];
        $argumentsToBeExcludedFromQueryString = $arguments['argumentsToBeExcludedFromQueryString'];
        $addQueryStringMethod = $arguments['addQueryStringMethod'];
        $action = $arguments['action'];
        $controller = $arguments['controller'];
        $extensionName = $arguments['extensionName'];
        $pluginName = $arguments['pluginName'];
        $arguments = $arguments['arguments'];

        try {

            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \Madj2k\Postmaster\UriBuilder\EmailUriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get(EmailUriBuilder::class);

            $uriBuilder
                ->reset()
                ->setTargetPageUid($pageUid)
                ->setTargetPageType($pageType)
                ->setNoCache($noCache)
                ->setUseCacheHash(!$noCacheHash)
                ->setSection($section)
                ->setFormat($format)
                ->setLinkAccessRestrictedPages($linkAccessRestrictedPages)
                ->setArguments($additionalParams)
                ->setCreateAbsoluteUri(true)// force absolute link
                ->setAddQueryString($addQueryString)
                ->setArgumentsToBeExcludedFromQueryString($argumentsToBeExcludedFromQueryString)
                ->setAddQueryStringMethod($addQueryStringMethod);

            if ($queueMail) {
                $uriBuilder->setUseRedirectLink(true)
                    ->setQueueMail($queueMail);

                if ($queueRecipient) {
                    $uriBuilder->setQueueRecipient($queueRecipient);
                }
            }

            return $uriBuilder->uriFor($action, $arguments, $controller, $extensionName, $pluginName);

        } catch (\Exception $e) {

            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->log(
                LogLevel::ERROR,
                sprintf(
                    'Error while trying to set link: %s',
                    $e->getMessage()
                )
            );
        }

        return '';
    }

}
