<?php

namespace Madj2k\Postmaster\Validation;

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

use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * EmailValidationUtility
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @todo write tests
 */
class EmailValidator
{

    /**
     * Cleans email
     *
     * @param string $email
     * @return string
     */
    public static function cleanUpEmail(string $email): string
    {
        return trim(str_replace('mailto:', '', $email));
    }


    /**
     * Validates email
     *
     * @param string $email
     * @return bool
     */
    public static function validateEmail(string $email): bool
    {
        $email = self::cleanUpEmail($email);
        return GeneralUtility::validEmail($email);
    }

}
