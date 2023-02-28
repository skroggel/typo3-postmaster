<?php
namespace Madj2k\Postmaster\Tests\Integration\Persistence;

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
use Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use Madj2k\Postmaster\Persistence\Cleaner;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * CleanerTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class CleanerTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/CleanerTest/Fixtures';

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
    protected $coreExtensionsToLoad = [ ];


    /**
     * @var \Madj2k\Postmaster\Persistence\Cleaner|null
     */
    private ?Cleaner $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager|null
     */
    private ?ObjectManager $objectManager = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    private ?PersistenceManager $persistenceManager = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository|null
     */
    private ?QueueMailRepository $queueMailRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository|null
     */
    private ?QueueRecipientRepository $queueRecipientRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository|null
     */
    private ?MailingStatisticsRepository $mailingStatisticsRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\OpeningStatisticsRepository|null
     */
    private ?OpeningStatisticsRepository $openingStatisticsRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\ClickStatisticsRepository|null
     */
    private ?ClickStatisticsRepository $clickStatisticsRepository = null;


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
        $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        $this->subject = $this->objectManager->get(Cleaner::class);
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        $this->mailingStatisticsRepository = $this->objectManager->get(MailingStatisticsRepository::class);
        $this->openingStatisticsRepository = $this->objectManager->get(OpeningStatisticsRepository::class);
        $this->clickStatisticsRepository = $this->objectManager->get(ClickStatisticsRepository::class);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function deleteQueueMailDeletesGivenQueueMail()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given one of these queueMail-objects as parameter for the method
         * When the method is called
         * Then the value one is returned
         * Then one queueMail-object is left
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(10);

        $result = $this->subject->deleteQueueMail($queueMail);

        self::assertEquals(1, $result);
        self::assertCount(1, $this->queueMailRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteQueueMailDoesNotDeleteQueueRecipients()
    {

        /**
         * Scenario:
         *
         * Given one queueMail-object in database
         * Given this queueMail-object has two queueRecipients
         * Given the queueMail-object as parameter for the method
         * When the method is called
         * Then the value one is returned
         * Then no queueMail-object is left
         * Then two queueRecipients are left
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(20);

        $result = $this->subject->deleteQueueMail($queueMail);

        self::assertEquals(1, $result);
        self::assertCount(0, $this->queueMailRepository->findAll());
        self::assertCount(2, $this->queueRecipientRepository->findAll());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function deleteQueueRecipientsDeletesQueueRecipientsOfGivenQueueMail()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given both queueMail-objects have two queueRecipient-objects in database
         * Given one of these queueMail-objects as parameter for the method
         * When the method is called
         * Then the value two is returned
         * Then two queueRecipient-objects are left
         * Then only the queueRecipient-objects of the given queueMail are deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail= $this->queueMailRepository->findByIdentifier(30);

        $result = $this->subject->deleteQueueRecipients($queueMail);

        self::assertEquals(2, $result);
        self::assertCount(2, $this->queueRecipientRepository->findAll());
        self::assertCount(0, $this->queueRecipientRepository->findByQueueMail($queueMail));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function deleteQueueRecipientsDoesNotDeletesGivenQueueMail()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given both queueMail-objects have two queueRecipient-objects in database
         * Given one of these queueMail-objects as parameter for the method
         * When the method is called
         * Then the value two is returned
         * Then two queueMail-objects are left
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail= $this->queueMailRepository->findByIdentifier(30);

        $result = $this->subject->deleteQueueRecipients($queueMail);

        self::assertEquals(2, $result);
        self::assertCount(2, $this->queueMailRepository->findAll());
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function deleteStatisticsDeletesStatisticsOfGivenQueueMail()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given both queueMail-objects have a mailingStatistics-object
         * Given both queueMail-objects have two openingStatistics-object
         * Given both queueMail-objects have two clickStatistics-object
         * Given one of these queueMail-objects as parameter for the method
         * When the method is called
         * Then the value five is returned
         * Then only the statistic-objects of the given queueMail are deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail= $this->queueMailRepository->findByIdentifier(40);

        $result = $this->subject->deleteStatistics($queueMail);

        self::assertEquals(5, $result);
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());
        self::assertCount(0, $this->mailingStatisticsRepository->findByQueueMail($queueMail));

        self::assertCount(2, $this->openingStatisticsRepository->findAll());
        self::assertCount(0, $this->openingStatisticsRepository->findByQueueMail($queueMail));

        self::assertCount(2, $this->clickStatisticsRepository->findAll());
        self::assertCount(0, $this->clickStatisticsRepository->findByQueueMail($queueMail));
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function cleanupReturnsFalseIfStatisticsMigrationIncomplete()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given the first queueMail-object has the status finished
         * Given the first queueMail-object has one queueRecipient
         * Given the first queueMail-object has a mailingStatistics-object
         * Given the first mailingStatistics-object has a tstampRealSending-value which is older than 30 days
         * Given the first queueMail-object as parameter for the method
         * Given the second queueMail-object has the status finished
         * Given the second queueMail-object has no mailingStatistics-object
         * When the method is called
         * Then false is returned
         * Then no queueMail-object is deleted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check110.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(110);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup();

        self::assertFalse($result);
        self::assertCount(2, $this->queueMailRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupReturnsFalseOnQueueMailIfNotFinished()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has the status sending
         * Given this queueMail-object has a mailingStatistics-object
         * Given this mailingStatistics-object has a tstampRealSending-value which is older than 30 days
         * When the method is called
         * Then false is returned
         * Then the queueMail-object is not deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(50);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup();

        self::assertFalse($result);
        self::assertCount(1, $this->queueMailRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupReturnsFalseOnQueueMailWithError()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has the status error
         * Given this queueMail-object has a mailingStatistics-object
         * Given this mailingStatistics-object has a tstampRealSending-value which is older than 30 days
         * When the method is called
         * Then false is returned
         * Then the queueMail-object is not deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(60);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup();

        self::assertFalse($result);
        self::assertCount(1, $this->queueMailRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupReturnsFalseOnQueueMailWithWrongType()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has the status finished
         * Given this queueMail-object has the type 1
         * Given this queueMail-object has a mailingStatistics-object
         * Given this mailingStatistics-object has a tstampRealSending-value which is older than 30 days
         * When the method is called
         * Then false is returned
         * Then the queueMail-object is not deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(70);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup();

        self::assertFalse($result);
        self::assertCount(1, $this->queueMailRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupReturnsFalseOnQueueMailNotOldEnough()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has the status finished
         * Given this queueMail-object has a mailingStatistics-object
         * Given this mailingStatistics-object has a tstampRealSending-value which is not older than 30 days
         * When the method is called
         * Then false is returned
         * Then the queueMail-object is not deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(80);
        $mailingStatistics->setTstampFinishedSending(time() - intval(15 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup();

        self::assertFalse($result);
        self::assertCount(1, $this->queueMailRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupRespectsGivenType()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given both queueMail-objects have the status finished
         * Given one queueMail-object has the type 1
         * Given one queueMail-object has the type 2
         * Given both queueMail-object have a mailingStatistics-object
         * Given both queueMail-object have a tstampRealSending-value which is older than 30 days
         * Given the type 2 is set as parameter for the method
         * When the method is called
         * Then true is returned
         * Then the queueMail-object with the type two is deleted
         * Then the queueMail-object with the type one is not deleted
         */
        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(90);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);

        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(91);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup(30, [2]);

        self::assertTrue($result);
        self::assertCount(1, $this->queueMailRepository->findAll());
        self::assertCount(0, $this->queueMailRepository->findByType(2));
        self::assertCount(1, $this->queueMailRepository->findByType(1));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupDeletesQueueMailAndQueueRecipient()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has the status finished
         * Given this queueMail-object has two queueRecipients
         * Given this queueMail-object has a mailingStatistics-object
         * Given this mailingStatistics-object has a tstampRealSending-value which is older than 30 days
         * Given the queueMail-object has two openingStatistics-object
         * Given the queueMail-object has two clickStatistics-object
         * Given one of these queueMail-objects as parameter for the method
         * When the method is called
         * Then true is returned
         * Then the queueMail-object is deleted
         * Then the two queueRecipient-objects of this queueMail are deleted
         * Then the mailingStatistics-object of this queueMail is kept
         * Then the openingStatistics-objects of this queueMail are kept
         * Then the clickStatistics-object of this queueMail are kept
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(100);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup();

        self::assertTrue($result);
        self::assertCount(0, $this->queueMailRepository->findAll());
        self::assertCount(0, $this->queueRecipientRepository->findByQueueMail(100));
        self::assertCount(1, $this->mailingStatisticsRepository->findByQueueMail(100));
        self::assertCount(2, $this->openingStatisticsRepository->findByQueueMail(100));
        self::assertCount(2, $this->clickStatisticsRepository->findByQueueMail(100));
    }


    /**
     * @test
     * @throws \Exception
     */
    public function cleanupDeletesAll()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has the status finished
         * Given this queueMail-object has two queueRecipients
         * Given this queueMail-object has a mailingStatistics-object
         * Given this mailingStatistics-object has a tstampRealSending-value which is older than 30 days
         * Given the queueMail-object has two openingStatistics-object
         * Given the queueMail-object has two clickStatistics-object
         * Given one of these queueMail-objects as parameter for the method
         * Given includingStatistics is set to true as parameter for the method
         * When the method is called
         * Then true is returned
         * Then the queueMail-object is deleted
         * Then the two queueRecipient-objects of this queueMail are deleted
         * Then the mailingStatistics-object of this queueMail are deleted
         * Then the openingStatistics-objects of this queueMail are kept
         * Then the clickStatistics-object of this queueMail are kept
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(100);
        $mailingStatistics->setTstampFinishedSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $result = $this->subject->cleanup(30, [], true);

        self::assertTrue($result);
        self::assertCount(0, $this->queueMailRepository->findAll());
        self::assertCount(0, $this->queueRecipientRepository->findByQueueMail(100));
        self::assertCount(0, $this->mailingStatisticsRepository->findByQueueMail(100));
        self::assertCount(0, $this->openingStatisticsRepository->findByQueueMail(100));
        self::assertCount(0, $this->clickStatisticsRepository->findByQueueMail(100));

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
