<?php

namespace Madj2k\Postmaster\Domain\Repository;
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

use TYPO3\CMS\Extbase\Persistence\Generic\Typo3QuerySettings;

/**
 * BounceMailRepository
 *
 * @author Steffen Kroggel <developer@steffenkroggel.de>
 * @copyright Steffen Kroggel
 * @package Madj2k_Postmaster
 * @license http://www.gnu.org/licenses/gpl.html GNU General Public License, version 3 or later
 */
class BounceMailRepository extends AbstractRepository
{

    /**
     * @return void
     * @throws \TYPO3\CMS\Extbase\Configuration\Exception\InvalidConfigurationTypeException
     */
    public function initializeObject(): void
    {
        parent::initializeObject();
        $this->defaultQuerySettings = $this->objectManager->get(Typo3QuerySettings::class);
        $this->defaultQuerySettings->setRespectStoragePage(false);
    }


    /**
     * Count bounces by email and type
     *
     * @param string $email
     * @param string $type
     * @return int
     * @todo write tests
     */
    public function countByEmailAndType (
        string $email,
        string $type = 'hard'
    ): int {

        $query = $this->createQuery();
        $query->matching(
            $query->logicalAnd(
                $query->equals('email', $email),
                $query->equals('type', $type)
                )
        );

        return $query->execute()->count();
    }

}
