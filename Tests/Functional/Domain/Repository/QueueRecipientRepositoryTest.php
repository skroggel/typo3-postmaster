<?php
namespace Madj2k\Postmaster\Tests\Functional\Domain\Repository;


use Nimut\TestingFramework\TestCase\FunctionalTestCase;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
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
/**
 * QueueRecipientRepositoryTest
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 * @deprecated
 * @todo rework
 */
class QueueRecipientRepositoryTest extends FunctionalTestCase
{
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
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository|null
     */
    private ?QueueRecipientRepository $subject = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    private ?PersistenceManager $persistenceManager = null;


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
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueRecipientRepository/Pages.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueRecipientRepository/QueueMail.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueRecipientRepository/QueueRecipient.xml');
        $this->importDataSet(__DIR__ . '/Fixtures/Database/QueueRecipientRepository/BounceMail.xml');

        $this->setUpFrontendRootPage(
            1,
            [
                'EXT:postmaster/Configuration/TypoScript/setup.txt',
                'EXT:postmaster/Tests/Functional/Utility/Fixtures/Frontend/Configuration/Rootpage.typoscript',
            ]
        );
        $this->persistenceManager = GeneralUtility::makeInstance(PersistenceManager::class);

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->subject = $this->objectManager->get(QueueRecipientRepository::class);
    }


    /**
     * @test
     */
    public function findAllLastBounced_GivenNothing_ReturnsExpectedResultList()
    {

        $result = $this->subject->findAllLastBounced()->toArray();
        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $objectOne */
        $objectOne = $result[0];

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $objectTwo */
        $objectTwo = $result[1];

        self::assertEquals(2, count($result));

        self::assertEquals(8, $objectOne->getUid());
        self::assertEquals(9, $objectTwo->getUid());

    }


    /**
     * TearDown
     */
    protected function tearDown(): void
    {
        parent::tearDown();
    }
}
