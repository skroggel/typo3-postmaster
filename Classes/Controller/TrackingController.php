<?php

namespace Madj2k\Postmaster\Controller;

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

use Madj2k\Postmaster\Tracking\ClickTracker;
use Madj2k\Postmaster\Tracking\OpeningTracker;

/**
 * TrackingController
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TrackingController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{

    /**
     * @var \Madj2k\Postmaster\Tracking\ClickTracker
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ClickTracker $clickTracker;


    /**
     * @var \Madj2k\Postmaster\Tracking\OpeningTracker
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected OpeningTracker $openingTracker;


    /**
     * action redirect
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function redirectAction()
    {
        $parameters = $this->request->getArguments();
        $hash = preg_replace('/[^a-zA-Z0-9]/', '', $parameters['hash']);
        $trackingUrl = filter_var($parameters['url'], FILTER_SANITIZE_URL);
        $queueMailId = intval($parameters['mid']);
        $queueMailRecipientId = intval($parameters['uid']);

        // try to get the tracking-url via old version with hash
        if ($hash) {
            $trackingUrl = $this->clickTracker->getPlainUrlByHash($hash);
        }

        // track the given url
        $this->clickTracker->track($queueMailId, $trackingUrl);

        // get the redirect-url with all relevant parameters
        if ($url = $this->clickTracker->getRedirectUrl($trackingUrl, $queueMailId, $queueMailRecipientId)) {

            // if no delay is set, redirect directly
            if (!intval($this->settings['redirectDelay'])) {
                /** @todo currently not working with subscription-edit redirect to subdomain - don't know why! */
                // $this->redirectToUri($url);
                // exit();
            }

            $this->addFlashMessage(
                \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                    'linkController.message.redirect_wait', 'postmaster'
                )
            );

            $this->view->assignMultiple(
                array(
                    'redirectUrl'     => $url,
                    'redirectTimeout' => intval($this->settings['redirectDelay']) * 1000,
                )
            );

            return;
        }

        $this->addFlashMessage(
            \TYPO3\CMS\Extbase\Utility\LocalizationUtility::translate(
                'linkController.error.redirect_not_possible', 'postmaster'
            ),
            '',
            \TYPO3\CMS\Core\Messaging\AbstractMessage::ERROR
        );

    }


    /**
     * action opening
     * count unique mail openings via tracking pixel
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     */
    public function openingAction()
    {

        $parameters = $this->request->getArguments();
        $queueMailId = intval($parameters['mid']);
        $queueRecipientId = intval($parameters['uid']);

        // track
        $this->openingTracker->track($queueMailId, $queueRecipientId);

        // return gif-data
        $name = \TYPO3\CMS\Core\Utility\GeneralUtility::getFileAbsFileName('EXT:postmaster/Resources/Public/Images/spacer.gif');
        header("Content-Type: image/gif");
        header("Content-Length: " . filesize($name));
        readfile($name);

        exit();
    }

}
