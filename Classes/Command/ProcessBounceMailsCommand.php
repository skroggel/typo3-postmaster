<?php
namespace Madj2k\Postmaster\Command;

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

use Madj2k\Postmaster\Domain\Repository\BounceMailRepository;
use Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use TYPO3\CMS\Core\Log\Logger;
use TYPO3\CMS\Core\Log\LogLevel;
use TYPO3\CMS\Core\Log\LogManager;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Object\ObjectManager;
use TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager;

/**
 * class ProcessBounceMailsCommand
 *
 * Execute on CLI with: 'vendor/bin/typo3 postmaster:processBounceMails <username> <password> <host>'
 * @todo rework
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class ProcessBounceMailsCommand extends Command
{

    /**
     * @var \Madj2k\Postmaster\Domain\Repository\QueueRecipientRepository|null
     */
    protected ?QueueRecipientRepository $queueRecipientRepository = null;


    /**
     * @var \Madj2k\Postmaster\Domain\Repository\BounceMailRepository|null
     */
    protected ?BounceMailRepository $bounceMailRepository = null;


    /**
     * @var \TYPO3\CMS\Extbase\Persistence\Generic\PersistenceManager|null
     */
    protected ?PersistenceManager $persistenceManager = null;


    /**
     * @var \TYPO3\CMS\Core\Log\Logger|null
     */
    protected ?Logger $logger = null;


    /**
     * Configure the command by defining the name, options and arguments
     */
    protected function configure(): void
    {
        $this->setDescription('Process bounced emails.')
            ->addOption(
                'maxEmails',
                'm',
                InputOption::VALUE_REQUIRED,
                'Maximum number of emails to be processed (Default: 100)',
                100
            );
    }


    /**
     * Initializes the command after the input has been bound and before the input
     * is validated.
     *
     * This is mainly useful when a lot of commands extends one main command
     * where some things need to be initialized based on the input arguments and options.
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function initialize(InputInterface $input, OutputInterface $output): void
    {

        /** @var \TYPO3\CMS\Extbase\Object\ObjectManager $objectManager */
        $objectManager = GeneralUtility::makeInstance(ObjectManager::class);
        $this->bounceMailRepository = $objectManager->get(BounceMailRepository::class);
        $this->queueRecipientRepository = $objectManager->get(QueueRecipientRepository::class);
        $this->persistenceManager = $objectManager->get(PersistenceManager::class);
    }


    /**
     * Executes the command for showing sys_log entries
     *
     * @param \Symfony\Component\Console\Input\InputInterface $input
     * @param \Symfony\Component\Console\Output\OutputInterface $output
     * @return int
     * @see \Symfony\Component\Console\Input\InputInterface::bind()
     * @see \Symfony\Component\Console\Input\InputInterface::validate()
     */
    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);
        $io->title($this->getDescription());
        $io->newLine();

        $maxEmails = $input->getOption('maxEmails');

        $result = 0;
        try {
            if (
                ($bouncedRecipients = $this->queueRecipientRepository->findAllLastBounced($maxEmails))
                && (count($bouncedRecipients))
            ){

                /** @var \Madj2k\Postmaster\Domain\Model\QueueRecipient $queueRecipient */
                foreach ($bouncedRecipients as $queueRecipient) {

                    // set status to bounced
                    $queueRecipient->setStatus(98);
                    $this->queueRecipientRepository->update($queueRecipient);

                    // set status of bounceMail to processed for all bounces of the same email-address
                    // but only if they haven't been processed yet
                    $bounceMails = $this->bounceMailRepository->findByEmailAndStatus($queueRecipient->getEmail(), 0);

                    /** @var \Madj2k\Postmaster\Domain\Model\BounceMail $bounceMail */
                    foreach ($bounceMails as $bounceMail) {
                        $bounceMail->setStatus(1);
                        $bounceMail->setQueueMail($queueRecipient->getQueueMail());
                        $this->bounceMailRepository->update($bounceMail);
                    }

                    $message = sprintf(
                        'Setting bounced status for queueRecipient id=%, email=%s.',
                        $queueRecipient->getUid(),
                        $queueRecipient->getEmail()
                    );
                    $io->note($message);
                    $this->getLogger()->log(LogLevel::INFO, $message);
                }

                $io->note(sprintf('Processed %s emails.',  count($bouncedRecipients)));
                $this->persistenceManager->persistAll();

            } else {
                $message = 'No bounced mails processed.';
                $io->note($message);
                $this->getLogger()->log(LogLevel::DEBUG, $message);
            }


        } catch (\Exception $e) {

            $message = sprintf('An unexpected error occurred while trying to update the statistics of e-mails: %s',
                str_replace(array("\n", "\r"), '', $e->getMessage())
            );
            $io->error($message);
            $this->getLogger()->log(LogLevel::ERROR, $message);
            $result = 1;
        }

        $io->writeln('Done');
        return $result;

    }


    /**
     * Returns logger instance
     *
     * @return \TYPO3\CMS\Core\Log\Logger
     */
    protected function getLogger(): \TYPO3\CMS\Core\Log\Logger
    {
        if (!$this->logger instanceof \TYPO3\CMS\Core\Log\Logger) {
            $this->logger = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(LogManager::class)->getLogger(__CLASS__);
        }

        return $this->logger;
    }
}
