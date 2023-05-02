<?php
namespace Madj2k\Postmaster\Tests\Integration\Cache;

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
use Madj2k\Postmaster\Cache\MailCache;
use Madj2k\Postmaster\Domain\Model\QueueRecipient;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Cache\Backend\NullBackend;
use TYPO3\CMS\Core\Cache\Backend\SimpleFileBackend;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;

/**
 * MailCacheTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailCacheTest extends FunctionalTestCase
{

    /**
     * @const
     */
    const FIXTURE_PATH = __DIR__ . '/MailCacheTest/Fixtures';

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
     * @var \Madj2k\Postmaster\Cache\MailCache|null
     */
    private ?MailCache $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager<null
     */
    private ?ObjectManager $objectManager;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository|null
     */
    private ?QueueRecipientRepository $queueRecipientRepository;


    /**
     * Setup
     * @throws \Exception
     */
    protected function setUp(): void
    {

        parent::setUp();

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Global.xml');
        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:accelerator/Configuration/TypoScript/setup.typoscript',
                'EXT:core_extended/Configuration/TypoScript/setup.typoscript',
                'EXT:postmaster/Configuration/TypoScript/setup.typoscript',
                static::FIXTURE_PATH . '/Frontend/Configuration/Rootpage.typoscript',
            ]
        );

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $this->objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        $this->subject = $this->objectManager->get(MailCache::class);
        $this->subject->clearCache();

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function securityCheckWritesDirectoryProtection()
    {

        /**
         * Scenario:
         *
         * Given the cache is configured as SimpleFileBackend
         * When the method is called
         * Then true is returned
         * Then the htaccess-file is written to the cache dir
         * Then the nginx-file is written to the public dir
         */

        $this->subject= $this->objectManager->get(MailCache::class, SimpleFileBackend::class);
        $cachePath = DIRECTORY_SEPARATOR . trim($this->subject->getCache()->getBackend()->getCacheDirectory(), '/') . DIRECTORY_SEPARATOR;
        $publicPath = DIRECTORY_SEPARATOR . trim(Environment::getPublicPath(), '/') . DIRECTORY_SEPARATOR;
        $hash = substr(md5($cachePath), 0, 12);

        self::assertTrue($this->subject->securityCheck());

        self::assertFileExists($cachePath . '.htaccess');
        self::assertFileExists($publicPath . 'ext_' . $hash . '.nginx');

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getIdentifierUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getIdentifier($queueRecipient, 'Abc');

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getIdentifierUsingPersistedQueueRecipientReturnsIdentifier()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given a property-name as string
         * When method is called
         * Then a string is returned
         * Then the string begins with the prefix "MailCache"
         * Then the string contains the  uid of the queueRecipient
         * Then the string contains the property-name
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);

        $result = $this->subject->getIdentifier($queueRecipient, 'test');
        self::assertStringStartsWith('MailCache', $result);
        self::assertStringContainsString('_10_', $result);
        self::assertStringEndsWith('test', $result);

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setPlaintextBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->setPlaintextBody($queueRecipient, 'Abc');

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getPlaintextBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getPlaintextBody($queueRecipient);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPlaintextBodyUsingSameQueueRecipientReturnsSameString()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given the cache for the queueRecipient-object has already been set
         * When method is called
         * Then the cached content is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);
        $this->subject->setPlaintextBody($queueRecipient, 'Abc');

        self::assertEquals('Abc', $this->subject->getPlaintextBody($queueRecipient));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getPlaintextBodyUsingAnotherQueueRecipientReturnsAnotherString()
    {

        /**
         * Scenario:
         *
         * Given two persisted queueRecipient-objects
         * Given the cache for the first queueRecipient-object has already been set
         * Given the second queueRecipient-object as parameter
         * When method is called
         * Then an empty string is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipientOne */
        $queueRecipientOne = $this->queueRecipientRepository->findbyUid(20);
        $this->subject->setPlaintextBody($queueRecipientOne, 'Abc');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(21);

        self::assertEmpty($this->subject->getPlaintextBody($queueRecipientTwo));

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setHtmlBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->setHtmlBody($queueRecipient, 'Abc');

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getHtmlBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getHtmlBody($queueRecipient);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getHtmlBodyUsingSameQueueRecipientReturnsSameString()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given the cache for the queueRecipient-object has already been set
         * When method is called
         * Then the cached content is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);
        $this->subject->setHtmlBody($queueRecipient, 'Abc');

        self::assertEquals('Abc', $this->subject->getHtmlBody($queueRecipient));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getHtmlBodyUsingAnotherQueueRecipientReturnsAnotherString()
    {

        /**
         * Scenario:
         *
         * Given two persisted queueRecipient-objects
         * Given the cache for the first queueRecipient-object has already been set
         * Given the second queueRecipient-object as parameter
         * When method is called
         * Then an empty string is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipientOne */
        $queueRecipientOne = $this->queueRecipientRepository->findbyUid(20);
        $this->subject->setHtmlBody($queueRecipientOne, 'Abc');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(21);

        self::assertEmpty($this->subject->getHtmlBody($queueRecipientTwo));

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function setCalendarBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->setCalendarBody($queueRecipient, 'Abc');

    }

    //=============================================

    /**
     * @test
     * @throws \Exception
     */
    public function getCalendarBodyUsingNonPersistentQueueRecipientThrowsException()
    {

        /**
         * Scenario:
         *
         * Given a non-persisted queueRecipient-object
         * When the method is called
         * Then an exception is thrown
         * Then the code of the exception is 1634308452
         */

        static::expectException(\Madj2k\Postmaster\Exception::class);
        static::expectExceptionCode(1634308452);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = GeneralUtility::makeInstance(QueueRecipient::class);
        $this->subject->getCalendarBody($queueRecipient);

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getCalendarBodyUsingSameQueueRecipientReturnsSameString()
    {

        /**
         * Scenario:
         *
         * Given a persisted queueRecipient-object
         * Given the cache for the queueRecipient-object has already been set
         * When method is called
         * Then the cached content is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check10.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = $this->queueRecipientRepository->findbyUid(10);
        $this->subject->setCalendarBody($queueRecipient, 'Abc');

        self::assertEquals('Abc', $this->subject->getCalendarBody($queueRecipient));

    }


    /**
     * @test
     * @throws \Exception
     */
    public function getCalendarBodyUsingAnotherQueueRecipientReturnsAnotherString()
    {

        /**
         * Scenario:
         *
         * Given two persisted queueRecipient-objects
         * Given the cache for the first queueRecipient-object has already been set
         * Given the second queueRecipient-object as parameter
         * When method is called
         * Then an empty string is returned
         */

        $this->importDataSet(static::FIXTURE_PATH . '/Database/Check20.xml');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipientOne */
        $queueRecipientOne = $this->queueRecipientRepository->findbyUid(20);
        $this->subject->setCalendarBody($queueRecipientOne, 'Abc');

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipientTwo */
        $queueRecipientTwo = $this->queueRecipientRepository->findbyUid(21);

        self::assertEmpty($this->subject->getCalendarBody($queueRecipientTwo));

    }

    //=============================================

    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        $this->subject->clearCache();
        parent::tearDown();
    }








}
