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
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository;
use Madj2k\Postmaster\Tracking\OpeningTracker;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * OpeningTracker
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class OpeningTrackerTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/OpeningTrackerTest/Fixtures';


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
     * @var \Madj2k\Postmaster\Tracking\OpeningTracker|null
     */
    private ?OpeningTracker $subject = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository|null
     */
    private ?OpeningStatisticsRepository $openingStatisticsRepository = null;


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
        $this->openingStatisticsRepository = $this->objectManager->get(OpeningStatisticsRepository::class);
        $this->subject = $this->objectManager->get(OpeningTracker::class);
    }

    //=============================================

    /**
     * @test
     */
    public function trackDoesNotTrackNonExistingQueueMail()
    {

        /**
         * Scenario:
         *
         * Given a non-persistent queueMail-object
         * Given a persistent queueRecipient-object
         * When the method is called
         * Then false is returned
         * Then no entry in the statistic table is generated
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');
        self::assertFalse($this->subject->track(10, 10));
        self::assertEmpty($this->openingStatisticsRepository->findAll());

    }


    /**
     * @test
     */
    public function trackDoesNotTrackOnNonExistingQueueRecipient()
    {

        /**
         * Scenario:
         *
         * Given a persistent queueMail-object
         * Given a non-persistent queueRecipient-object
         * When the method is called
         * Then false is returned
         * Then no entry in the statistic table is generated
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');
        self::assertFalse($this->subject->track(20, 20));
        self::assertEmpty($this->openingStatisticsRepository->findAll());
    }


    /**
     * @test
     */
    public function trackAddsNewTracking()
    {

        /**
         * Scenario:
         *
         * Given a persistent queueMail-object
         * Given a persistent queueRecipient-object
         * When the method is called
         * Then true is returned
         * Then a new entry in the statistic table is generated
         * Then this entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has a queueRecipient-Object set
         * Then the queueRecipient-Object of this entry in the statistic table has the uid of the given queueRecipient
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to one
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        self::assertTrue($this->subject->track(30, 30));

        $statisticResultList = $this->openingStatisticsRepository->findAll();
        self::assertCount(1, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\OpeningStatistics $openingStatistics */
        $openingStatistics = $statisticResultList->getFirst();
        self::assertInstanceOf(QueueMail::class, $openingStatistics->getQueueMail());
        self::assertEquals(30, $openingStatistics->getQueueMail()->getUid());
        self::assertInstanceOf(QueueRecipient::class, $openingStatistics->getQueueRecipient());
        self::assertEquals(30, $openingStatistics->getQueueRecipient()->getUid());
        self::assertNotEmpty($openingStatistics->getHash());
        self::assertEquals(1, $openingStatistics->getCounter());
    }


    /**
     * @test
     */
    public function trackAddsNewTrackingOnMismatch ()
    {

        /**
         * Scenario:
         *
         * Given a persistent queueMail-object
         * Given a persistent queueRecipient-object
         * Given the queueRecipient-uid has already been tracked with another queueMail-uid than given
         * When the method is called
         * Then true returned
         * Then a new entry in the statistic table is generated
         * Then this entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has a queueRecipient-Object set
         * Then the queueRecipient-Object of this entry in the statistic table has the uid of the given queueRecipient
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to one
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        self::assertTrue($this->subject->track(40, 40));

        $statisticResultList = $this->openingStatisticsRepository->findAll();
        self::assertCount(2, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\OpeningStatistics $openingStatistics */
        $statisticResultList->next();
        $openingStatistics = $statisticResultList->current();
        self::assertInstanceOf(QueueMail::class, $openingStatistics->getQueueMail());
        self::assertEquals(40, $openingStatistics->getQueueMail()->getUid());
        self::assertInstanceOf(QueueRecipient::class, $openingStatistics->getQueueRecipient());
        self::assertEquals(40, $openingStatistics->getQueueRecipient()->getUid());
        self::assertNotEmpty($openingStatistics->getHash());
        self::assertEquals(1, $openingStatistics->getCounter());
    }


    /**
     * @test
     */
    public function trackUpdatesExistingTracking ()
    {

        /**
         * Scenario:
         *
         * Given a persistent queueMail-object
         * Given a persistent queueRecipient-object
         * Given the queueRecipient-uid has already been tracked with the same queueMail-uid as given
         * When the method is called
         * Then true returned
         * Then no new entry in the statistic table is generated
         * Then the existing entry in the statistic table has a queueMail-Object set
         * Then the queueMail-Object of this entry in the statistic table has the uid of the given queueMail
         * Then this entry in the statistic table has a queueRecipient-Object set
         * Then the queueRecipient-Object of this entry in the statistic table has the uid of the given queueRecipient
         * Then this entry in the statistic table has a unique value for the hash-property set
         * Then this entry in the statistic table has the counter-property set to two
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        self::assertTrue($this->subject->track(50, 50));

        $statisticResultList = $this->openingStatisticsRepository->findAll();
        self::assertCount(1, $statisticResultList);

        /** @var \Madj2k\Postmaster\Domain\Model\OpeningStatistics $openingStatistics */
        $openingStatistics = $statisticResultList->getFirst();
        self::assertInstanceOf(QueueMail::class, $openingStatistics->getQueueMail());
        self::assertEquals(50, $openingStatistics->getQueueMail()->getUid());
        self::assertInstanceOf(QueueRecipient::class, $openingStatistics->getQueueRecipient());
        self::assertEquals(50, $openingStatistics->getQueueRecipient()->getUid());
        self::assertNotEmpty($openingStatistics->getHash());
        self::assertEquals(2, $openingStatistics->getCounter());
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
