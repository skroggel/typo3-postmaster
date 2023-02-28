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

use TYPO3\CMS\Core\Log\LogLevel;

/**
 * RenderCache
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RenderCache extends AbstractCache
{


    /**
     * Returns identifier for cache
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @param boolean $isPlaintext
     * @param string $additionalIdentifier
     * @return string
     * @throws \Madj2k\Postmaster\Exception
     */
    public function getIdentifier(
        \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail,
        bool $isPlaintext = false,
        string $additionalIdentifier = ''
    ): string {

        if ($queueMail->_isNew()) {
            throw new \Madj2k\Postmaster\Exception (
                'The queueMail-object has to be persisted before it can be used.',
                1634648093
            );
        }

        return 'ViewHelperCache_' . intval($queueMail->getUid()) . '_' . ($isPlaintext ? 'plaintext' : 'html') . '_' . sha1($additionalIdentifier);
    }


    /**
     * Replaces marker in content
     *
     * @param string $content
     * @param array $marker
     * @return string
     */
    public function replaceMarkers (string $content, array $marker = []): string
    {

        // replace marker
        foreach ($marker as $key => $value) {

            $contentBefore = $content;
            $content = str_replace('---' . $key . '---', $value, $content);
            $content = str_replace('###' . $key . '###', $value, $content);

            if ($contentBefore != $content) {
                $this->logger->log(
                    LogLevel::DEBUG,
                    sprintf(
                        'ViewHelperCache replaced key "%s" with value "%s".',
                        $key,
                        str_replace("\n", '', print_r($value, true))
                    )
                );
            }
        }

        return $content;
    }


}
