<?php

namespace Madj2k\Postmaster\UriBuilder;

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
use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use TYPO3\CMS\Core\Utility\ArrayUtility;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;

/**
 * EmailUriBuilder
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * comment: implicitly tested
 */
class EmailUriBuilder extends \TYPO3\CMS\Extbase\Mvc\Web\Routing\UriBuilder
{

    /**
     * @var int
     */
    protected int $redirectPid = 0;


    /**
     * @var bool
     */
    protected bool $useRedirectLink = false;


    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    protected ?QueueMail $queueMail = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueRecipient|null
     */
    protected ?QueueRecipient $queueRecipient = null;


    /**
     * @var string
     */
    protected string $redirectLink = '';


    /**
     * @var array
     */
    protected array $settings = [];


    /**
     * Life-cycle method that is called by the DI container as soon as this object is completely built
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {

        // set url scheme based on settings
        $this->settings = $this->getSettings();

        /* @todo: guess we don't need this any more because it is overwritten by routing */
        if (
            (isset($this->settings['baseUrl']))
            || (\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('TYPO3_SSL'))
        ){
            $this->setAbsoluteUriScheme($this->getUrlScheme($this->settings['baseUrl']));
        }

        parent::initializeObject();
    }


    /**
     * Uid of the target page
     *
     * @param int $targetPageUid
     * @return self
     * @api
     */
    public function setTargetPageUid($targetPageUid): self
    {
        $this->targetPageUid = $targetPageUid;
        return $this;
    }


    /**
     * Sets useRedirectLink
     *
     * @param boolean $useRedirectLink
     * @return self
     */
    public function setUseRedirectLink(bool $useRedirectLink): self
    {
        $this->useRedirectLink = (boolean) $useRedirectLink;
        return $this;
    }


    /**
     * Gets useRedirectLink
     *
     * @return boolean
     */
    public function getUseRedirectLink(): bool
    {
        return $this->useRedirectLink;
    }


    /**
     * Sets queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return self
     */
    public function setQueueMail(QueueMail $queueMail):  self
    {
        $this->queueMail = $queueMail;
        return $this;
    }


    /**
     * Gets queueMail
     *
     * @return \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    public function getQueueMail():? QueueMail
    {
        return $this->queueMail;
    }


    /**
     * Sets queueRecipient
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @return self
     */
    public function setQueueRecipient(QueueRecipient $queueRecipient): self
    {
        $this->queueRecipient = $queueRecipient;
        return $this;
    }


    /**
     * Gets queueRecipient
     *
     * @return \Madj2k\Postmaster\Domain\Model\QueueRecipient|null
     */
    public function getQueueRecipient():? QueueRecipient
    {
        return $this->queueRecipient;
    }


    /**
     * Sets redirectLink
     *
     * @param string $redirectLink
     * @return $this the current UriBuilder to allow method chaining
     */
    public function setRedirectLink(string $redirectLink): EmailUriBuilder
    {
        $this->redirectLink = $redirectLink;
        return $this;
    }


    /**
     * Gets redirectLink
     *
     * @return string
     */
    public function getRedirectLink(): string
    {
        return $this->redirectLink;
    }


    /**
     * Sets redirectPid
     *
     * @param int $redirectPid
     * @return self
     */
    public function setRedirectPid(int $redirectPid): self
    {
        $this->redirectPid = $redirectPid;
        return $this;
    }


    /**
     * Gets $redirectLPid
     *
     * @return int
     */
    public function getRedirectPid(): int
    {
        if (!$this->redirectPid) {
            $this->redirectPid = intval($this->settings['redirectPid']);
        }
        return $this->redirectPid;
    }


    /**
     * Creates an URI used for linking to an Extbase action.
     * Works in Frontend and Backend mode of TYPO3.
     *
     * @param string|null $actionName Name of the action to be called
     * @param array $controllerArguments Additional query parameters. Will be "namespaced" and merged with $this->arguments.
     * @param string|null $controllerName Name of the target controller. If not set, current ControllerName is used.
     * @param string|null $extensionName Name of the target extension, without underscores. If not set, current ExtensionName is used.
     * @param string|null $pluginName Name of the target plugin. If not set, current PluginName is used.
     * @return string the rendered URI
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @api
     * @see build()
     */
    public function uriFor(
        $actionName = null,
        $controllerArguments = [],
        $controllerName = null,
        $extensionName = null,
        $pluginName = null
    ): string {

        // kill request-calls for non-set values
        if ($actionName !== null) {
            $controllerArguments['action'] = $actionName;
        }
        if ($controllerName !== null) {
            $controllerArguments['controller'] = $controllerName;
        }

        if ($this->format !== '') {
            $controllerArguments['format'] = $this->format;
        }
        if ($this->argumentPrefix !== null) {
            $prefixedControllerArguments = [$this->argumentPrefix => $controllerArguments];
        } else {
            $pluginNamespace = $this->extensionService->getPluginNamespace($extensionName, $pluginName);
            $prefixedControllerArguments = [$pluginNamespace => $controllerArguments];
        }

        ArrayUtility::mergeRecursiveWithOverrule($this->arguments, $prefixedControllerArguments);

        // Fix since TYPO3 9: Remove cHash-param manually!
        $uri = $this->build();
        if (! $this->getUseCacheHash()) {
            $uri = preg_replace('#([&|\?]cHash=[^&]+)#', '', $uri);
        }

        return $uri;
    }


    /**
     * Builds the URI, frontend flavour
     *
     * @return string The URI
     */
    public function buildFrontendUri(): string
    {
        // Fix since TYPO3 9: Remove cHash-param manually!
        $uri = parent::buildFrontendUri();
        if (! $this->getUseCacheHash()) {
            $uri = preg_replace('#([&|\?]cHash=[^&]+)#', '', $uri);
        }

        return $uri;
    }


    /**
     * Builds the URI
     *
     * @return string The URI
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @api
     * @see buildFrontendUri()
     */
    public function build(): string
    {

        if (
            ($this->getUseRedirectLink())
            && ($this->getQueueMail())
        ) {

            if ($this->getRedirectPid()) {

                // unset redirect to avoid an infinite loop since uriFor() calls build()!
                // keep the set arguments (addition to queryString)
                //$this->reset();
                $this->setUseRedirectLink(false);

                // get url
                $url = $this->buildFrontendUri();
                if ($this->getRedirectLink()) {
                    $url = $this->getRedirectLink();
                }

                // set params
                $arguments = [
                    'tx_postmaster_tracking[url]' => $url,
                    'tx_postmaster_tracking[mid]'  => intval($this->getQueueMail()->getUid()),
                ];

                if ($this->getQueueRecipient()) {
                    $arguments['tx_postmaster_tracking[uid]']  = intval($this->getQueueRecipient()->getUid());
                }

                // never use cHash or pageType here!
                // this is a bad thing when sending from BE!
                // set all params for redirect link!
                $this->setTargetPageUid($this->getRedirectPid())
                    ->setNoCache(true)
                    ->setTargetPageType(0)
                    ->setUseCacheHash(false)
                    ->setCreateAbsoluteUri(true)
                    ->setArguments(
                        $arguments
                    );

                // generate redirect link
                $uri = $this->uriFor(
                    'redirect',
                    [],
                    'Tracking',
                    'postmaster',
                    'Tracking'
                );

                return $uri;
            }
        }

        // never use cHash here - this is a bad thing when sending from BE!
        // force absolute link and link to access-restricted pages
        $this->setUseCacheHash(false)
            ->setCreateAbsoluteUri(true)
            ->setLinkAccessRestrictedPages(true);

        return $this->buildFrontendUri();
    }


    /**
     * Get UrlScheme
     *
     * @param string $baseUrl
     * @return string
    */
    public function getUrlScheme(string $baseUrl): string
    {
        $parsedUrl = parse_url($baseUrl);
        return ($parsedUrl['scheme'] ?? 'http');
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
        return GeneralUtility::getTypoScriptConfiguration('Postmaster', $which);
    }
}
