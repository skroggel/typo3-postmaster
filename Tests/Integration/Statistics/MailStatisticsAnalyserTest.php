<?php
namespace Madj2k\Postmaster\Tests\Integration\Statistics;

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
use Madj2k\Postmaster\Domain\Model\MailingStatistics;
use Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Statistics\MailingStatisticsAnalyser;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * MailStatisticsAnalyserTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailStatisticsAnalyserTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/MailStatisticsAnalyserTest/Fixtures';


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
     * @var \Madj2k\Postmaster\Statistics\MailingStatisticsAnalyser|null
     */
    private ?MailingStatisticsAnalyser $subject = null;


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
     * @var \Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository
     */
    private ?MailingStatisticsRepository $mailingStatisticsRepository = null;


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
        $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        $this->mailingStatisticsRepository = $this->objectManager->get(MailingStatisticsRepository::class);
        $this->subject = $this->objectManager->get(MailingStatisticsAnalyser::class);
    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function analyseQueueMailReturnsTotalAndIgnoresRecipientsWithStatusDraft()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has seven queueRecipients
         * Given one of the queueRecipient-objects has the status draft
         * Given one of the queueRecipient-objects has the status waiting
         * Given one of the queueRecipient-objects has the status sending
         * Given one of the queueRecipient-objects has the status finished
         * Given one of the queueRecipient-objects has the status deferred
         * Given one of the queueRecipient-objects has the status bounced
         * Given one of the queueRecipient-objects has the status failed
         * When the method is called
         * Then mailingStatistics-object is added to the queueMail-object
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then the totalRecipients-property of the mailingStatistics-object is set to the value six
         * Then the mailingStatistics-object is persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(10);

        $this->subject->analyseQueueMail($queueMail);

        self::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        self::assertEquals($queueMail->getUid(), $queueMail->getMailingStatistics()->getQueueMail()->getUid());
        self::assertEquals(6, $queueMail->getMailingStatistics()->getTotalRecipients());
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseQueueMailReturnsTotalSentAndIgnoresRecipientsWithStatusDeferred()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has seven queueRecipients
         * Given one of the queueRecipient-objects has the status draft
         * Given one of the queueRecipient-objects has the status waiting
         * Given one of the queueRecipient-objects has the status sending
         * Given one of the queueRecipient-objects has the status finished
         * Given one of the queueRecipient-objects has the status deferred
         * Given one of the queueRecipient-objects has the status bounced
         * Given one of the queueRecipient-objects has the status failed
         * When the method is called
         * Then mailingStatistics-object is added to the queueMail-object
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then the totalSent-property of the mailingStatistics-object is set to the value three
         * Then the mailingStatistics-object is persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(20);

        $this->subject->analyseQueueMail($queueMail);

        self::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        self::assertEquals($queueMail->getUid(), $queueMail->getMailingStatistics()->getQueueMail()->getUid());
        self::assertEquals(3, $queueMail->getMailingStatistics()->getTotalSent());
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseQueueMailReturnsDelivered()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has seven queueRecipients
         * Given one of the queueRecipient-objects has the status draft
         * Given two of the queueRecipient-objects have the status waiting
         * Given three of the queueRecipient-objects have the status sending
         * Given four of the queueRecipient-objects have the status finished
         * Given five of the queueRecipient-objects have the status deferred
         * Given six of the queueRecipient-objects have the status bounced
         * Given seven of the queueRecipient-objects have the status failed
         * When the method is called
         * Then mailingStatistics-object is added to the queueMail-object
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then the delivered-property of the mailingStatistics-object is set to the value four
         * Then the mailingStatistics-object is persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check30.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(30);

        $this->subject->analyseQueueMail($queueMail);

        self::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        self::assertEquals($queueMail->getUid(), $queueMail->getMailingStatistics()->getQueueMail()->getUid());
        self::assertEquals(4, $queueMail->getMailingStatistics()->getDelivered());
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseQueueMailReturnsFailed()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has seven queueRecipients
         * Given one of the queueRecipient-objects has the status draft
         * Given two of the queueRecipient-objects have the status waiting
         * Given three of the queueRecipient-objects have the status sending
         * Given four of the queueRecipient-objects have the status finished
         * Given five of the queueRecipient-objects have the status deferred
         * Given six of the queueRecipient-objects have the status bounced
         * Given seven of the queueRecipient-objects have the status failed
         * When the method is called
         * Then mailingStatistics-object is added to the queueMail-object
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then the failed-property of the mailingStatistics-object is set to the value seven
         * Then the mailingStatistics-object is persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check40.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(40);

        $this->subject->analyseQueueMail($queueMail);

        self::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        self::assertEquals($queueMail->getUid(), $queueMail->getMailingStatistics()->getQueueMail()->getUid());
        self::assertEquals(7,$queueMail->getMailingStatistics()->getFailed());
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseQueueMailReturnsDeferred()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has seven queueRecipients
         * Given one of the queueRecipient-objects has the status draft
         * Given two of the queueRecipient-objects have the status waiting
         * Given three of the queueRecipient-objects have the status sending
         * Given four of the queueRecipient-objects have the status finished
         * Given five of the queueRecipient-objects have the status deferred
         * Given six of the queueRecipient-objects have the status bounced
         * Given seven of the queueRecipient-objects have the status failed
         * When the method is called
         * Then mailingStatistics-object is added to the queueMail-object
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then the deferred-property of the mailingStatistics-object is set to the value five
         * Then the mailingStatistics-object is persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check50.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(50);

        $this->subject->analyseQueueMail($queueMail);

        self::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        self::assertEquals($queueMail->getUid(), $queueMail->getMailingStatistics()->getQueueMail()->getUid());
        self::assertEquals(5, $queueMail->getMailingStatistics()->getDeferred());
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseQueueMailReturnsBounced()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in database
         * Given this queueMail-object has seven queueRecipients
         * Given one of the queueRecipient-objects has the status draft
         * Given two of the queueRecipient-objects have the status waiting
         * Given three of the queueRecipient-objects have the status sending
         * Given four of the queueRecipient-objects have the status finished
         * Given five of the queueRecipient-objects have the status deferred
         * Given six of the queueRecipient-objects have the status bounced
         * Given seven of the queueRecipient-objects have the status failed
         * When the method is called
         * Then mailingStatistics-object is added to the queueMail-object
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then the bounced-property of the mailingStatistics-object is set to the value six
         * Then the mailingStatistics-object is persisted
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check60.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(60);

        $this->subject->analyseQueueMail($queueMail);

        self::assertInstanceOf(MailingStatistics::class, $queueMail->getMailingStatistics());
        self::assertEquals($queueMail->getUid(), $queueMail->getMailingStatistics()->getQueueMail()->getUid());
        self::assertEquals(6, $queueMail->getMailingStatistics()->getBounced());
        self::assertCount(1, $this->mailingStatisticsRepository->findAll());

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function analyseMigratesMailingStatistics()
    {

        /**
         * Scenario:
         *
         * Given two queueMail-objects in database
         * Given one queueMail-object has a mailingStatistics-object linked
         * Given the other queueMail-object has no mailingStatistics-object linked
         * Given this queueMail-object has the tstampFavSending-property set
         * Given this queueMail-object has the tstampRealSending-property set
         * Given this queueMail-object has the tstampSendFinished-property set
         * Given this queueMail-object has a subject set
         * Given this queueMail-object has a type-value of 1
         * Given this queueMail-object has four queueRecipients
         * Given one of this queueRecipients has the status draft
         * Given one of this queueRecipients has the status finished
         * Given one of this queueRecipients has the status deferred
         * Given one of this queueRecipients has the status failed
         * When the method is called
         * Then a mailingStatistics-object is added to the database
         * Then this mailingStatistics-object has the queueMail-property set to the uid of the given queueMail
         * Then this mailingStatistics-object has the subject-property set to the subject of the queueMail
         * Then this mailingStatistics-object has the type-property set to the type of the queueMail
         * Then the totalRecipients-property of the mailingStatistics-object is set to the value three
         * Then the totalSent-property of the mailingStatistics-object is set to the value two
         * Then the delivered-property of the mailingStatistics-object is set to the value one
         * Then the deferred-property of the mailingStatistics-object is set to the value one
         * Then the failed-property of the mailingStatistics-object is set to the value one
         * Then the tstampFavSending-property of the mailingStatistics-object is set accoriding to the tstampFavSending-property of the queueMail-object
         * Then the tstampRealSending-property of the mailingStatistics-object is set accoriding to the tstampRealSending-property of the queueMail-object
         * Then the tstampSendFinsihed-property of the mailingStatistics-object is set accoriding to the tstampSendFinsihed-property of the queueMail-object
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check70.xml');

        $this->subject->analyse();

        $result = $this->mailingStatisticsRepository->findAll();
        self::assertCount(2, $result);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(70);

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $queueMail->getMailingStatistics();
        self::assertEquals(70, $mailingStatistics->getQueueMail()->getUid());
        self::assertInstanceOf(MailingStatistics::class, $mailingStatistics);
        self::assertEquals(3, $mailingStatistics->getTotalRecipients());
        self::assertEquals(2, $mailingStatistics->getTotalSent());
        self::assertEquals(1, $mailingStatistics->getDelivered());
        self::assertEquals(1, $mailingStatistics->getDeferred());
        self::assertEquals(1, $mailingStatistics->getFailed());
        self::assertEquals(111, $mailingStatistics->getTstampFavSending());
        self::assertEquals(222, $mailingStatistics->getTstampRealSending());
        self::assertEquals(333, $mailingStatistics->getTstampFinishedSending());
        self::assertEquals($queueMail->getSubject(), $mailingStatistics->getSubject());
        self::assertEquals($queueMail->getType(), $mailingStatistics->getType());
    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseUpdatesStatistics()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in the database
         * Given this queueMail-object has the status sending
         * Given this queueMail-object has four queueRecipients
         * Given one of this queueRecipients has the status draft
         * Given one of this queueRecipients has the status finished
         * Given one of this queueRecipients has the status deferred
         * Given one of this queueRecipients has the status bounced
         * Given one queueMail-object has a mailingStatistics-object linked
         * Given this mailingStatistics-object has a tstampRealSending that is not older than 30 days
         * Given the totalRecipients-property of the mailingStatistics-object is set to the value two
         * Given the totalSent-property of the mailingStatistics-object is set to the value two
         * Given the delivered-property of the mailingStatistics-object is set to the value two
         * Given the deferred-property of the mailingStatistics-object is set to the value one
         * Given the bounced-property of the mailingStatistics-object is set to the value zero
         * When the method is called
         * Then the mailingStatistics-object is persisted
         * Then the totalRecipients-property of the mailingStatistics-object is set to the value three
         * Then the totalRecipients-property of the mailingStatistics-object is set to the value two
         * Then the delivered-property of the mailingStatistics-object is set to the value one
         * Then the deferred-property of the mailingStatistics-object is set to the value one
         * Then the bounced-property of the mailingStatistics-object is set to the value one
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check80.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(80);
        $mailingStatistics->setTstampRealSending(time());
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $this->subject->analyse();

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(80);

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $queueMail->getMailingStatistics();
        self::assertEquals(3, $mailingStatistics->getTotalRecipients());
        self::assertEquals(2, $mailingStatistics->getTotalSent());
        self::assertEquals(1, $mailingStatistics->getDelivered());
        self::assertEquals(1, $mailingStatistics->getDeferred());
        self::assertEquals(1, $mailingStatistics->getBounced());

    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseDoesNotUpdateStatisticsOfOlderMails()
    {

        /**
         * Scenario:
         *
         * Given a queueMail-object in the database
         * Given this queueMail-object has the status sending
         * Given this queueMail-object has one queueRecipient
         * Given this queueRecipients has the status finished
         * Given the queueMail-object has a mailingStatistics-object linked
         * Given the totalRecipients-property of the mailingStatistics-object is set to the value zero
         * Given the totalSent-property of the mailingStatistics-object is set to the value zero
         * Given the delivered-property of the mailingStatistics-object is set to the value zero
         * Given this mailingStatistics-object has a tstampRealSending that is older than 30 days
         * When the method is called
         * Then the mailingStatistics-object of the queueMail is not updated
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check90.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(90);
        $mailingStatistics->setTstampRealSending(time() - intval(31 * 24 * 60 * 60));
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        // remove linkage
        $mailingStatistics = unserialize(serialize($mailingStatistics));

        $this->subject->analyse();

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(90);

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatisticsAfter */
        $mailingStatisticsAfter = $queueMail->getMailingStatistics();

        self::assertEquals($mailingStatistics, $mailingStatisticsAfter);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function analyseSetsStatusAndSubjectAndType()
    {

        /**
         * Scenario:
         *
         * Given one queueMail-objects in database
         * Given the queueMail-object has the status finished
         * Given the queueMail-object has a mailingStatistics-object linked
         * Given this mailingStatistics-object has a tstampRealSending that is not older than 30 days
         * When the method is called
         * Then the mailingStatistics-object is persisted
         * Then the mailingStatistics-object of the queueMail has the status-property set to finished
         */

        $this->importDataSet(self::FIXTURE_PATH . '/Database/Check100.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $this->mailingStatisticsRepository->findByIdentifier(100);
        $mailingStatistics->setTstampRealSending(time());
        $this->mailingStatisticsRepository->update($mailingStatistics);
        $this->persistenceManager->persistAll();

        $this->subject->analyse();

        /** @var \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail */
        $queueMail = $this->queueMailRepository->findByIdentifier(100);

        /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
        $mailingStatistics = $queueMail->getMailingStatistics();
        self::assertEquals($queueMail->getStatus(), $mailingStatistics->getStatus());
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
