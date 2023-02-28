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

use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\UriBuilder\EmailUriBuilder;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3Fluid\Fluid\Core\Rendering\RenderingContextInterface;
use TYPO3Fluid\Fluid\Core\ViewHelper\Traits\CompileWithRenderStatic;

/**
 * Class PixelCounterViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PixelCounterViewHelper extends \TYPO3Fluid\Fluid\Core\ViewHelper\AbstractViewHelper
{

    use CompileWithRenderStatic;


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
        $this->registerArgument('queueMail', QueueMail::class, 'QueueMail-object for counter');
        $this->registerArgument('queueRecipient', QueueRecipient::class, 'QueueRecipient-object for counter');
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

        try {

            $settings = self::getSettings();
            $counterPixelPid = intval($settings['counterPixelPid']);

            if (
                ($counterPixelPid > 0)
                && ($queueRecipient > 0)
                && ($queueMail > 0)
            ) {

                // load EmailUriBuilder
                $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

                /** @var \Madj2k\Postmaster\UriBuilder\EmailUriBuilder $uriBuilder */
                $uriBuilder = $objectManager->get(EmailUriBuilder::class);
                $uriBuilder->reset();

                // build link to controller action with needed params
                $uriBuilder->setTargetPageUid($counterPixelPid)
                    ->setNoCache(true)
                    ->setArguments(
                        array(
                            'tx_postmaster_tracking[uid]' => intval($queueRecipient->getUid()),
                            'tx_postmaster_trackingr[mid]' => intval($queueMail->getUid()),
                        )
                    );

                return '<img src="' . urldecode(
                        $uriBuilder->uriFor(
                            'opening',
                            array(),
                            'Tracking',
                            'postmaster',
                            'Tracking'
                        )
                    ) . '" width="1" height="1" alt="" />';
            }

        } catch (\Exception $e) {

            $logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
            $logger->log(
                LogLevel::ERROR,
                sprintf(
                    'Error while trying to set pixel-counter: %s',
                    $e->getMessage()
                )
            );
        }

        return '';
    }


    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    static protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return \Madj2k\CoreExtended\Utility\GeneralUtility::getTypoScriptConfiguration('Postmaster', $which);
    }

}
