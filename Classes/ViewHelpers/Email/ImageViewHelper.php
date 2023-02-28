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

use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * Class ImageViewHelper
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ImageViewHelper extends \TYPO3\CMS\Fluid\ViewHelpers\ImageViewHelper
{

    /**
     * Resizes a given image (if required) and renders the respective img tag
     *
     * @see https://docs.typo3.org/typo3cms/TyposcriptReference/ContentObjects/Image/
     * @throws \TYPO3Fluid\Fluid\Core\ViewHelper\Exception
     * @return string Rendered tag
     */
    public function render(): string
    {

        $return = '';

        try {

            // force non-absolute path!
            $this->arguments['absolute'] = 0;

            $result = parent::render();
            $return = $this->replacePath($result);

        } catch (\Exception $e) {

            // try fallback without rendering!
            try {

                // force non-absolute path!
                $this->arguments['absolute'] = 0;

                $image = $this->imageService->getImage($this->arguments['src'], $this->arguments['image'], $this->arguments['treatIdAsReference']);
                $imageUri = $this->imageService->getImageUri($image, $this->arguments['absolute']);

                $this->tag->addAttribute('src', $imageUri);
                $this->tag->addAttribute('width', intval($this->arguments['width']));

                $styleAttribute = $this->tag->getAttribute('style');
                if (strrpos($styleAttribute, ';') !== (strlen($styleAttribute) -1)) {
                    $styleAttribute .= ';';
                }
                if ($this->arguments['maxHeight']) {
                    $styleAttribute .= 'max-height:' . intval($this->arguments['maxHeight']) .'px;';
                }
                if ($this->arguments['maxWidth']) {
                    $styleAttribute .= 'max-width:' . intval($this->arguments['maxWidth']) .'px;';
                }
                if ($styleAttribute) {
                    $this->tag->addAttribute('style', $styleAttribute );
                }

                $alt = $image->getProperty('alternative');
                $title = $image->getProperty('title');

                // The alt-attribute is mandatory to have valid html-code, therefore add it even if it is empty
                if (empty($this->arguments['alt'])) {
                    $this->tag->addAttribute('alt', $alt);
                }
                if (empty($this->arguments['title']) && $title) {
                    $this->tag->addAttribute('title', $title);
                }

                $result = $this->replacePath($this->tag->render());
                $this->getLogger()->log(
                    LogLevel::WARNING,
                    sprintf(
                        'Using fallback image rendering for image %s. Result: %s',
                        $imageUri,
                        $result
                    )
                );

                $return = $result;

            } catch (\Exception $e) {
                $this->getLogger()->log(
                    LogLevel::ERROR,
                    'Unable to render image.'
                );
            }
        }

        return $return;
    }


    /**
     * Replaces relative paths and absolute paths to server-root
     *
     * @param string $tag
     * @return string
     */
    protected function replacePath (string $tag): string
    {

        /* @todo Check if Environment-variables are still valid in TYPO3 8.7 and upwards! */
        $replacePaths = [
            GeneralUtility::getIndpEnv('TYPO3_SITE_PATH'),
            $_SERVER['TYPO3_PATH_ROOT'] .'/'
        ];

        foreach ($replacePaths as $replacePath) {
            $tag = preg_replace(
                '/(src|href)="' . str_replace('/', '\/', $replacePath) . '([^"]+)"/',
                '$1="' . '/$2"',
                $tag
            );
        }

        return $tag;
    }


    /**
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        return GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
    }
}

