<?php

namespace Clearbooks\LabsMysql\User;

use Clearbooks\Labs\User\UseCase\ToggleStatusModifier;
use Clearbooks\Labs\User\UseCase\ToggleStatusModifierService;
use Clearbooks\Labs\User\UseCase\UserToggleService;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 12:26
 */
class MysqlToggleStatusModifierService implements ToggleStatusModifierService
{
    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * MysqlUserToggleService constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param $toggleIdentifier
     * @param $groupIdentifier
     * @param $actingUserIdentifier
     * @return bool
     */
    private function validateSetToggleStatusForGroupParameters( $toggleIdentifier, $groupIdentifier,
                                                                $actingUserIdentifier )
    {
        return empty( $toggleIdentifier ) || empty( $groupIdentifier ) || empty( $actingUserIdentifier );
    }

    /**
     * @param $toggleIdentifier
     * @param $userIdentifier
     * @return bool
     */
    private function validateSetToggleStatusForUserParameters( $toggleIdentifier, $userIdentifier )
    {
        return empty( $toggleIdentifier ) || empty( $userIdentifier );
    }

    /**
     * @param string $toggleStatus
     * @return bool
     */
    private function toggleStatusActive( $toggleStatus )
    {
        return $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_ACTIVE;
    }

    /**
     * @param string $toggleStatus
     * @return bool
     */
    private function toggleStatusInactive( $toggleStatus )
    {
        return $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_INACTIVE;
    }

    /**
     * @param string $toggleStatus
     * @return bool
     */
    private function toggleStatusUnset( $toggleStatus )
    {
        return $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_UNSET;
    }

