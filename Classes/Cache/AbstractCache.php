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

use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Cache\Frontend\VariableFrontend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\PathUtility;

/**
 * AbstractCache
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
abstract class AbstractCache
{

    /**
     * @var \TYPO3\CMS\Core\Cache\Frontend\VariableFrontend|null
     */
    protected ?VariableFrontend $cache= null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * Constructor
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Core\Cache\Exception\NoSuchCacheException
     */
    public function __construct()
    {
        $this->cache = GeneralUtility::makeInstance(CacheManager::class)->getCache('postmaster');
        $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        $this->securityCheck();
    }


    /**
     * Gets the cache object
     *
     * @return \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
     */
    public function getCache(): \TYPO3\CMS\Core\Cache\Frontend\FrontendInterface
    {
        return $this->cache;
    }


    /**
     * Returns cached content
     *
     * @param string $cacheIdentifier
     * @return string
     */
    public function getContent(string $cacheIdentifier): string
    {
        if ($this->cache->has($cacheIdentifier)) {

            // get cached content
            $this->logger->log(
                LogLevel::DEBUG,
                sprintf(
                    'Getting cache for identifier "%s".',
                    $cacheIdentifier
                )
            );
            return $this->cache->get($cacheIdentifier);
        }

        return '';
    }


    /**
     * Sets cache content
     *
     * @param string $cacheIdentifier
     * @param mixed $value
     * @return void
     */
    public function setContent(string $cacheIdentifier, $value): void
    {

        $this->logger->log(
            LogLevel::DEBUG,
            sprintf(
                'Setting cache for identifier "%s".',
                $cacheIdentifier
            )
        );

        $className = strtolower(substr(strrchr(get_class($this), '\\'), 1));
        $this->cache->set(
            $cacheIdentifier,
            $value,
            array(
                'tx_postmaster_' . $className,
            ),
            86400
        );
    }


    /**
     * Clear cached content
     */
    public function clearCache(): void
    {
        $this->logger->log(LogLevel::DEBUG, 'Flushing cache');
        $this->cache->flush();
    }


    /**
     * .htaccess-based protection for SimpleFileBackend-Cache
     *
     * @return bool
     * @throws \Madj2k\Postmaster\Exception
     * @throws \Madj2k\CoreExtended\Exception
     */
    public function securityCheck(): bool
    {

        if (
            ($cacheBackend = $this->cache->getBackend())
            && ($cacheBackend instanceof \TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend)
        ){

            if (! \Madj2k\CoreExtended\Utility\GeneralUtility::protectFolder($cacheBackend->getCacheDirectory())){
                throw new \Madj2k\Postmaster\Exception('Cache directory is not secure and file for directory protection can not be written. Please fix this first');
            }

            return true;
        }

        return false;
    }

}
