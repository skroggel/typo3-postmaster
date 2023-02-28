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
use Madj2k\CoreExtended\Utility\FrontendSimulatorUtility;
use Madj2k\Postmaster\View\EmailStandaloneView;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Cache\CacheManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * PageViewHelperTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class PageViewHelperTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/PageViewHelperTest/Fixtures';


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
    protected $coreExtensionsToLoad = [];


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
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLink ()
    {

        /**
        * Scenario:
        *
        * Given the ViewHelper is used in a template
        * Given the absolute parameter is set to false
        * Given the baseUrl of postmaster is set to http-protocol
        * Given the pageUid-attribute is set to an existing site
        * When the link is rendered
        * Then an absolute link is returned like in frontend context
        * Then the link uses the http-protocol
        * Then no cHash is used
         */

        $this->standAloneViewHelper->setTemplate('Check10.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.example.com/test', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @todo this test runs smoothly if you select the ViewHelper-tests directory. If you run ALL tests, this test fails for mysterious reasons
     */
    public function itRendersAbsoluteLinkHttps ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the absolute parameter is set to false
         * Given the baseUrl of postmaster is set to https-protocol
         * Given the pageUid-attribute is set to an existing site
         * When the link is rendered
         * Then an absolute link is returned like in frontend context
         * Then the link uses the https-protocol
         * Then no cHash is used
         */
        $this->setUpFrontendRootPage(
            10,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/RootpageHttps.typoscript',
            ],
            ['example.com' => self::FIXTURE_PATH .  '/Frontend/Configuration/configHttps.yaml']
        );

        // Flush all caches because of file-cache for YAML-files!
        FrontendSimulatorUtility::resetFrontendEnvironment();
        GeneralUtility::makeInstance(CacheManager::class)->flushCaches();
        sleep(3);

        $this->standAloneViewHelper = $this->objectManager->get(EmailStandaloneView::class, 10);
        $this->standAloneViewHelper->setTemplateRootPaths(
            [
                0 => self::FIXTURE_PATH . '/Frontend/Templates'
            ]
        );
        $this->standAloneViewHelper->setTemplate('Check20.html');
        $result = $this->standAloneViewHelper->render();

        /** IMPORTANT HINT IN PhpDocs !!! */
        self::assertStringContainsString('https://www.example.com/test', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithFeGroup ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the pageUid-attribute is set to an existing site
         * Given that existing site is access-restricted
         * When the link is rendered
         * Then an absolute link to this given pageUid is returned like in frontend context
         * Then no cHash is used
         */

        $this->importDataSet(self::FIXTURE_PATH  . '/Database/Check40.xml');

        $this->standAloneViewHelper->setTemplate('Check40.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.example.com/test/', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithPageType ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given the absolute parameter is set to false
         * Given the baseUrl of postmaster is set to http-protocol
         * Given the pageUid-attribute is set to an existing site
         * Given the pageType-attribute is set
         * When the link is rendered
         * Then an absolute link is returned like in frontend context
         * Then the link uses the http-protocol
         * Then the pageType is set to the given value
         * Then no cHash is used
         */

        $this->standAloneViewHelper->setTemplate('Check50.html');
        $result = $this->standAloneViewHelper->render();
        self::assertStringContainsString('http://www.example.com/test/print', $result);
        self::assertStringNotContainsString('cHash=', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueMailAndRedirect ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid-attribute is set
         * Given a queueMail-attribute is set
         * Given a redirect page is configured and exists
         * When the link is rendered
         * Then an absolute link to the configured redirect page is returned like in frontend context
         * Then the redirect link calls the redirect plugin of postmaster
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains an url-attribute
         * Then the url-attribute contains the absolute link to the given pageUid
         * Then no cHash is used
         * Then a noCache-parameter is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/?', $result);
        self::assertStringContainsString('tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.example.com%2Ftest%2F', $result);
        self::assertStringNotContainsString('cHash=', $result);
        self::assertStringContainsString('no_cache=1', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueRecipientAndRedirect ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid-attribute is set
         * Given a queueMail-attribute is set
         * Given a queueRecipient-attribute is set
         * When the link is rendered
         * Then an absolute link to the configured redirect page is returned like in frontend context
         * Then the redirect link calls the redirect plugin of postmaster
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains the queueRecipientUid
         * Then the redirect link contains an url-attribute
         * Then no cHash is used
         * Then a noCache-parameter is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check30.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);
        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/1/?', $result);
        self::assertStringContainsString('tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.example.com%2Ftest%2F', $result);
        self::assertStringNotContainsString('cHash=', $result);
        self::assertStringContainsString('no_cache=1', $result);
    }


    /**
     * @test
     * @throws \Exception
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     */
    public function itRendersAbsoluteLinkWithQueueRecipientAndRedirectAndPageType ()
    {

        /**
         * Scenario:
         *
         * Given the ViewHelper is used in a template
         * Given a pageUid-attribute is set
         * Given a queueMail-attribute is set
         * Given a redirect page is configured and exists
         * Given a pageType is set
         * When the link is rendered
         * Then an absolute link to the configured redirect page is returned like in frontend context
         * Then the redirect link calls the redirect plugin of postmaster
         * Then the redirect link contains the queueMailUid
         * Then the redirect link contains an url-attribute
         * Then the redirect link contains the pageType-parameter
         * Then the url-attribute contains the absolute link to the given pageUid
         * Then no pageType-parameter is added to the rendered link itself
         * Then no cHash is used
         * Then a noCache-parameter is set
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');
        $queueMail = $this->queueMailRepository->findByIdentifier(1);
        $queueRecipient = $this->queueRecipientRepository->findByIdentifier(1);

        $this->standAloneViewHelper->setTemplate('Check60.html');
        $this->standAloneViewHelper->assign('queueMail', $queueMail);
        $this->standAloneViewHelper->assign('queueRecipient', $queueRecipient);

        $result = $this->standAloneViewHelper->render();

        self::assertStringContainsString('http://www.example.com/umleitungsseite-der-umleitungen/postmaster/redirect/1/1/?', $result);
        self::assertStringContainsString('tx_postmaster_tracking%5Burl%5D=http%3A%2F%2Fwww.example.com%2Ftest%2Fprint%2F', $result);
        self::assertStringNotContainsString('cHash=', $result);
        self::assertStringNotContainsString('type=98', $result);
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
