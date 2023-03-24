<?php
namespace Madj2k\Postmaster\Example;

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

use Madj2k\CoreExtended\Utility\GeneralUtility;
use Madj2k\Postmaster\Mail\MailMessage;
use Madj2k\Postmaster\Utility\FrontendLocalizationUtility;
use Madj2k\FeRegister\Domain\Model\FrontendUser;
use Madj2k\FeRegister\Domain\Model\OptIn;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\ObjectStorage;

/**
 * MailService
 * This is an example file for using the mailer-API as a mail-service
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailService implements \TYPO3\CMS\Core\SingletonInterface
{


    /**
     * Handles create user event
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \Madj2k\FeRegister\Domain\Model\OptIn $optIn
     * @return void
     * @throws \Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendOptInEmail(FrontendUser $frontendUser, OptIn $optIn): void
    {

        /** Load configuration an template path */
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var \Madj2k\Postmaster\Mail\MailMessage $mailMessage */
            $mailMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailMessage::class);

            /**
             * Here we set the recipients of the email.
             * Expected params:
             * 1. Parameter: FE-User-object OR array with the following keys:
             *      email --> email-address - HAS TO BE SET!
             *      firstName --> first name - optional
             *      lastName --> last name - optional
             * 2. Parameter: Additional params that are added to the object of the mail-recipient
             *
             * @see: Madj2k\Postmaster\Domain\Model\QueueRecipient
             *      Example:
             *      subject (string) --> Overrides globally set subject of the email. Allows to personalize the subject of the email
             *      marker (array) --> Every key-value-pair given into the marker array will be given into the fluid template
             *      The languageKey is needed to translate the email into the language of the user
             * @see Resources/Private/Templates/Email/Example.html
             *      You can also use the variables queueMail and queueRecipient in fluid. These reference to the following objects:
             * @see: Madj2k\Postmaster\Domain\Model\QueueMail
             * @see: Madj2k\Postmaster\Domain\Model\QueueRecipient
             * You can call setTo multiple times in order to send the same email to different users.
             * The variables will be set for every recipient accordingly.
             */
            $mailMessage->setTo($frontendUser, array(
                'marker' => array(
                    'tokenYes'        => $optIn->getTokenYes(),
                    'tokenNo'         => $optIn->getTokenNo(),
                    'tokenUser'       => $optIn->getTokenUser(),
                    'frontendUser'    => $frontendUser,
                    'settings'        => $settingsDefault,
                    'pageUid'         => intval($GLOBALS['TSFE']->id),
                ),
            ));

            /**
             * Set the globally used subject
             * Here we use a user-specific translation based on the languageKey of the user.
             */
            $mailMessage->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
                    'postmaster.optIn.subject',
                    'your_extension',
                    null,
                    $frontendUser->getTxFeregisterLanguageKey()
                )
            );

            /**
             * Set the templates. The templates are to be placed in the extension that uses the service.
             */
            $mailMessage->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailMessage->getQueueMail()->setPlaintextTemplate('Email/Example/OptIn');
            $mailMessage->getQueueMail()->setHtmlTemplate('Email/Example/OptIn');

            /**
             * send the email.
             * If you have set more than one recipient, the mail will be queued and send via cronjob
             */
            $mailMessage->send();
        }
    }


    /**
     * Handles optIn-event for group-admins
     *
     * @param \Madj2k\FeRegister\Domain\Model\FrontendUser $frontendUser
     * @param \Madj2k\FeRegister\Domain\Model\OptIn $optIn
     * @param \TYPO3\CMS\Extbase\Persistence\ObjectStorage<\Madj2k\FeRegister\Domain\Model\BackendUser> $approvals
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function sendGroupOptInEmailAdmin(FrontendUser $frontendUser, OptIn $optIn, ObjectStorage $approvals): void
    {

        // get settings
        $settings = $this->getSettings(ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK);
        $settingsDefault = $this->getSettings();

        if ($settings['view']['templateRootPaths']) {

            /** @var \Madj2k\Postmaster\Mail\MailMessage $mailMessage */
            $mailMessage = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(MailMessage::class);

            /** @var \Madj2k\FeRegister\Domain\Model\BackendUser $backendUser */
            foreach ($approvals as $backendUser) {

                // send new user an email with token
                $mailMessage->setTo($backendUser, array(
                    'marker' => array(
                        'tokenYes' => $optIn->getAdminTokenYes(),
                        'tokenNo' => $optIn->getAdminTokenNo(),
                        'tokenUser' => $optIn->getTokenUser(),
                        'frontendUser' => $frontendUser,
                        'backendUser' => $backendUser,
                        'frontendUserGroup' => $optIn->getData(),
                        'settings' => $settingsDefault,
                        'pageUid' => intval($GLOBALS['TSFE']->id),
                    ),

                    /**
                     * Set the specific subject based on the language of the backendUser
                     */
                    'subject' => FrontendLocalizationUtility::translate(
                        'postmaster.group.optInAdmin.subject',
                        'your_extension',
                        null,
                        $backendUser->getLang()
                    ),
                ));
            }

            /**
             * Set the globally used subject
             * Here we use a user-specific translation based on the languageKey of the user.
             */
            $mailMessage->getQueueMail()->setSubject(
                FrontendLocalizationUtility::translate(
                    'postmaster.group.optInAdmin.subject',
                    'your_extension',
                )
            );

            $mailMessage->getQueueMail()->addTemplatePaths($settings['view']['templateRootPaths']);
            $mailMessage->getQueueMail()->setPlaintextTemplate('Email/Example/OptInAdmin');
            $mailMessage->getQueueMail()->setHtmlTemplate('Email/Example/OptInAdmin');
            $mailMessage->send();
        }
    }

    /**
     * Returns TYPO3 settings
     *
     * @param string $which Which type of settings will be loaded
     * @return array
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    protected function getSettings(string $which = ConfigurationManagerInterface::CONFIGURATION_TYPE_SETTINGS): array
    {
        return GeneralUtility::getTypoScriptConfiguration('YourExtension', $which);
    }
}
