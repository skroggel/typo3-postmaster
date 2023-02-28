<?php
namespace Madj2k\Postmaster\Tests\Integration\ViewHelpers\Cache;

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

use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Madj2k\Postmaster\Cache\RenderCache;
use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * RenderCacheViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RenderCacheViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RenderCacheViewHelperTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/accelerator',
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster',
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \TYPO3\CMS\Fluid\View\StandaloneView|null
     */
    private ?StandaloneView $standAloneViewHelper = null;


    /**
     * @var \Madj2k\Postmaster\Cache\RenderCache|null
     */
    private ?RenderCache $renderCache = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository|null
     */
    private ?QueueMailRepository $queueMailRepository = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                static::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->renderCache = $this->objectManager->get(RenderCache::class);
        $this->renderCache->clearCache();

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => static::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itSetsCache ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given an array with one marker is set
         * Given this marker is a queueMail-object
         * Given this queueMail-object is persisted
         * Given there are no nonCachedMarkers
         * When the ViewHelper is rendered
         * Then the string is cached
         * Then the string cached is equal to the original rendered string
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findbyUid(10);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'queueMail' => $queueMail,
            ]
        );

        $content = $this->standAloneViewHelper->render();

        $cacheIdentifier = $this->renderCache->getIdentifier($queueMail, true);
        $cachedContent = $this->renderCache->getContent($cacheIdentifier);
        self::assertNotEmpty($cachedContent);
        self::assertStringContainsString('This is to be cached.', $content);
        self::assertStringContainsString('This is to be cached.', $cachedContent);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itDoesNotCachesNonCachedMarkers ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given an array with fhree markers is set
         * Given the first marker is a queueMail-object
         * Given the queueMail-object is persisted
         * Given the last two markers are strings
         * Given one of this markers is passed as nonCachedMarker
         * When the ViewHelper is rendered
         * Then the string is returned with all markers replaced
         * Then the string is cached
         * Then the normal markers are cached
         * Then the nonCachedMarker is are not cached
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findbyUid(20);

        $timestampStart = microtime(true);
        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'queueMail' => $queueMail,
                'array' => range(0, 100000),
                'test1' => $timestampStart,
                'test2' => $timestampStart,
            ]
        );

        $content = $this->standAloneViewHelper->render();
        self::assertStringContainsString('This is to be cached.', $content);
        self::assertStringContainsString('"test1"=' . $timestampStart, $content);
        self::assertStringContainsString('"test2"=' . $timestampStart, $content);

        $cacheIdentifier = $this->renderCache->getIdentifier($queueMail, true);
        $cachedContent = $this->renderCache->getContent($cacheIdentifier);
        self::assertNotEmpty($cachedContent);
        self::assertStringContainsString('This is to be cached.', $cachedContent);
        self::assertStringNotContainsString('{test1}', $cachedContent);
        self::assertStringContainsString('###test2###', $cachedContent);

    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersFasterWithSetCache ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given an array with two markers is set
         * Given the first marker is a queueMail-object
         * Given the queueMail-object is persisted
         * Given the second marker is an array with 100.000 entries
         * Given the template with the ViewHelper is rendered twice
         * When the ViewHelper is rendered
         * Then the string is cached
         * Then the second render-call is much faster than the first
         */
        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findbyUid(30);

        $timestampStart = microtime(true);
        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assignMultiple(
            [
                'queueMail' => $queueMail,
                'array' => range(0, 100000),
            ]
        );

        $this->standAloneViewHelper->render();
        $durationFirst = microtime(true) - $timestampStart;

        $cacheIdentifier = $this->renderCache->getIdentifier($queueMail, true);
        $cachedContent = $this->renderCache->getContent($cacheIdentifier);
        self::assertNotEmpty($cachedContent);

        $timestampStart = microtime(true);
        $this->standAloneViewHelper->render();
        $durationSecond = microtime(true) - $timestampStart;

        self::assertLessThan($durationFirst - 0.2, $durationSecond);
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
        $this->renderCache->clearCache();
    }








}
