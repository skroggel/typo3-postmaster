<?php
namespace Madj2k\Postmaster\Cache;

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

/**
 * CacheInterface
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
interface CacheInterface
{


    /**
     * Gets the cache object
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    public function getCache(): \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface;


    /**
     * Returns cached content
     *
     * @param string $cacheIdentifier
     * @return string
     */
    public function getContent(string $cacheIdentifier): string;


    /**
     * Sets cache content
     *
     * @param string $cacheIdentifier
     * @param mixed $value
     * @return void
     */
    public function setContent(string $cacheIdentifier, $value);


    /**
     * Clear cached content
     */
    public function clearCache(): void;


}
