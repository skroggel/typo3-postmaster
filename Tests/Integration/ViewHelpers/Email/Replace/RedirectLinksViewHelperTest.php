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
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * RedirectLinksViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class RedirectLinksViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/RedirectLinksViewHelperTest/Fixtures';


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
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository|null
     */
    private ?QueueMailRepository $queueMailRepository = null;


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
            ],
            ['example.com' => self::FIXTURE_PATH .  '/Frontend/Configuration/config.yaml']
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);

        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);

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
    public function itReplacesNoLinkWhenNoQueueMailGiven ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used in a template
        * Given a queueRecipient-object is defined
        * Given there is no queueMail-object defined
        * When the ViewHelper is rendered
        * Then the links are returned unchanged
        */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('Testen Sie hier: http://www.google.de', $result);
        self::assertStringContainsString('Testen Sie da: [http://www.yahoo.de/#anchor-1]', $result);
        self::assertStringContainsString('Testen Sie dort: mail@example.com', $result);
        self::assertStringContainsString('Testen Sie überall: #anchor-2', $result);

        self::assertStringContainsString('<a href="http://www.google.de">Testen Sie hier</a>', $result);
        self::assertStringContainsString('<a href="http://www.yahoo.de/#anchor-1">Testen Sie da</a>', $result);
        self::assertStringContainsString('<a href="mailto:mail@example.com">Testen Sie dort</a>', $result);
        self::assertStringContainsString('<a href="#anchor-2">Testen Sie überall</a>', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReplacesLinkWhenQueueMailButNoQueueRecipientGiven ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueMail-object is defined
         * Given there is no queueRecipient-object defined
         * When the ViewHelper is rendered
         * Then all normal links are replaced by a redirect link
         * Then anchor- and e-mail-links are left unchanged
         * Then a queueMail-parameter is set in the redirect-links
         * Then no queueRecipient-parameter is set in the redirect-links
         * Then a noCache-parameter is set
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check20.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('Testen Sie hier: http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.google.de', $result);
        self::assertStringContainsString('Testen Sie da: [http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.yahoo.de%2F%23anchor-1', $result);
        self::assertStringContainsString('Testen Sie dort: mail@example.com', $result);
        self::assertStringContainsString('Testen Sie überall: #anchor-2', $result);

        self::assertStringContainsString('<a href="http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.google.de', $result);
        self::assertStringContainsString('>Testen Sie hier</a>', $result);
        self::assertStringContainsString('<a href="http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.yahoo.de%2F%23anchor-1', $result);
        self::assertStringContainsString('>Testen Sie da</a>', $result);
        self::assertStringContainsString('<a href="mailto:mail@example.com">Testen Sie dort</a>', $result);
        self::assertStringContainsString('<a href="#anchor-2">Testen Sie überall</a>', $result);

        self::assertStringContainsString('postmaster/redirect/1?', $result);
        self::assertStringContainsString('no_cache=1', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itReplacesLinkWhenQueueMailAndQueueRecipientGiven ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a queueMail-object is defined
         * Given a queueRecipient-object is defined
         * When the ViewHelper is rendered
         * Then all normal links are replaced by a redirect link
         * Then anchor- and e-mail-links are left unchanged
         * Then a queueMail-parameter is set in the redirect-links
         * Then a queueRecipient-parameter is set in the redirect-links
         * Then a noCache-parameter is set
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('Testen Sie hier: http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.google.de', $result);
        self::assertStringContainsString('Testen Sie da: [http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.yahoo.de%2F%23anchor-1', $result);
        self::assertStringContainsString('Testen Sie dort: mail@example.com', $result);
        self::assertStringContainsString('Testen Sie überall: #anchor-2', $result);

        self::assertStringContainsString('<a href="http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.google.de', $result);
        self::assertStringContainsString('>Testen Sie hier</a>', $result);
        self::assertStringContainsString('<a href="http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/1?no_cache=1&tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.yahoo.de%2F%23anchor-1', $result);
        self::assertStringContainsString('>Testen Sie da</a>', $result);
        self::assertStringContainsString('<a href="mailto:mail@example.com">Testen Sie dort</a>', $result);
        self::assertStringContainsString('<a href="#anchor-2">Testen Sie überall</a>', $result);

        self::assertStringContainsString('postmaster/redirect/1/1?', $result);
        self::assertStringContainsString('no_cache=1', $result);
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
