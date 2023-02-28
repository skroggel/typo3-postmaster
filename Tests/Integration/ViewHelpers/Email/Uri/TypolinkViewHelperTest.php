<?php
namespace Madj2k\Postmaster\Tests\Integration\ViewHelpers\Email\Uri;

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
use Madj2k\Postmaster\View\EmailStandaloneView;
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * TypolinkViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class TypolinkViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/TypolinkViewHelperTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster'
    ];


    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \Madj2k\Postmaster\View\EmailStandaloneView|null
     */
    private ?EmailStandaloneView $standAloneViewHelper = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {
        parent::setUp();

        $this->importDataSet(__DIR__ . '/TypolinkViewHelperTest/Fixtures/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ],
            ['example.com' => self::FIXTURE_PATH .  '/Frontend/Configuration/config.yaml']
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(EmailStandaloneView::class, 1);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersLinks ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a typolink to an external page with target, class and title
         * Given a typolink to an external page with anchor, target, class and title
         * Given a typolink to an email with target, class and title, using the old typolink-style
         * Given a typolink to an email with target, class and title, using the new typolink-style
         * Given a typolink to an internal page with target, class and title, using the old typolink-style
         * Given a typolink to an internal page with target, class and title, using the new typolink-style
         * Given a typolink to an internal page with anchor, target, class and title, using the old typolink-style
         * Given a typolink to an internal page with anchor, target, class and title, using the new typolink-style
         * Given a typolink to an existing file with anchor, target, class and title
         * When the ViewHelper is rendered
         * Then the typolink to an external page with target, class and title is returned as absolute link with target, class and title
         * Then the typolink to an external page with anchor, target, class and title is returned as absolute link with anchor, target, class and title
         * Then the typolink to an email with target, class and title, using the old typolink-style is returned as email-link with target, class and title
         * Then the typolink to an email with target, class and title, using the new typolink-style is returned as email-link with target, class and title
         * Then the typolink to an internal page with target, class and title, using the old typolink-style is returned as absolute link with target, class and title
         * Then the typolink to an internal page with target, class and title, using the new typolink-style is returned as absolute link with target, class and title
         * Then the typolink to an internal page with anchor, target, class and title, using the old typolink-style is returned as absolute link with anchor, target, class and title
         * Then the typolink to an internal page with anchor, target, class and title, using the new typolink-style is returned as absolute link with anchor, target, class and title
         * Then the typolink to an existing file with target, class and title is returned as absolute link with target, class and title
         */
        $this->standAloneViewHelper->setTemplate('Check10.html');

        $expected = file_get_contents(__DIR__ . '/TypolinkViewHelperTest/Fixtures/Expected/Check10.txt');
        $result = $this->standAloneViewHelper->render();

        self::assertEquals($expected, $result);
    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }

}
