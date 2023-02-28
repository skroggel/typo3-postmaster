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
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * RecipientSalutationViewHelperTest
 *
 * @author Maximilian Fäßler <maximilian@faesslerweb.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RecipientSalutationViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RecipientSalutationViewHelperTest/Fixtures';


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
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository|null
     */
    private ?QueueRecipientRepository $queueRecipientRepository = null;


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
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

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
    public function itRendersMaleSalutationDefault ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check10.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersFemaleSalutationDefault ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a female salutation is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(2);

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check20.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersMaleSalutationUsingFirstName ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation with firstName is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check30.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersMaleSalutationUsingPrependText ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation with prependText is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check40.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check40.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersMaleSalutationUsingAppendText ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation with appendText is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check50.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check50.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersSalutationUsingFallbackText ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation with fallbackText is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(3);

        $this->standAloneViewHelper->setTemplate('Check60.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check60.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersMaleSalutationAllOptions ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation with fallbackText is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check70.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = file_get_contents(self::FIXTURE_PATH . '/Expected/Check70.txt');
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersMxSalutationAllOptions ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a male salutation with fallbackText is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check80.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = str_replace("\n", '', file_get_contents(self::FIXTURE_PATH . '/Expected/Check80.txt'));
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

        self::assertEquals($expected, $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3\CMS\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersMxSalutationDefault ()
    {
        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueRecipient is defined
         * When the ViewHelper is rendered
         * Then a female salutation is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(2);

        $this->standAloneViewHelper->setTemplate('Check90.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $expected = str_replace("\n", '', file_get_contents(self::FIXTURE_PATH . '/Expected/Check90.txt'));
        $result = str_replace("\n", '', $this->standAloneViewHelper->render());

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