    /**
     * @param string $toggleIdentifier
     * @param string $userIdentifier
     */
    private function insertActiveUserActivatedToggle( $toggleIdentifier, $userIdentifier )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $userIdentifier, 'toggle_id' => $toggleIdentifier, 'is_active' => 1 ] );
    }

    /**
     * @param string $toggleIdentifier
     * @param string $groupIdentifier
     * @param bool $isActive
     */
    private function insertGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, $isActive )
    {
        $this->connection->insert( "`group_activated_toggle`",
            [ 'group_id' => $groupIdentifier, 'toggle_id' => $toggleIdentifier, 'active' => $isActive ] );
    }

    /**
     * @return QueryBuilder
     */
    private function generateQueryBuilderForUserToggleUpdate()
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->update( 'user_activated_toggle' )
            ->set( 'is_active', '?' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'user_id = ?' );
        return $queryBuilder;
    }

    /**
     * @return QueryBuilder
     */
    private function generateQueryBuilderFroGroupToggleUpdate()
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->update( 'group_activated_toggle' )
            ->set( 'active', '?' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'group_id = ?' );
        return $queryBuilder;
    }

    /**
     * @return QueryBuilder
     */
    private function generateQueryBuilderForUserToggleDelete()
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->delete( 'user_activated_toggle' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'user_id = ?' );
        return $queryBuilder;
    }

    /**
     * @return QueryBuilder
     */
    private function generateQueryBuilderForGroupToggleDelete()
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->delete( 'group_activated_toggle' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'group_id = ?' );
        return $queryBuilder;
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $toggleIdentifier
     * @param string $groupIdentifier
     * @param bool $isActive
     */
    private function updateToggle( QueryBuilder $queryBuilder, $toggleIdentifier, $groupIdentifier, $isActive )
    {
        $queryBuilder
            ->setParameter( 0, $isActive )
            ->setParameter( 1, $toggleIdentifier )
            ->setParameter( 2, $groupIdentifier );
        $queryBuilder->execute();
    }

    /**
     * @param $toggleIdentifier
     * @param $userIdentifier
     */
    private function tryInsertElseUpdateUserToggleToActiveState( $toggleIdentifier, $userIdentifier )
    {
        try {
            $this->insertActiveUserActivatedToggle( $toggleIdentifier, $userIdentifier );
        } catch ( \Exception $e ) {
            $queryBuilder = $this->generateQueryBuilderForUserToggleUpdate();
            $this->updateToggle( $queryBuilder, $toggleIdentifier, $userIdentifier, true );
        }
    }

    /**
     * @param $toggleIdentifier
     * @param $groupIdentifier
     */
    private function tryInsertElseUpdateGroupToggleToAGivenState( $toggleIdentifier, $groupIdentifier, $isActive )
    {
        try {
            $this->insertGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, $isActive );
        } catch ( \Exception $e ) {
            $queryBuilder = $this->generateQueryBuilderFroGroupToggleUpdate();
            $this->updateToggle( $queryBuilder, $toggleIdentifier, $groupIdentifier, $isActive );
        }
    }

    /**
     * @param QueryBuilder $queryBuilder
     * @param string $param1
     * @param string $param2
     */
    private function deleteToggle( QueryBuilder $queryBuilder, $param1, $param2 )
    {
        $queryBuilder
            ->setParameter( 0, $param1 )
            ->setParameter( 1, $param2 );
        $queryBuilder->execute();
    }

    /**
     * @param string $toggleIdentifier
     * @param string $toggleStatus
     * @param string $userOrGroupIdentifier
     * @param bool $isGroup
     * @return bool
     */
    private function setToggleStatus( $toggleIdentifier, $toggleStatus, $userOrGroupIdentifier, $isGroup = false )
    {
        if ( $this->toggleStatusActive( $toggleStatus ) ) {
            if ( !$isGroup ) {
                $this->tryInsertElseUpdateUserToggleToActiveState( $toggleIdentifier, $userOrGroupIdentifier );
            } else {
                $this->tryInsertElseUpdateGroupToggleToAGivenState( $toggleIdentifier, $userOrGroupIdentifier, true );
            }
            return true;
        } else if ( $this->toggleStatusInactive( $toggleStatus ) ) {
            if ( !$isGroup ) {
                $queryBuilder = $this->generateQueryBuilderForUserToggleUpdate();
                $this->updateToggle( $queryBuilder, $toggleIdentifier, $userOrGroupIdentifier, false );
            } else {
                $this->tryInsertElseUpdateGroupToggleToAGivenState( $toggleIdentifier, $userOrGroupIdentifier, false );
            }
            return true;
        } else if ( $this->toggleStatusUnset( $toggleStatus ) ) {
            if ( !$isGroup ) {
                $queryBuilder = $this->generateQueryBuilderForUserToggleDelete();
                $this->deleteToggle( $queryBuilder, $toggleIdentifier, $userOrGroupIdentifier );
            } else {
                $queryBuilder = $this->generateQueryBuilderForGroupToggleDelete();
                $this->deleteToggle( $queryBuilder, $toggleIdentifier, $userOrGroupIdentifier );
            }
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $toggleIdentifier
     * @param string $toggleStatus
     * @param string $userIdentifier
     * @return bool
     */
    public function setToggleStatusForUser( $toggleIdentifier, $toggleStatus, $userIdentifier )
    {
        if ( $this->validateSetToggleStatusForUserParameters( $toggleIdentifier, $userIdentifier ) ) {
            return false;
        }
        return $this->setToggleStatus( $toggleIdentifier, $toggleStatus, $userIdentifier, false );
    }

    /**
     * @param string $toggleIdentifier
     * @param string $toggleStatus
     * @param string $groupIdentifier
     * @param string $actingUserIdentifier
     * @return bool
     */
    public function setToggleStatusForGroup( $toggleIdentifier, $toggleStatus, $groupIdentifier, $actingUserIdentifier )
    {
        //Here we should check for $actingUserIdentifier to be a group admin. But for now we asume that everyone is the admin.

        if ( $this->validateSetToggleStatusForGroupParameters( $toggleIdentifier, $groupIdentifier,
            $actingUserIdentifier )
        ) {
            return false;
        }
        return $this->setToggleStatus( $toggleIdentifier, $toggleStatus, $groupIdentifier, true );
    }
}