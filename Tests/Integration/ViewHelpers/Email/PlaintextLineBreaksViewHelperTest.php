<?php
namespace Madj2k\Postmaster\Tests\Integration\ViewHelpers\Email;

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
use TYPO3\CMS\Fluid\View\StandaloneView;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * PlaintextLineBreaksViewHelperTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PlaintextLineBreaksViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PlaintextLineBreaksViewHelperTest/Fixtures';


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
     * @var \TYPO3\CMS\Fluid\View\StandaloneView|null
     */
    private ?StandaloneView $standAloneViewHelper = null;


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

        $this->importDataSet(self::FIXTURE_PATH .  '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->standAloneViewHelper = $this->objectManager->get(StandaloneView::class);
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
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRemovesIdents()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text without line break
         * Given the text is indented
         * When the ViewHelper is rendered
         * Then the text is returned without line breaks
         * Then the intends are removed
         */
        $this->standAloneViewHelper->setTemplate('Check10.html');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');

        self::assertEquals($expected, $this->standAloneViewHelper->render());
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersRemovesLineBreaksAndIndents ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text with line break
         * Given the text is indented
         * When the ViewHelper is rendered
         * Then the text is returned without line breaks
         * Then the intends are removed
         */
        $this->standAloneViewHelper->setTemplate('Check20.html');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check20.txt');

        self::assertEquals($expected, $this->standAloneViewHelper->render());
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAddsCustomLineBreaks ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text with line break
         * Given the text is indented
         * Given the text has a manual line break set
         * When the ViewHelper is rendered
         * Then the text is returned without normal line breaks
         * Then the intends are removed
         * Then the manual line break is replaced by a real line break
         */
        $this->standAloneViewHelper->setTemplate('Check30.html');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check30.txt');

        self::assertEquals($expected, $this->standAloneViewHelper->render());
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersTextWithLineBreakAndOptionKeepLineBreaks ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a text with a line break
         * Given the text is indented
         * Given the attribute convertLineBreaks is set to true
         * When the ViewHelper is rendered
         * Then the text is returned without normal line breaks
         * Then the intends are removed
         * Then the normal line break is replaced by \n
         */

        $this->standAloneViewHelper->setTemplate('Check40.html');
        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check40.txt');

        self::assertEquals($expected, $this->standAloneViewHelper->render());
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
