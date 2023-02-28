<?php
namespace Madj2k\Postmaster\Tests\Integration\ViewHelpers\Email\Replace;

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
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * RteLinksViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RteLinksViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RteLinksViewHelperTest/Fixtures';


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

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Global.xml');
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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersLinksWithStyles ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given we have a plaintext section
         * Given we have a html-section
         * Given each section has a part with the old link-tag for typolink
         * Given each section has a part with the new a-tag for typolink
         * Given each section has an additional style-attribute given
         * Given each section contains links to existing internal pages
         * Given each section contains links to external pages
         * Given each section contains links to existing files
         * When the ViewHelper is rendered
         * Then all links are rendered with absolute urls
         * Then the plaintext-links are rendered without settings from the additional parameters
         * Then the plaintext-links are enclosed by brackets and the link text is placed before the link
         * Then the html-links include all additional parameters given as attributes
         * Then the existing style-attribute of the html-links using the old link-tag for typolink is replaced by the styles given via viewHelper-attribute
         * Then the existing style-attribute of the html-links using the new a-tag for typolink is extended by the styles given via viewHelper-attribute
         */

        $this->standAloneViewHelper->setTemplate('Check10.html');

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');
        $result = $this->standAloneViewHelper->render();

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersLinksWithoutStyles ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given we have a plaintext section
         * Given we have a html-section
         * Given each section has a part with the old link-tag for typolink
         * Given each section has a part with the new a-tag for typolink
         * Given no section has an additional style-attribute given
         * Given each section contains links to existing internal pages
         * Given each section contains links to external pages
         * Given each section contains links to existing files
         * When the ViewHelper is rendered
         * Then all links are rendered with absolute urls
         * Then the plaintext-links are rendered without settings from the additional parameters
         * Then the plaintext-links are enclosed by brackets and the link text is placed before the link
         * Then the html-links include all additional parameters given as attributes
         * Then the existing style-attribute of the html-links using the old link-tag for typolink are removed
         * Then the existing style-attribute of the html-links using the new a-tag for typolink are keept as they were
         */

        $this->standAloneViewHelper->setTemplate('Check20.html');

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check20.txt');
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
