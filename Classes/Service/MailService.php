<?php
namespace Madj2k\Postmaster\Service;

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
use Madj2k\Accelerator\Persistence\MarkerReducer;
use Madj2k\Postmaster\Domain\Model\MailingStatistics;
use Madj2k\Postmaster\Domain\Model\QueueMail;
use Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository;
use Madj2k\Postmaster\Mail\Mailer;
use Madj2k\Postmaster\Utility\QueueMailUtility;
use Madj2k\Postmaster\Utility\QueueRecipientUtility;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;
use Madj2k\Postmaster\Domain\Repository\QueueMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use Madj2k\Postmaster\Validation\QueueMailValidator;
use Madj2k\Postmaster\Validation\QueueRecipientValidator;
use TYPO3\CMS\Extbase\Persistence\QueryResultInterface;

/**
 * MailService
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class MailService
{

    /**
     * @var \TYPO3\CMS\Extbase\Object\ObjectManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ObjectManager $objectManager;


    /**
     * @var \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected ConfigurationManagerInterface $configurationManager;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected PersistenceManager $persistenceManager;


    /**
     * @var \Madj2k\Postmaster\Domain\Model\QueueMail|null
     */
    protected ?QueueMail $queueMail = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueMailRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected QueueMailRepository $queueMailRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected QueueRecipientRepository $queueRecipientRepository;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\MailingStatisticsRepository
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected MailingStatisticsRepository $mailingStatisticsRepository;


    /**
     * @var \Madj2k\Postmaster\Validation\QueueMailValidator
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected QueueMailValidator $queueMailValidator;


    /**
     * @var \Madj2k\Postmaster\Validation\QueueRecipientValidator
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected QueueRecipientValidator $queueRecipientValidator;


    /**
     * @var \Madj2k\Postmaster\Mail\Mailer
     * @TYPO3\CMS\Extbase\Annotation\Inject
     */
    protected Mailer $mailer;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * @var array
     */
    protected array $settings = [];


    /**
     * Constructor
     * @param bool $unitTest
     */
    public function __construct(bool $unitTest = false)
    {
        self::debugTime(__LINE__, __METHOD__);
        if (! $unitTest) {
            $this->initializeService();
        }

        self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * function initializeService
     *
     * @return void
     */
    public function initializeService(): void
    {
        // set objects if they haven't been injected yet
        if (!$this->objectManager) {
            $this->objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        }
        if (!$this->configurationManager) {
            $this->configurationManager = $this->objectManager->get(ConfigurationManagerInterface::class);
        }
        if (!$this->persistenceManager) {
            $this->persistenceManager = $this->objectManager->get(PersistenceManager::class);
        }
        if (!$this->queueMailRepository) {
            $this->queueMailRepository = $this->objectManager->get(QueueMailRepository::class);
        }
        if (!$this->queueRecipientRepository) {
            $this->queueRecipientRepository = $this->objectManager->get(QueueRecipientRepository::class);
        }
        if (!$this->queueMailValidator) {
            $this->queueMailValidator = $this->objectManager->get(QueueMailValidator::class);
        }
        if (!$this->queueRecipientValidator) {
            $this->queueRecipientValidator = $this->objectManager->get(QueueRecipientValidator::class);
        }
        if (!$this->mailer) {
            $this->mailer = $this->objectManager->get(Mailer::class);
        }
        if (!$this->mailingStatisticsRepository) {
            $this->mailingStatisticsRepository = $this->objectManager->get(MailingStatisticsRepository::class);
        }
        trigger_error(__CLASS__ . ': Please use the ObjectManager to load this class.', E_USER_DEPRECATED);
    }


    /**
     * Returns mailer
     * @return \Madj2k\Postmaster\Mail\Mailer
     */
    public function getMailer (): Mailer
    {
        return $this->mailer;
    }


    /**
     * Init and return the queueMail
     *
     * @return \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @api
     */
    public function getQueueMail(): QueueMail
    {
        if (!$this->queueMail instanceof \Madj2k\Postmaster\Domain\Model\QueueMail) {

            // init object
            $storagePid = intval($this->getSettings('storagePid', 'persistence'));

            /** @var \Madj2k\Postmaster\Domain\Model\QueueMail queueMail */
            $this->queueMail = QueueMailUtility::initQueueMail($storagePid);

            // add and persist
            /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
            $this->queueMailRepository->add($this->queueMail);
            $this->persistenceManager->persistAll();

            // add mailingStatistics - we do it now because before the persist-call we had no uid!
            /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
            $mailingStatistics = GeneralUtility::makeInstance(MailingStatistics::class);
            $mailingStatistics->setQueueMail($this->queueMail);
            $this->mailingStatisticsRepository->add($mailingStatistics);

            $this->queueMail->setMailingStatistics($mailingStatistics);
            $this->queueMailRepository->update($this->queueMail);
            $this->persistenceManager->persistAll();
        }

        return $this->queueMail;
    }


    /**
     * Sets the queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueMail $queueMail
     * @return void
     * @throws \Madj2k\Postmaster\Exception
     * @api
     */
    public function setQueueMail(QueueMail $queueMail): void
    {

        if ($queueMail->_isNew()) {
            throw new \Madj2k\Postmaster\Exception (
                'The queueMail-object has to be persisted before it can be used.',
                1540193242
            );
        }

        // add mailingStatistics if not already existent
        if (! $queueMail->getMailingStatistics()) {

            /** @var \Madj2k\Postmaster\Domain\Model\MailingStatistics $mailingStatistics */
            $mailingStatistics = GeneralUtility::makeInstance(MailingStatistics::class);
            $mailingStatistics->setQueueMail($queueMail);
            $this->mailingStatisticsRepository->add($mailingStatistics);

            $queueMail->setMailingStatistics($mailingStatistics);
            $this->queueMailRepository->update($queueMail);
            $this->persistenceManager->persistAll();
        }

        $this->queueMail = $queueMail;
    }



    /**
     * Sets the recipients
     *
     * @param \TYPO3\CMS\Extbase\Domain\Model\FrontendUser|\TYPO3\CMS\Extbase\Domain\Model\BackendUser|array $basicData
     * @param array $additionalData
     * @param bool $renderTemplates
     * @return boolean
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \Madj2k\Postmaster\Exception
     * @api
     */
    public function setTo(
        $basicData,
        array $additionalData = [],
        bool $renderTemplates = false
    ): bool {

        self::debugTime(__LINE__, __METHOD__);

        /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
        $queueRecipient = QueueRecipientUtility::initQueueRecipient($basicData, $additionalData);

        if ($this->addQueueRecipient($queueRecipient)) {

            // render templates right away?
            if ($renderTemplates) {
                $this->mailer->renderTemplates($this->getQueueMail(), $queueRecipient);
            }

            self::debugTime(__LINE__, __METHOD__);
            return true;
        }

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * Returns the recipients
     *
     * @return \TYPO3\CMS\Extbase\Persistence\QueryResultInterface
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     */
    public function getTo(): QueryResultInterface
    {
        return $this->queueRecipientRepository->findByQueueMail($this->getQueueMail());
    }


    /**
     * add queueRecipient to queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
     * @throws \Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @return bool
     * @api
     */
    public function addQueueRecipient(
        \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient
    ): bool {

        self::debugTime(__LINE__, __METHOD__);
        if (
            ($this->queueRecipientValidator->validate($queueRecipient))
            && (! $this->hasQueueRecipient($queueRecipient))
        ){

            // add recipient with status "waiting" to queueMail and remove it from object storage
            $queueRecipient->setStatus(2);

            // set storage pid
            $queueRecipient->setPid(intval($this->getSettings('storagePid', 'persistence')));

            // set queueMail
            $queueRecipient->setQueueMail($this->getQueueMail());

            // update, add and persist
            $this->queueRecipientRepository->add($queueRecipient);
            $this->persistenceManager->persistAll();

            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Added recipient with email "%s" (uid %s) to queueMail with uid %s.',
                    $queueRecipient->getEmail(),
                    $queueRecipient->getUid(),
                    $this->getQueueMail()->getUid()
                )
            );

            self::debugTime(__LINE__, __METHOD__);
            return true;
        }

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * check if queue recipient already exists for queueMail
     *
     * @param \Madj2k\Postmaster\Domain\Model\QueueRecipient|string $email
     * @throws \Exception
     * @return bool
     * @api
     */
    public function hasQueueRecipient($email): bool
    {
        self::debugTime(__LINE__, __METHOD__);

        if ($email instanceof \Madj2k\Postmaster\Domain\Model\QueueRecipient){
            $email = $email->getEmail();
        }

        if ($this->queueRecipientRepository->findOneByEmailAndQueueMail($email, $this->getQueueMail())) {
            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'Recipient with email "%s" already exists for queueMail with uid %s.',
                    $email,
                    $this->getQueueMail()->getUid()
                )
            );

            self::debugTime(__LINE__, __METHOD__);
            return true;
        }

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * function send
     *
     * @return boolean
     * @throws \Exception
     * @throws \Madj2k\Postmaster\Exception
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @api
     */
    public function send(): bool
    {
        self::debugTime(__LINE__, __METHOD__);

        $queueMail = $this->getQueueMail();
        if (!$this->queueMailValidator->validate($queueMail)) {
            throw new \Madj2k\Postmaster\Exception(
                'Invalid or missing data in queueMail-object.',
                1540186577
            );
        }

        // only start sending if we are in draft status
        if ($queueMail->getStatus() == QueueMailUtility::STATUS_DRAFT) {

            // find all final recipients of waiting mails!
            $recipientCount = $this->queueRecipientRepository->findAllByQueueMailWithStatusWaiting(
                $queueMail,
                0
            )->count();

            if ($recipientCount > 0) {

                // set status to waiting so the email will be processed
                $queueMail->setStatus(2);
                $queueMail->getMailingStatistics()->setTstampFavSending(time());

                // update and persist changes
                $this->queueMailRepository->update($queueMail);
                $this->persistenceManager->persistAll();

                /** @todo can we savely remove this? It interferes with rkw_newsletter */
                // $this->unsetVariables();

                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'Marked queueMail with uid %s for cronjob (%s recipients).',
                        $queueMail->getUid(),
                        $recipientCount
                    )
                );

                self::debugTime(__LINE__, __METHOD__);
                return true;

            } else {
                $this->getLogger()->log(
                    LogLevel::INFO,
                    sprintf(
                        'QueueMail with uid %s has no recipients.',
                        $queueMail->getUid()
                    )
                );
            }

        } else {
            $this->getLogger()->log(
                LogLevel::INFO,
                sprintf(
                    'QueueMail with uid %s is not a draft (status %s).',
                    $queueMail->getUid(),
                    $queueMail->getStatus()
                )
            );
        }

        self::debugTime(__LINE__, __METHOD__);
        return false;
    }


    /**
     * Sets queueMail as pipeline and updates database
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @api
     */
    public function startPipelining(): void
    {
        self::debugTime(__LINE__, __METHOD__);

        $queueMail = $this->getQueueMail();

        // set status to draft and activate pipelining
        $queueMail->setStatus(1);
        $queueMail->setPipeline(true);
        $this->queueMailRepository->update($queueMail);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Marked QueueMail with uid %s as pipeline.',
                $queueMail->getUid()
            )
        );

        self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * Unsets queueMail as pipeline and updates database
     *
     * @return void
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\IllegalObjectTypeException
     * @throws \TYPO3\CMS\Extbase\Persistence\Exception\UnknownObjectException
     * @throws \TYPO3Fluid\Fluid\View\Exception\InvalidTemplateResourceException
     * @api
     */
    public function stopPipelining(): void
    {
        self::debugTime(__LINE__, __METHOD__);

        $queueMail = $this->getQueueMail();
        $queueMail->setPipeline(false);
        $this->queueMailRepository->update($queueMail);
        $this->persistenceManager->persistAll();

        $this->getLogger()->log(
            LogLevel::INFO,
            sprintf(
                'Unmarked QueueMail with uid %s as pipeline.',
                $queueMail->getUid()
            )
        );

        self::debugTime(__LINE__, __METHOD__);
    }


    /**
     * unset several variables
     *
     * @return void
     */
    protected function unsetVariables()
    {
        unset($this->queueMail);
    }


    /**
     * Gets TypoScript framework settings
     *
     * @param string $param
     * @param string $type
     * @return mixed
     */
    protected function getSettings(string $param = '', string $type = 'settings')
    {

        if (!$this->settings) {

            $this->settings = $this->configurationManager->getConfiguration(
                \TYPO3\CMS\Extbase\Configuration\ConfigurationManagerInterface::CONFIGURATION_TYPE_FRAMEWORK,
                'Postmaster',
                'user'
            );
        }

        if ($param) {

            if ($this->settings[$type][$param . '.']) {
                return $this->settings[$type][$param . '.'];
            }

            return $this->settings[$type][$param];
        }

        return $this->settings[$type];
    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): Logger
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }
        return $this->logger;
    }


    /**
     * Does debugging of runtime
     *
     * @param int $line
     * @param string  $function
     */
    protected static function debugTime(int $line, string $function): void
    {
        if (GeneralUtility::getApplicationContext()->isDevelopment()) {
            $path = \TYPO3\CMS\Core\Core\Environment::getPublicPath() . '/typo3temp/var/logs/tx_postmaster_runtime.txt';
            file_put_contents($path, microtime() . ' ' . $line . ' ' . $function . "\n", FILE_APPEND);
        }
    }
}
