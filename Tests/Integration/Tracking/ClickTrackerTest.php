<?php
namespace Madj2k\Postmaster\Tests\Integration\Tracking;

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
use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository;
use Madj2k\Postmaster\Tracking\ClickTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * ClickTrackerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ClickTrackerTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/ClickTrackerTest/Fixtures';


    /**
     * Signal name
     *
     * @const string
     */
    const NUMBER_OF_STATISTIC_OPENINGS = 3;


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
    protected $coreExtensionsToLoad = [];


    /**
     * @var \Madj2k\Postmaster\Tracking\ClickTracker|null
     */
    private ?ClickTracker $subject = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository|null
     */
    private ?ClickStatisticsRepository $clickStatisticsRepository = null;


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
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                self::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->clickStatisticsRepository = $this->objectManager->get(ClickStatisticsRepository::class);
        $this->subject = $this->objectManager->get(ClickTracker::class);
    }

    //=============================================

    /**
     * @test
     */
    public function trackDoesNotTrackNonExistentQueueMail()
    {

        /**
         * Scenario:
         *
         * Given an invalid queueMail-uid
         * Given an encoded link
         * When the method is called
         * Then false is returned
         * Then no entry in the statistic table is generated
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->track(9999, $encodedUrl);
        self::assertFalse( $result);
        self::assertEmpty($this->clickStatisticsRepository->findAll());
    }


    /**
     * @test
     */
    public function trackDoesNotTrackNonEmptyString()
    {

        /**
         * Scenario:
         *
         * Given an valid queueMail-uid
         * Given an empty string as url
         * When the method is called
         * Then false is returned
         * Then no entry in the statistic table is generated
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        $result = $this->subject->track(100, '');
        self::assertFalse($result);
        self::assertEmpty($this->clickStatisticsRepository->findAll());
    }


    /**
     * @test
     */
    public function trackAddsNewTrackingFromEncodedUrl()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given an encoded link
         * When the method is called
         * Then true is returned
         * Then one entry in the statistic table is generated
         * Then this entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has the decoded link set accordingly
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to one
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->track(100, $encodedUrl);
        self::assertTrue($result);

        $statisticResultList = $this->clickStatisticsRepository->findAll();
        self::assertCount(1, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\ClickStatistics $clickStatistics */
        $clickStatistics = $statisticResultList->getFirst();
        self::assertInstanceOf(QueueMail::class, $clickStatistics->getQueueMail());
        self::assertEquals(100, $clickStatistics->getQueueMail()->getUid());
        self::assertEquals($expected, $clickStatistics->getUrl());
        self::assertNotEmpty($clickStatistics->getHash());
        self::assertEquals(1, $clickStatistics->getCounter());

    }


    /**
     * @test
     */
    public function trackAddsNewTrackingFromNormalUrl()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a not encoded link
         * When the method is called
         * Then true is returned
         * Then one entry in the statistic table is generated
         * Then this entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has the decoded link set accordingly
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to one
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        $encodedUrl = $expected = 'https://www.google.de/suchen/';

        $result = $this->subject->track(100, $encodedUrl);
        self::assertTrue($result);

        $statisticResultList = $this->clickStatisticsRepository->findAll();
        self::assertCount(1, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\ClickStatistics $clickStatistics */
        $clickStatistics = $statisticResultList->getFirst();
        self::assertInstanceOf(QueueMail::class, $clickStatistics->getQueueMail());
        self::assertEquals(100, $clickStatistics->getQueueMail()->getUid());
        self::assertEquals($expected, $clickStatistics->getUrl());
        self::assertNotEmpty($clickStatistics->getHash());
        self::assertEquals(1, $clickStatistics->getCounter());

    }


    /**
     * @test
     */
    public function trackAddsNewTrackingOnMismatch ()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-uid
         * Given an encoded link
         * Given the encoded link has already been tracked with another queueMail-uid than given
         * When the method is called
         * Then true is returned
         * Then a new entry in the statistic table is generated
         * Then this entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has the decoded link set accordingly without additional params
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to one
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check110.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->track(110, $encodedUrl);
        self::assertTrue($result);

        $statisticResultList = $this->clickStatisticsRepository->findAll();
        self::assertCount(2, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\ClickStatistics $clickStatistics */
        $statisticResultList->next();
        $clickStatistics = $statisticResultList->current();
        self::assertInstanceOf(QueueMail::class, $clickStatistics->getQueueMail());
        self::assertEquals(110, $clickStatistics->getQueueMail()->getUid());
        self::assertEquals($expected, $clickStatistics->getUrl());
        self::assertNotEmpty($clickStatistics->getHash());
        self::assertEquals(1, $clickStatistics->getCounter());
    }


    /**
     * @test
     */
    public function trackUpdatesExistingTracking ()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-uid
         * Given an encoded link
         * Given the encoded link has already been tracked with the same queueMail-uid as given
         * When the method is called
         * Then true returned
         * Then no new entry in the statistic table is generated
         * Then the existing entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has the decoded link set accordingly without additional params
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to two
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check120.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->track(120, $encodedUrl);
        self::assertTrue($result);

        $statisticResultList = $this->clickStatisticsRepository->findAll();
        self::assertCount(1, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\ClickStatistics $clickStatistics */
        $clickStatistics = $statisticResultList->getFirst();
        self::assertInstanceOf(QueueMail::class, $clickStatistics->getQueueMail());
        self::assertEquals(120, $clickStatistics->getQueueMail()->getUid());
        self::assertEquals($expected, $clickStatistics->getUrl());
        self::assertNotEmpty($clickStatistics->getHash());
        self::assertEquals(2, $clickStatistics->getCounter());

    }

    //=============================================

    /**
     * @test
     */
    public function getRedirectUrlReturnsDecodedLink()
    {

        /**
         * Scenario:
         *
         * Given an invalid queueMail-uid
         * Given an encoded link
         * When the method is called
         * Then the decoded link is returned
         * Then no queueMail-parameter is added to the decoded link
         * Then no queueRecipient-parameter is added to the decoded link         *
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->getRedirectUrl($encodedUrl, 99999);
        self::assertEquals($expected, $result);
        self::assertStringNotContainsString('tx_postmaster[mid]=', $result);
        self::assertStringNotContainsString('tx_postmaster[uid]=', $result);
    }


    /**
     * @test
     */
    public function getRedirectUrlReturnsDecodedLinkWithQueueMail()
    {

        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given an encoded link
         * When the method is called
         * Then the decoded link is returned
         * Then a queueMail-parameter with the queueMail-uid is added to the decoded link
         * Then no queueRecipient-parameter is added to the decoded link
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check200.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->getRedirectUrl($encodedUrl, 200);
        self::assertStringStartsWith($expected, $result);
        self::assertStringEndsWith('?tx_postmaster[mid]=200', $result);
        self::assertStringNotContainsString('tx_postmaster[uid]=', $result);

    }


    /**
     * @test
     */
    public function getRedirectUrlReturnsDecodedLinkIgnoresNonMatchingQueueRecipient ()
    {
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid queueRecipient-uid
         * Given an encoded link
         * Given the queueRecipient-uid does not belong to the given queueMail-uid
         * When the method is called
         * Then the decoded link is returned
         * Then a queueMail-parameter with the queueMail-uid is added to the decoded link
         * Then no queueRecipient-parameter is added to the decoded link
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check210.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->getRedirectUrl($encodedUrl, 210, 210);
        self::assertStringStartsWith($expected, $result);
        self::assertStringEndsWith('?tx_postmaster[mid]=210', $result);
        self::assertStringNotContainsString('tx_postmaster[uid]=', $result);

    }


    /**
     * @test
     */
    public function getRedirectUrlReturnsDecodedLinkAndAddsMatchingQueueRecipient ()
    {
        /**
         * Scenario:
         *
         * Given a valid queueMail-uid
         * Given a valid queueRecipient-uid
         * Given an encoded link
         * Given the queueRecipient-uid belongs to the given queueMail-uid
         * When the method is called
         * Then the decoded link is returned
         * Then a queueMail-parameter with the queueMail-uid is added to the decoded link
         * Then a queueRecipient-parameter with the queueRecipient-uid is added to the decoded link
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check220.xml');

        $encodedUrl = 'https%3A%2F%2Fwww.google.de%2Fsuchen%2F';
        $expected = urldecode($encodedUrl);

        $result = $this->subject->getRedirectUrl($encodedUrl, 220, 220);
        self::assertStringStartsWith($expected, $result);
        self::assertStringEndsWith('?tx_postmaster[mid]=220&tx_postmaster[uid]=220', $result);
    }

    //=============================================

    /**
     * @test
     */
    public function getPlainUrlByHashReturnsEmptyOnInvalidLinkHash()
    {

        /**
         * Scenario:
         *
         * Given an invalid link-hash
         * When the method is called
         * Then an empty string is returned
         */
        self::assertEmpty($this->subject->getPlainUrlByHash('abc'));
    }


    /**
     * @test
     */
    public function getPlainUrlByHashReturnsLink ()
    {

        /**
         * Scenario:
         *
         * Given a valid link-hash
         * When the method is called
         * Then the corresponding link is returned
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        $result = $this->subject-> getPlainUrlByHash('48723b1aa49952c291e71078d6690caabd1370ae');
        self::assertEquals('http://aprodi-projekt.de', $result);
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
