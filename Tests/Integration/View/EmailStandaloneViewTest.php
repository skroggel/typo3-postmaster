<?php
namespace Madj2k\Postmaster\Tests\Integration\View;

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
use Madj2k\CoreExtended\Domain\Repository\PagesRepository;
use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\View\EmailStandaloneView;
use TYPO3\CMS\Core\Core\SystemEnvironmentBuilder;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Service\EnvironmentService;

/**
 * EmailStandaloneViewTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class EmailStandaloneViewTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/EmailStandaloneViewTest/Fixtures';


    /**
     * @var string[]
     */
    protected $testExtensionsToLoad = [
        'typo3conf/ext/accelerator',
        'typo3conf/ext/core_extended',
        'typo3conf/ext/postmaster'
    ];

    /**
     * @var string[]
     */
    protected $coreExtensionsToLoad = [];


    /**
     * @var \Madj2k\Postmaster\View\EmailStandaloneView|null
     */
    private ?EmailStandaloneView $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager;


    /**
     * @var \Madj2k\CoreExtended\Domain\Repository\PagesRepository|null
     */
    private ?PagesRepository $pagesRepository;


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
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(EmailStandaloneView::class);
        $this->pagesRepository = $this->objectManager->get(PagesRepository::class);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function constructLoadsConfigurationOfGivenPage ()
    {

        /**
        * Scenario:
        *
        * Given a rootpage with the configuration for the mailer extension
        * Given this rootpage has a subpage
        * Given this subpage as parameter
        * When the object is instanced
        * Then a configuration array is loaded
        * Then the three configuration types of the rootpage of the given subpage are loaded
        */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 11);
        $settings = $this->subject->getSettings();

        self::assertIsArray($settings);

        self::assertEquals(
            'EXT:postmaster/Tests/Integration/Service/Fixtures/Frontend/Check10/Layouts/',
            $settings['view']['layoutRootPaths'][1]
        );

        self::assertEquals(
            1010,
            $settings['persistence']['storagePid']
        );

        self::assertEquals(
            1010,
            $settings['settings']['redirectPid']
        );
    }


    /**
     * @test
     * @throws \Exception
     */
    public function constructLoadsFallbackConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a rootpage with the configuration for the mailer extension
         * Given no pid is passed as argument
         * When the object is instanced
         * Then a configuration array is loaded
         * Then a three configuration types of the rootpage of the default page are loaded
         */

        $this->subject = $this->objectManager->get(EmailStandaloneView::class);
        $settings = $this->subject->getSettings();

        self::assertIsArray($settings);

        self::assertEquals(
            'EXT:postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Layouts/',
            $settings['view']['layoutRootPaths'][1]
        );

        self::assertEquals(
            9999,
            $settings['persistence']['storagePid']
        );

        self::assertEquals(
            9999,
            $settings['settings']['redirectPid']
        );

    }


    /**
     * @test
     * @throws \Exception
     */
    public function constructSetsSettingsPidToGivenPage ()
    {

        /**
         * Scenario:
         *
         * Given a rootpage with the configuration for the mailer extension
         * Given this rootpage has a subpage
         * Given this subpage as parameter
         * When the object is instanced
         * Then the given pid is set as settingsPid
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 11);

        self::assertEquals(11, $this->subject->getSettingsPid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function constructSetsViewPathsAccoringToConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a rootpage with the configuration for the mailer extension
         * Given this rootpage has a subpage
         * Given this subpage as parameter
         * When the object is instanced
         * Then the layoutRootPaths are set according to configuration
         * Then the partialRootPaths are set according to configuration
         * Then the templateRootPaths are set according to configuration
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check10.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 11);

        $expected = [
            'layout' => [
                0 => 'typo3conf/ext/postmaster/Resources/Private/Layouts/',
                1 => 'typo3conf/ext/postmaster/Tests/Integration/Service/Fixtures/Frontend/Check10/Layouts/'
            ],
            'partial' => [
                0 => 'typo3conf/ext/postmaster/Resources/Private/Partials/',
                1 => 'typo3conf/ext/postmaster/Tests/Integration/Service/Fixtures/Frontend/Check10/Partials/'
            ],
            'template' => [
                0 => 'typo3conf/ext/postmaster/Resources/Private/Templates/',
                1 => 'typo3conf/ext/postmaster/Tests/Integration/Service/Fixtures/Frontend/Check10/Templates/'
            ],
        ];

        $result = $this->subject->getLayoutRootPaths();
        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertStringEndsWith($expected['layout'][0], $result[0]);
        self::assertStringEndsWith($expected['layout'][1], $result[1]);

        $result = $this->subject->getPartialRootPaths();
        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertStringEndsWith($expected['partial'][0], $result[0]);
        self::assertStringEndsWith($expected['partial'][1], $result[1]);

        $result = $this->subject->getTemplateRootPaths();
        self::assertIsArray( $result);
        self::assertCount(2, $result);
        self::assertStringEndsWith($expected['template'][0], $result[0]);
        self::assertStringEndsWith($expected['template'][1], $result[1]);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathDoesNotChangeRelativePaths ()
    {

        /**
         * Scenario:
         *
         * Given a relative path without prefix
         * When the method is called
         * Then the path is returned unchanged
         * Then the trailing slash is removed
         */

        $path = 'fileadmin/stuff/Images/';
        $expected = 'fileadmin/stuff/Images';
        self::assertEquals($expected, $this->subject->getRelativePath($path));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathWorksOnValidPrefixOnly ()
    {

        /**
         * Scenario:
         *
         * Given a path with an invalid prefix
         * When the method is called
         * Then the path is returned unchanged
         * Then the trailing slash is removed
         */
        $path = 'EXI:postmaster/postmaster/Resources/Public/Images/';
        $expected = 'EXI:postmaster/postmaster/Resources/Public/Images';
        self::assertEquals($expected, $this->subject->getRelativePath($path));
    }
    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathWorksForLoadedExtensionsOnly ()
    {

        /**
         * Scenario:
         *
         * Given a path with a valid prefix
         * Given that paths references an unloaded extension
         * When the method is called
         * Then the path is returned unchanged
         * Then the trailing slash is removed
         */
        $path = 'EXT:failed/postmaster/Resources/Public/Images/';
        $expected = 'EXT:failed/postmaster/Resources/Public/Images';
        self::assertEquals($expected, $this->subject->getRelativePath($path));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function getRelativePathReturnsRelativePath ()
    {

        /**
         * Scenario:
         *
         * Given a path with a valid prefix
         * Given that paths references a loaded extension
         * When the method is called
         * Then the path is returned as relative path
         * Then the trailing slash is removed
         */
        $path = 'EXT:postmaster/postmaster/Resources/Public/Images/';
        $expected = 'typo3conf/ext/postmaster/postmaster/Resources/Public/Images';
        self::assertEquals($expected, $this->subject->getRelativePath($path));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getBaseUrlReturnsBaseUrlBasedOnConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then a full url to the image-path is returned
         * Then the trailing slash is removed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            20,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 20);


        $expected = 'http://www.example.de';
        self::assertEquals($expected, $this->subject->getBaseUrl());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getBaseUrlImagesReturnsBaseUrlForImagesBasedOnConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then a full url to the image-path is returned
         * Then the trailing slash is removed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            20,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 20);

        $expected = 'http://www.example.de/typo3conf/ext/postmaster/Resources/Public/Images';
        self::assertEquals($expected, $this->subject->getBaseUrlImages());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getLogoUrlReturnsUrlBasedOnConfiguration ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then a full url to the logo-path is returned
         * Then the trailing slash is removed
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        $this->setUpFrontendRootPage(
            20,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check20.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 20);

        $expected = 'http://www.example.de/typo3conf/ext/postmaster/Resources/Public/Images/logo.png';
        self::assertEquals($expected, $this->subject->getLogoUrl());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addLayoutPathsMergesGivenPathsToExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration with two configured layoutPaths for the view
         * Given two further layoutPaths for the view
         * When the method is called
         * Then four layoutPaths exist
         * Then the further layoutPaths are added after the existing ones
         */

        $paths = [
            0 => 'EXT:postmaster/Tests/Integration/Service/New100/Layouts/',
            1 => 'EXT:postmaster/Tests/Integration/Service/New200/Layouts/'
        ];

        $expected = [
            0 => 'typo3conf/ext/postmaster/Resources/Private/Layouts/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Layouts/',
            2 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New100/Layouts/',
            3 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New200/Layouts/'
        ];

        $this->subject->addLayoutRootPaths($paths);
        $result = $this->subject->getLayoutRootPaths();

        self::assertCount(4, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);
        self::assertStringEndsWith($expected[2], $result[2]);
        self::assertStringEndsWith($expected[3], $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addLayoutPathsSetsGivenPathsWhenNoExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given no valid configuration for layoutPaths for the view
         * Given two further layoutPaths for the view
         * When the method is called
         * Then four layoutPaths exist
         * Then the further layoutPaths are added
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $this->setUpFrontendRootPage(
            30,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 30);

        $paths = [
            0 => 'EXT:postmaster/Tests/Integration/Service/New100/Layouts/',
            1 => 'EXT:postmaster/Tests/Integration/Service/New200/Layouts/'
        ];

        $expected = [
            0 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New100/Layouts/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New200/Layouts/'
        ];

        $this->subject->addLayoutRootPaths($paths);
        $result = $this->subject->getLayoutRootPaths();

        self::assertCount(2, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addPartialPathsMergesGivenPathsToExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration with two configured partialPaths for the view
         * Given two further partialPaths for the view
         * When the method is called
         * Then four partialPaths exist
         * Then the further partialPaths are added after the existing ones
         */

        $paths = [
            0 => 'EXT:postmaster/Tests/Integration/Service/New100/Partials/',
            1 => 'EXT:postmaster/Tests/Integration/Service/New200/Partials/'
        ];

        $expected = [
            0 => 'typo3conf/ext/postmaster/Resources/Private/Partials/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Partials/',
            2 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New100/Partials/',
            3 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New200/Partials/'
        ];

        $this->subject->addPartialRootPaths($paths);
        $result = $this->subject->getPartialRootPaths();

        self::assertCount(4, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);
        self::assertStringEndsWith($expected[2], $result[2]);
        self::assertStringEndsWith($expected[3], $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addPartialPathsSetsGivenPathsWhenNoExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given no a valid configuration for partialPaths for the view
         * Given two further partialPaths for the view
         * When the method is called
         * Then two partialPaths exist
         * Then the further partialPaths are added
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $this->setUpFrontendRootPage(
            30,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 30);

        $paths = [
            0 => 'EXT:postmaster/Tests/Integration/Service/New100/Partials/',
            1 => 'EXT:postmaster/Tests/Integration/Service/New200/Partials/'
        ];

        $expected = [
            0 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New100/Partials/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New200/Partials/'
        ];

        $this->subject->addPartialRootPaths($paths);
        $result = $this->subject->getPartialRootPaths();

        self::assertCount(2, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function addTemplatePathsMergesGivenPathsToExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration with two configured templatePaths for the view
         * Given two further templatePaths for the view
         * When the method is called
         * Then four templatePaths exist
         * Then the further templatePaths are added after the existing ones
         */

        $paths = [
            0 => 'EXT:postmaster/Tests/Integration/Service/New100/Templates/',
            1 => 'EXT:postmaster/Tests/Integration/Service/New200/Templates/'
        ];

        $expected = [
            0 => 'typo3conf/ext/postmaster/Resources/Private/Templates/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/',
            2 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New100/Templates/',
            3 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New200/Templates/'
        ];

        $this->subject->addTemplateRootPaths($paths);
        $result = $this->subject->getTemplateRootPaths();

        self::assertCount(4, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);
        self::assertStringEndsWith($expected[2], $result[2]);
        self::assertStringEndsWith($expected[3], $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function addTemplatePathsSetsGivenPathsWhenNoExistingOnes ()
    {

        /**
         * Scenario:
         *
         * Given no a valid configuration for templatePaths for the view
         * Given two further templatePaths for the view
         * When the method is called
         * Then two templatePaths exist
         * Then the further templatePaths are added
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $this->setUpFrontendRootPage(
            30,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Check30.typoscript',
            ]
        );

        $this->subject = $this->objectManager->get(EmailStandaloneView::class, 30);

        $paths = [
            0 => 'EXT:postmaster/Tests/Integration/Service/New100/Templates/',
            1 => 'EXT:postmaster/Tests/Integration/Service/New200/Templates/'
        ];

        $expected = [
            0 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New100/Templates/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/Service/New200/Templates/'
        ];

        $this->subject->addTemplateRootPaths($paths);
        $result = $this->subject->getTemplateRootPaths();

        self::assertCount(2, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateAsAction ()
    {

        /**
         * Scenario:
         *
         * Given a controller action as template
         * When the method is called
         * Then no paths as added to the templateRootPaths
         * Then the controller action is set as template
         */

        $expected = $this->subject->getTemplateRootPaths();
        $this->subject->setTemplate('test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        self::assertCount(2, $resultingPaths);
        self::assertEquals($expected, $resultingPaths);
        self::assertEquals('test', $this->subject->getRenderingContext()->getControllerAction());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateAsFullPath()
    {

        /**
         * Scenario:
         *
         * Given a full path to the template beginning with EXT-keyword
         * Given no file extension is specified for the template
         * When the method is called
         * Then no paths are added to the templateRootPath
         * Then the resolved path is set for the template
         * Then the default file extension is used
         */

        $this->subject->setTemplate(
            'EXT:postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test'
        );

        $resultingPaths = $this->subject->getTemplateRootPaths();
        self::assertCount(2, $resultingPaths);
        self::assertStringEndsWith(
            'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.html',
            $this->subject->getTemplatePathAndFilename()
        );
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateAsFullPathWithGivenFileExtension()
    {

        /**
         * Scenario:
         *
         * Given a full path to the template beginning with EXT-keyword
         * Given a file extension is specified for the template
         * When the method is called
         * Then no paths as added to the templateRootPath
         * Then the resolved path is set for the template
         * Then the file extension specified is used
         */

        $this->subject->setTemplate(
            'EXT:postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.test'
        );

        $resultingPaths = $this->subject->getTemplateRootPaths();
        self::assertCount(2, $resultingPaths);
        self::assertStringEndsWith(
            'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.test',
            $this->subject->getTemplatePathAndFilename()
        );
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsTemplateRootPathsAndAction ()
    {

        /**
         * Scenario:
         *
         * Given an existing relative path as template
         * When the method is called
         * Then the given relative path is added to the templateRootPaths
         * Then the given relative path is resolved to the absolute path
         * Then the last part of the relative path is set as template
         */
        $this->subject->setTemplate('Testing/test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        self::assertCount(3, $resultingPaths);
        self::assertStringEndsWith(
            'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/',
            $resultingPaths[2]
        );
        self::assertEquals('test', $this->subject->getRenderingContext()->getControllerAction());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateAddsNoTemplateRootPathsAndActionOnNonExtistingPath ()
    {

        /**
         * Scenario:
         *
         * Given an non-existing relative path as template
         * When the method is called
         * Then the given nothing is added to the templateRootPaths
         * Then the last part of the relative path is set as template
         */
        $this->subject->setTemplate('Testingxyz/test');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        self::assertCount(2, $resultingPaths);
        self::assertEquals('test', $this->subject->getRenderingContext()->getControllerAction());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateTypesThrowsExceptionIfNoQueueMailSet ()
    {

        /**
         * Scenario:
         *
         * Given a valid type-string
         * Given no queueMail is set
         * When the method is called
         * Then an exception is thrown
         * Then the exception has the code 1633088149
         */
        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1633088149);

        $this->subject->setTemplateType('plaintext');
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateTypesThrowsExceptionIfInvalidType ()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object has been set to subject  before
         * Given an invalid type-string
         * When the method is called
         * Then an exception is thrown
         * Then the exception has the code 1633088157
         */
        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1633088157);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('test');
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setTemplateTypesSetsTemplateAndTypeProperty ()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object has been set to subject before
         * Given the queueMail-object contains a full path to the template beginning with EXT-keyword in the plaintextTemplate-attribute
         * Given in that full path no file-extension is specified for the template
         * Given the valid type-string with value "plaintext"
         * When the method is called
         * Then no paths are added to the templateRootPath
         * Then the resolved path is set as the template
         * Then the default file-extension is used
         * Then the templateType-property of subject is set to the given type-string
         * Then the templateType-property is transformed to lower case letters only
         */

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $queueMail->setPlaintextTemplate(
            'EXT:postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test'
        );

        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('plaInTeXt');

        $resultingPaths = $this->subject->getTemplateRootPaths();
        self::assertCount(2, $resultingPaths);
        self::assertStringEndsWith(
            'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/Testing/Test.html',
            $this->subject->getTemplatePathAndFilename()
        );
        self::assertEquals('plaintext', $this->subject->getTemplateType());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleExplodesValues()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a key with a string
         * Given that array contains a second key with a Page-Object
         * When the method is called
         * Then the Page-Object in the values is exploded
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\CoreExtended\Domain\Model\Pages $entityOne */
        $entityOne = $this->pagesRepository->findByIdentifier(1);
        $expected = [
            'hello' => 'string',
            'page' => $entityOne
        ];

        $values = [
            'hello' => 'string',
            'page' => 'TX_ACCELERATOR_NAMESPACES Madj2k\CoreExtended\Domain\Model\Pages:1'
        ];

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();
        unset($variables['settings']);

        self::assertEquals($expected, $variables);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsSettings()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no settings key
         * When the method is called
         * Then the existing keys are kept
         * Then a settings-key is added to the values
         * Then this settings-key contains an array
         * Then this array equals the normal settings loaded for the view
         */

        $values = [
            'hello' => 'string',
            'page' => 'TX_ACCELERATOR_NAMESPACES Madj2k\CoreExtended\Domain\Model\Pages:1'
        ];

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        $settings = $this->subject->getSettings();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('page', $variables);
        self::assertArrayHasKey('settings', $variables);
        self::assertIsArray( $variables['settings']);
        self::assertEquals($settings['settings'], $variables['settings']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleMergesSettings()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a settings-key
         * Given that settings-key contains a multidimensional array
         * When the method is called
         * Then the existing keys are kept
         * Then the existing settings-array is merged with the settings-array of Postmaster
         */

        $values = [
            'hello' => 'string',
            'page' => 'TX_ACCELERATOR_NAMESPACES Madj2k\CoreExtended\Domain\Model\Pages:1',
            'settings' => [
                'test' => [
                    'testen' => 'deep'
                ]
            ]
        ];

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        $settings = $this->subject->getSettings();
        $expected = array_merge($values['settings'], $settings['settings']);

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('page', $variables);
        self::assertArrayHasKey('settings', $variables);
        self::assertIsArray( $variables['settings']);
        self::assertEquals($expected, $variables['settings']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleMergesSettingsOnMultipleCalls()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a settings-key
         * Given that settings-key contains a multidimensional array
         * Given the method was called before
         * When the method is called again witch a different settings-array
         * Then the existing keys are kept
         * Then the existing settings-array is merged with the new settings-array
         * Then both settings-arrays are merged with the settings-array of Postmaster
         */
        $values = [
            'hello' => 'string',
            'settings' => [
                'test' => [
                    'test1' => 'deep1',
                    'testen1' => 'deep1'
                ]
            ]
        ];

        $values2 = [
            'settings' => [
                'test2' => [
                    'testen2' => 'deep2',
                ],
                'test' => [
                    'testen2' => 'deep2'
                ]
            ]
        ];

        $this->subject->assignMultiple($values);
        $this->subject->assignMultiple($values2);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        $expected = [
            'test'  => [
                'testen2' => 'deep2'
            ],
            'privacyPid' => '{$plugin.tx_postmaster.settings.privacyPid}',
            'redirectPid' => '9999',
            'redirectDelay' => '5',
            'baseUrl' => 'http://www.example.de',
            'basePathImages' => 'EXT:postmaster/Resources/Public/Images',
            'basePathLogo' => 'EXT:postmaster/Resources/Public/Images/logo.png',
            'arraySettings' =>[
                'test'=> 'Wonderful!'
            ],
            'test2'=> [
                'testen2' => 'deep2'
            ],
            'counterPixelPid' => '9998'
        ];

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('settings', $variables);
        self::assertIsArray( $variables['settings']);
        self::assertEquals($expected, $variables['settings']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleKeepsSettingsOnMultipleCalls()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a settings-key
         * Given that settings-key contains a multidimensional array
         * Given the method was called before
         * When the method is called again without a settings-array
         * Then the existing keys are kept
         * Then the existing settings-array is kept
         * Then the settings-arrays is merged with the settings-array of Postmaster
         */
        $values = [
            'hello' => 'string',
            'settings' => [
                'test' => [
                    'test1' => 'deep1',
                    'testen1' => 'deep1'
                ]
            ]
        ];


        $this->subject->assignMultiple($values);
        $this->subject->assignMultiple([]);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        $expected = [
            'test'  => [
                'test1' => 'deep1',
                'testen1' => 'deep1'
            ],
            'privacyPid' => '{$plugin.tx_postmaster.settings.privacyPid}',
            'redirectPid' => '9999',
            'redirectDelay' => '5',
            'baseUrl' => 'http://www.example.de',
            'basePathImages' => 'EXT:postmaster/Resources/Public/Images',
            'basePathLogo' => 'EXT:postmaster/Resources/Public/Images/logo.png',
            'arraySettings' =>[
                'test'=> 'Wonderful!'
            ],
            'counterPixelPid' => '9998'
        ];

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('settings', $variables);
        self::assertIsArray( $variables['settings']);
        self::assertEquals($expected, $variables['settings']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsQueueMail()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no queueMail-key
         * Given a queueMail-object has been set to the subject via setQueueMail() before
         * When the method is called
         * Then the existing keys are kept
         * Then a queueMail-key is added to the values
         * Then this queueMail-key contains an queueMail-object
         */

        $values = [
            'hello' => 'string',
        ];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('queueMail', $variables);
        self::assertInstanceOf(QueueMail::class , $variables['queueMail']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleOverridesQueueMailInValues()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a queueMail-key
         * Given that queueMail-key contains a queueMail-object A
         * Given a queueMail-object B has been set to the subject via setQueueMail() before
         * When the method is called
         * Then the existing keys are kept
         * Then a queueMail-key exists
         * Then this queueMail-key contains an queueMail-object
         * Then the queueMail-key is overridden by queueMail B
         */

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $queueMail->setPid(1);

        $values = [
            'hello' => 'string',
            'queueMail' => $queueMail
        ];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail2 */
        $queueMail2 = new QueueMail();
        $queueMail2->setPid(2);

        $this->subject->setQueueMail($queueMail2);
        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('queueMail', $variables);
        self::assertInstanceOf(QueueMail::class , $variables['queueMail']);
        self::assertEquals(2, $variables['queueMail']->getPid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsQueueRecipient()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no queueRecipient-key
         * Given a queueRecipient-object has been set to the subject via setQueueRecipient() before
         * When the method is called
         * Then the existing keys are kept
         * Then a queueRecipient-key is added to the values
         * Then this queueRecipient-key contains an queueMail-object
         */

        $values = [
            'hello' => 'string',
        ];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = new QueueRecipient();
        $this->subject->setQueueRecipient($queueRecipient);

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('queueRecipient', $variables);
        self::assertInstanceOf(QueueRecipient::class , $variables['queueRecipient']);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleOverridesQueueRecipientInValues()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a queueRecipient-key
         * Given that queueRecipient-key contains a queueRecipient-object A
         * Given a queueRecipient-object B has been set to the subject via setQueueRecipient() before
         * When the method is called
         * Then the existing keys are kept
         * Then a queueRecipient-key exists
         * Then this queueRecipient-key contains an queueRecipient-object
         * Then the queueRecipient-key is overridden by queueRecipient B
         */

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = new QueueRecipient();
        $queueRecipient->setPid(1);
        $values = [
            'hello' => 'string',
            'queueRecipient' => $queueRecipient
        ];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient2 = new QueueRecipient();
        $queueRecipient2->setPid(2);
        $this->subject->setQueueRecipient($queueRecipient2);

        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('queueRecipient', $variables);
        self::assertInstanceOf(QueueRecipient::class , $variables['queueRecipient']);
        self::assertEquals(2, $variables['queueRecipient']->getPid());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleAddsMailTypeAndTemplateType()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains no mailType-key
         * Given that array contains no templateType-key
         * Given a queueMail-object has been set to the subject via setQueueMail() before
         * Given setTemplateType() of the subject has been called before successfully
         * When the method is called
         * Then the existing keys are kept
         * Then a mailType-key is added to the values
         * Then this mailType-key contains the type that was given to setTemplateType()
         * Then the first letter of the mailType-key is uppercase
         * Then a templateType-key is added to the values
         * Then this templateType-key contains the type that was given to setTemplateType()
         * Then the first letter of the templateType-key is uppercase
         */
        $values = [
            'hello' => 'string',
        ];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('plaintext');
        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('mailType', $variables);
        self::assertEquals('Plaintext', $variables['mailType']);
        self::assertArrayHasKey('templateType', $variables);
        self::assertEquals('Plaintext', $variables['templateType']);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function assignMultipleOverridesMailTypeAndTemplateTypeInValues()
    {

        /**
         * Scenario:
         *
         * Given an array of values
         * Given that array contains a mailType-key
         * Given that array contains a templateType-key
         * Given a queueMail-object has been set to the subject via setQueueMail() before
         * Given setTemplateType() of the subject has been called before successfully
         * When the method is called
         * Then the existing keys are kept
         * Then a mailTypeType-key exists in the values
         * Then the mailType-key contains the type that was given to setTemplateType()
         * Then the first letter of the mailType-key is uppercase
         * Then a templateType-key exists in the the values
         * Then this templateType-key contains the type that was given to setTemplateType()
         * Then the first letter of the templateType-key is uppercase
         */
        $values = [
            'hello' => 'string',
            'mailType' => 'html',
            'templateType' => 'html'
        ];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $this->subject->setQueueMail($queueMail);
        $this->subject->setTemplateType('plaintext');
        $this->subject->assignMultiple($values);

        $variableProvider = $this->subject->getRenderingContext()->getVariableProvider();
        $variables = $variableProvider->getAll();

        self::assertArrayHasKey('hello', $variables);
        self::assertArrayHasKey('mailType', $variables);
        self::assertEquals('Plaintext', $variables['mailType']);
        self::assertArrayHasKey('templateType', $variables);
        self::assertEquals('Plaintext', $variables['templateType']);


    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function renderReplacesPathMarkers()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then the baseUrl-marker is replaced
         * Then the baseUrlImages-marker is replaced
         * Then the baseUrlLogo-marker is replaced
         * Then the logoUrl-marker is replaced
         */
        $this->subject->setTemplate('Testing/Check40.html');
        $result = $this->subject->render();

        self::assertStringContainsString('baseUrl: http://www.example.de', $result);
        self::assertStringContainsString('baseUrlImages: http://www.example.de/typo3conf/ext/postmaster/Resources/Public/Images', $result);
        self::assertStringContainsString('baseUrlLogo: http://www.example.de/typo3conf/ext/postmaster/Resources/Public/Images/logo.png', $result);
        self::assertStringContainsString('logoUrl: http://www.example.de/typo3conf/ext/postmaster/Resources/Public/Images/logo.png', $result);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function renderResolvesRelativeAndAbsolutePathsToUrls()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * Given a relative path as url
         * Given a relative path as src
         * Given an absolute path as src
         * When the method is called
         * Then the relative url is resolved to an url
         * Then the relative src is resolved to an url
         * Then the absolute src is resolved to an url
         */
        $this->subject->setTemplate('Testing/Check50.html');
        $this->subject->assign('webPath', $_SERVER['TYPO3_PATH_WEB']);
        $result = $this->subject->render();


        self::assertStringContainsString('<a href="http://www.example.de/test.html">Test</a>', $result);
        self::assertStringContainsString('<img src="http://www.example.de/test.png" width="30" height="30" alt="Test"/>', $result);
        self::assertStringContainsString('<img src="http://www.example.de/fileadmin/_processed_/', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function renderHasAccessToExtensionSettings ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then the setting variables are available
         */
        $this->subject->setTemplate('Testing/Check70.html');
        $result = $this->subject->render();

        self::assertStringContainsString('Wonderful!', $result);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function renderHasAccessToAssignedSettings ()
    {

        /**
         * Scenario:
         *
         * Given a valid configuration
         * When the method is called
         * Then the setting variables are available
         */
        $this->subject->setTemplate('Testing/Check80.html');
        $this->subject->assign('settings', ['assignTest' => 'Testing Is ']);
        $result = $this->subject->render();

        self::assertStringContainsString('Testing Is Wonderful!', $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailAddsLayoutRootPaths()
    {

        /**
         * Scenario:
         *
         * Given a queueMailObject with a layoutPath-property set
         * When the method is called
         * Then the value of the layoutPath-property of the queueMailObject is added to the layoutPaths of the subject
         */

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $queueMail->setLayoutPaths(
            [
                0 => 'EXT:postmaster/Tests/Funky/New100/Layouts/',
                1 => 'EXT:postmaster/Tests/Funky/New200/Layouts/'
            ]
        );

        $expected = [
            0 => 'typo3conf/ext/postmaster/Resources/Private/Layouts/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Layouts/',
            2 => 'typo3conf/ext/postmaster/Tests/Funky/New100/Layouts/',
            3 => 'typo3conf/ext/postmaster/Tests/Funky/New200/Layouts/'
        ];

        $this->subject->setQueueMail($queueMail);
        $result = $this->subject->getLayoutRootPaths();

        self::assertCount(4, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);
        self::assertStringEndsWith($expected[2], $result[2]);
        self::assertStringEndsWith($expected[3], $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailAddsTemplateRootPaths()
    {

        /**
         * Scenario:
         *
         * Given a queueMailObject with a templatePath-property set
         * When the method is called
         * Then the value of the templatePath-property of the queueMailObject is added to the layoutPaths of the subject
         */

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $queueMail->setTemplatePaths(
            [
                0 => 'EXT:postmaster/Tests/Funky/New100/Templates/',
                1 => 'EXT:postmaster/Tests/Funky/New200/Templates/'
            ]
        );

        $expected = [
            0 => 'typo3conf/ext/postmaster/Resources/Private/Templates/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Templates/',
            2 => 'typo3conf/ext/postmaster/Tests/Funky/New100/Templates/',
            3 => 'typo3conf/ext/postmaster/Tests/Funky/New200/Templates/'
        ];

        $this->subject->setQueueMail($queueMail);
        $result = $this->subject->getTemplateRootPaths();

        self::assertCount(4, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);
        self::assertStringEndsWith($expected[2], $result[2]);
        self::assertStringEndsWith($expected[3], $result[3]);
    }


    /**
     * @test
     * @throws \Exception
     */
    public function setQueueMailAddsPartialRootPaths()
    {

        /**
         * Scenario:
         *
         * Given a queueMailObject with a partialPath-property set
         * When the method is called
         * Then the value of the partialPath-property of the queueMailObject is added to the partialsRootPaths of the subject
         */

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = new QueueMail();
        $queueMail->setPartialPaths(
            [
                0 => 'EXT:postmaster/Tests/Funky/New100/Partials/',
                1 => 'EXT:postmaster/Tests/Funky/New200/Partials/'
            ]
        );

        $expected = [
            0 => 'typo3conf/ext/postmaster/Resources/Private/Partials/',
            1 => 'typo3conf/ext/postmaster/Tests/Integration/View/EmailStandaloneViewTest/Fixtures/Frontend/Partials/',
            2 => 'typo3conf/ext/postmaster/Tests/Funky/New100/Partials/',
            3 => 'typo3conf/ext/postmaster/Tests/Funky/New200/Partials/'
        ];

        $this->subject->setQueueMail($queueMail);
        $result = $this->subject->getPartialRootPaths();

        self::assertCount(4, $result);
        self::assertStringEndsWith($expected[0], $result[0]);
        self::assertStringEndsWith($expected[1], $result[1]);
        self::assertStringEndsWith($expected[2], $result[2]);
        self::assertStringEndsWith($expected[3], $result[3]);
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
