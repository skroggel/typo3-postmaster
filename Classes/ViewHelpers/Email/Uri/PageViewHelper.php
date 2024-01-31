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
 * Class PageViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\Uri\PageViewHelper
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
        $pageUid = intval($arguments['pageUid']);
        $additionalParams = $arguments['additionalParams'] ?: [];
        $pageType = intval($arguments['pageType']);
        $noCache = boolval($arguments['noCache']);
        $section = $arguments['section'] ?: '';
        $linkAccessRestrictedPages = boolval($arguments['linkAccessRestrictedPages']);
        $addQueryString = boolval($arguments['addQueryString']);
        $argumentsToBeExcludedFromQueryString = $arguments['argumentsToBeExcludedFromQueryString'] ?: [];
        $addQueryStringMethod = $arguments['addQueryStringMethod'] ?: '';

        try {

            $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

            /** @var \Madj2k\Postmaster\UriBuilder\EmailUriBuilder $uriBuilder */
            $uriBuilder = $objectManager->get(EmailUriBuilder::class);

            $uriBuilder
                ->reset()
                ->setTargetPageUid($pageUid)
                ->setTargetPageType($pageType)
                ->setNoCache($noCache)
                ->setSection($section)
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

            return $uriBuilder->build();

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
