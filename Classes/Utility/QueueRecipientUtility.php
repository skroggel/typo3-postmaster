<?php

namespace Madj2k\Postmaster\Utility;

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

use Madj2k\Accelerator\Persistence\MarkerReducer;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\Validation\EmailValidator;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Domain\Model\BackendUser;
use TYPO3\CMS\Extbase\Domain\Model\FrontendUser;
use TYPO3\CMS\Extbase\DomainObject\AbstractEntity;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * QueueRecipientUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class QueueRecipientUtility
{

    /**
     * @var string
     */
    const STATUS_DRAFT = 1;

    /**
     * @var string
     */
    const STATUS_WAITING = 2;

    /**
     * @var string
     */
    const STATUS_SENDING = 3;

    /**
     * @var string
     */
    const STATUS_FINISHED = 4;

    /**
     * @var string
     */
    const STATUS_DEFERRED = 97;

    /**
     * @var string
     */
    const STATUS_BOUNCED = 98;

    /**
     * @var string
     */
    const STATUS_ERROR = 99;


    /**
     * Get a QueueRecipient-object based on properties of a given object
     *
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|array $basicData
     * @param array $additionalData
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient
     */
    public static function initQueueRecipient (
        $basicData = [],
        array $additionalData = []
    ): QueueRecipient {

        // if a FrontendUser is given, take it's basic values
        if ($basicData instanceof FrontendUser) {
            return self::initQueueRecipientViaFrontendUser($basicData, $additionalData);

        // if a BackendUser is given, take it's basic values
        } else if ($basicData instanceof BackendUser) {
            return self::initQueueRecipientViaBackendUser($basicData, $additionalData);
        }

        // fallback with array
        if (is_array($basicData)) {
            return self::initQueueRecipientViaArray(array_merge($additionalData, $basicData));
        }
        return self::initQueueRecipientViaArray($additionalData);
    }


    /**
     * Get a QueueRecipient-object with properties set via FrontendUser
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser $frontendUser
     * @param array $additionalData
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public static function initQueueRecipientViaFrontendUser (
        FrontendUser $frontendUser,
        array $additionalData = []
    ): QueueRecipient {

        // expand mapping for \Madj2k\FeRegister\Domain\Model\FrontendUser
        $additionalPropertyMapper = [
            'username' => 'email'
        ];

        if ($frontendUser instanceof \Madj2k\FeRegister\Domain\Model\FrontendUser) {
            $additionalPropertyMapper['txFeregisterGender'] = 'salutation';
            $additionalPropertyMapper['txFeregisterLanguageKey'] = 'languageCode';
            $additionalPropertyMapper['titleText'] = 'title';
        }

        // set all relevant values according to given data
        $queueRecipient = self::setProperties($frontendUser, $additionalData, $additionalPropertyMapper);

        /* @todo Leeds to problems since this does an implicit update on the object
         * which may lead to persisting data before having received a confirmation via opt-in-mail!!!
        if (!$frontendUser->_isNew()) {
            $queueRecipient->setFrontendUser($frontendUser);
        }*/

        return $queueRecipient;
    }


    /**
     * Get a QueueRecipient-object with properties set via BackendUser
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\BackendUser $backendUser
     * @param array $additionalData
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public static function initQueueRecipientViaBackendUser (
        BackendUser $backendUser,
        array $additionalData = []
    ): QueueRecipient {

        // expand mapping for \Madj2k\FeRegister\Domain\Model\BackendUser
        $additionalPropertyMapper = [];
        if ($backendUser instanceof \Madj2k\CoreExtended\Domain\Model\BackendUser) {
            $additionalPropertyMapper['lang'] = 'languageCode';
        }

        // find realName
        $realName = $backendUser->getRealName();
        if (
            (empty($realName))
            && (isset($additionalData['realName']))
            && ($additionalData['realName'])
        ) {
            $realName = $additionalData['realName'];
        }

        // split realName
        self::explodeNameToAdditionalData($realName, $additionalData);

        // set all relevant values according to given data
       return self::setProperties($backendUser, $additionalData, $additionalPropertyMapper);
    }


    /**
     * Get a QueueRecipient-object with properties set via array
     *
     * @param array $data
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    public static function initQueueRecipientViaArray (
        array $data = []
    ): QueueRecipient {

        return self::setProperties(null, $data);
    }


    /**
     * @param \TYPO3\CMS\Extbase\DomainObject\AbstractEntity|null $user
     * @param array $additionalPropertyMapper
     * @param array $additionalData
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient
     * @throws \TYPO3\CMS\Extbase\Persistence\Generic\Exception
     */
    protected static function setProperties (
        \TYPO3\CMS\Extbase\DomainObject\AbstractEntity $user = null,
        array $additionalData = [],
        array $additionalPropertyMapper = []
    ): QueueRecipient {

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        // define property mapping - ordering is important!
        $defaultPropertyMapper = [
            'email' => 'email',
            'title' => 'title',
            'salutation' => 'salutation',
            'firstName' => 'firstName',
            'lastName' => 'lastName',
            'subject' => 'subject',
            'languageCode' => 'languageCode',
            'marker' => 'marker'
        ];

        // add additional mappings
        $propertyMapper = array_merge(
            $additionalPropertyMapper,
            $defaultPropertyMapper
        );

        // set all relevant values according to given data
        foreach ($propertyMapper as $propertySource => $propertyTarget) {
            $getter = 'get' . ucFirst($propertySource);
            $setter = 'set' . ucFirst($propertyTarget);

            if (method_exists($queueRecipient, $setter)) {

                // check for getter value
                if (
                    ($user instanceof AbstractEntity)
                    && (method_exists($user, $getter))
                    && (null !== $value = $user->$getter())
                    && ($value !== '') // We cannot check with empty() here, because 0 is a valid value
                    && ($value !== 99)
                ) {

                    // only if value is valid email
                    if ($propertyTarget == 'email') {
                        if (EmailValidator::validateEmail($value)) {
                            $queueRecipient->$setter($value);
                        }
                    } elseif ($propertyTarget == 'marker'){
                        if (is_array($value)) {
                            $queueRecipient->$setter(
                                MarkerReducer::implode($value)
                            );
                        }
                    } else {
                        $queueRecipient->$setter($value);
                    }

                // fallback: check for value in additional data
                } else if (
                    (isset($additionalData[$propertySource]))
                    && (null !== $value = $additionalData[$propertySource])
                    && ($value !== '') // We cannot check with empty() here, because 0 is a valid value
                    && ($value !== 99)
                ){
                    // only if value is valid email
                    if ($propertyTarget == 'email') {
                        if (EmailValidator::validateEmail($value)) {
                            $queueRecipient->$setter($value);
                        }
                    } elseif ($propertyTarget == 'marker'){
                        if (is_array($value)) {
                            $queueRecipient->$setter(
                                MarkerReducer::implode($value)
                            );
                        }
                    } else {
                        $queueRecipient->$setter($value);
                    }
                }
            }
        }

        return $queueRecipient;
    }


    /**
     * Explodes name on spaces and sets values in additionalData-Array accordingly
     *
     * @param string $name
     * @param array $additionalData
     * @return void
     */
    protected static function explodeNameToAdditionalData(string $name, array &$additionalData): void
    {

        // split name
        $nameArray = explode(' ', $name);

        // set default
        $additionalData['lastName'] = $name;
        if (count($nameArray) == 2) {
            if (isset($nameArray[0])) {
                $additionalData['firstName'] = $nameArray[0];
            }
            if (isset($nameArray[1])) {
                $additionalData['lastName'] = $nameArray[1];
            }

        } else if (count($nameArray) == 3) {
            if (isset($nameArray[0])) {
                $additionalData['title'] = $nameArray[0];
            }
            if (isset($nameArray[1])) {
                $additionalData['firstName'] = $nameArray[1];
            }
            if (isset($nameArray[2])) {
                $additionalData['lastName'] = $nameArray[2];
            }
        }
    }
}
