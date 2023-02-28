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

use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use Madj2k\Postmaster\UriBuilder\EmailUriBuilder;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithContentArgumentAndRenderStatic;

/**
 * Class RedirectLinksViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RedirectLinksViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
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
        $this->registerArgument('queueMail', QueueMail::class, 'QueueMail-object for redirecting links');
        $this->registerArgument('queueRecipient', QueueRecipient::class, 'QueueRecipient-object of email');
        $this->registerArgument('isPlaintext', 'boolean', 'QueueRecipient-object of email');
        $this->registerArgument('additionalParams', 'array', 'Additional params for links');
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
        $queueMail = $arguments['queueMail'];
        $queueRecipient = $arguments['queueRecipient'];
        $isPlaintext  = (bool) $arguments['isPlaintext'];
        $additionalParams = $arguments['additionalParams'] ? $arguments['additionalParams'] : [] ;

        try {

            if ($queueMail) {

                // plaintext replacement
                if ($isPlaintext) {

                    return preg_replace_callback(
                        '/(http[s]?:\/\/[^\s]+)/',
                        function ($matches) use ($queueMail, $queueRecipient, $additionalParams) {

                            // do replacement but not for anchors and mailto's
                            if (
                                (count($matches) == 2)
                                && (strpos($matches[1], '#') !== 0)
                                && (strpos($matches[1], 'mailto:') !== 0)
                            ) {
                                return self::replace(
                                    trim($matches[1], '[](){}'),
                                    $queueMail,
                                    $queueRecipient,
                                    $additionalParams
                                );
                            }
                            return $matches[0];
                        }
                        , $value
                    );

                // HTML- replacement
                } else {

                    // U for non-greedy behavior: take as less signs as possible
                    return preg_replace_callback(
                        '/(<a.+href=")([^"]+)(")/U',
                        function ($matches) use ($queueMail, $queueRecipient, $additionalParams) {

                            // do replacement - but not for anchors and mailto's
                            if (
                                (count($matches) == 4)
                                && (strpos($matches[2], '#') !== 0)
                                && (strpos($matches[2], 'mailto:') !== 0)
                            ) {
                                return $matches[1] .
                                    self::replace(
                                        $matches[2],
                                        $queueMail,
                                        $queueRecipient,
                                        $additionalParams
                                    ) . $matches[3];
                            }

                            return $matches[0];
                        },
                        $value
                    );
                }
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


    /**
     * Replaces the link
     *
     * @param string $link
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient|null $queueRecipient
     * @param array $additionalParams
     * @return string
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    static protected function replace(
        string $link,
        QueueMail $queueMail,
        ?QueueRecipient $queueRecipient = null,
        array $additionalParams = []
    ): string {

        // load EmailUriBuilder
        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        /** @var \Madj2k\Postmaster\UriBuilder\EmailUriBuilder $uriBuilder */
        $uriBuilder = $objectManager->get(EmailUriBuilder::class);
        $uriBuilder->reset();

        $uriBuilder->setUseRedirectLink(true)
            ->setQueueMail($queueMail)
            ->setRedirectLink($link)
            ->setArguments($additionalParams);

        if ($queueRecipient) {
            $uriBuilder->setQueueRecipient($queueRecipient);
        }

        return $uriBuilder->build();

    }
}
