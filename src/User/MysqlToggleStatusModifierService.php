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
     * @param $toggleStatus
     * @return bool
     */
    private function toggleStatusUnset( $toggleStatus )
    {
        return $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_UNSET;
    }

    /**
     * @param $toggleStatus
     * @return bool
     */
    private function toggleStatusInactive( $toggleStatus )
    {
        return $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_INACTIVE;
    }

    /**
     * @param $toggleStatus
     * @return bool
     */
    private function toggleStatusActive( $toggleStatus )
    {
        return $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_ACTIVE;
    }

    /**
     * @param $toggleIdentifier
     * @param $userIdentifier
     * @param $isActive
     */
    private function updateUserToggleStatus( $toggleIdentifier, $userIdentifier, $isActive )
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->update( 'user_activated_toggle' )
            ->set( 'is_active', '?' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'user_id = ?' )
            ->setParameter( 0, $isActive )
            ->setParameter( 1, $toggleIdentifier )
            ->setParameter( 2, $userIdentifier );
        $queryBuilder->execute();
    }

    /**
     * @param $toggleIdentifier
     * @param $userIdentifier
     */
    private function unsetUserActivatedToggle( $toggleIdentifier, $userIdentifier )
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->delete( 'user_activated_toggle' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'user_id = ?' )
            ->setParameter( 0, $toggleIdentifier )
            ->setParameter( 1, $userIdentifier );
        $queryBuilder->execute();
    }

    /**
     * @param $toggleIdentifier
     * @param $userIdentifier
     */
    private function insertActiveUserActivatedToggle( $toggleIdentifier, $userIdentifier )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $userIdentifier, 'toggle_id' => $toggleIdentifier, 'is_active' => 1 ] );
    }

    /**
     * @param $toggleIdentifier
     * @param $groupIdentifier
     * @param $isActive
     */
    private function insertGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, $isActive )
    {
        $this->connection->insert( "`group_activated_toggle`",
            [ 'group_id' => $groupIdentifier, 'toggle_id' => $toggleIdentifier, 'active' => $isActive ] );
    }

    /**
     * @param $toggleIdentifier
     * @param $groupIdentifier
     * @param $isActive
     */
    private function updateGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, $isActive )
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->update( 'group_activated_toggle' )
            ->set( 'active', '?' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'group_id = ?' )
            ->setParameter( 0, $isActive )
            ->setParameter( 1, $toggleIdentifier )
            ->setParameter( 2, $groupIdentifier );
        $queryBuilder->execute();
    }

    /**
     * @param $toggleIdentifier
     * @param $groupIdentifier
     */
    private function deleteGroupActivatedToggle( $toggleIdentifier, $groupIdentifier )
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->delete( 'group_activated_toggle' )
            ->where( 'toggle_id = ?' )
            ->andWhere( 'group_id = ?' )
            ->setParameter( 0, $toggleIdentifier )
            ->setParameter( 1, $groupIdentifier );
        $queryBuilder->execute();
    }

    /**
     * @param string $toggleIdentifier
     * @param string $toggleStatus
     * @param int $userIdentifier
     * @return bool
     */
    public function setToggleStatusForUser( $toggleIdentifier, $toggleStatus, $userIdentifier )
    {
        if ( empty( $toggleIdentifier ) || empty( $userIdentifier ) ) {
            return false;
        }
        if ( $this->toggleStatusActive( $toggleStatus ) ) {
            try {
                $this->insertActiveUserActivatedToggle( $toggleIdentifier, $userIdentifier );
            } catch ( \Exception $e ) {
                $this->updateUserToggleStatus( $toggleIdentifier, $userIdentifier, true );
            }
            return true;
        } else if ( $this->toggleStatusInactive( $toggleStatus ) ) {
            $this->updateUserToggleStatus( $toggleIdentifier, $userIdentifier, false );
            return true;
        } else if ( $this->toggleStatusUnset( $toggleStatus ) ) {
            $this->unsetUserActivatedToggle( $toggleIdentifier, $userIdentifier );
            return true;
        } else {
            return false;
        }
    }

    /**
     * @param string $toggleIdentifier
     * @param string $toggleStatus
     * @param int $groupIdentifier
     * @param int $actingUserIdentifier
     * @return bool
     */
    public function setToggleStatusForGroup( $toggleIdentifier, $toggleStatus, $groupIdentifier, $actingUserIdentifier )
    {
        //Here we should check for $actingUserIdentifier to be a group admin. But for now we asume that everyone is the admin.

        if ( empty( $toggleIdentifier ) || empty( $groupIdentifier ) || empty( $actingUserIdentifier ) ) {
            return false;
        }
        if ( $this->toggleStatusActive( $toggleStatus ) ) {
            try {
                $this->insertGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, true );
            } catch ( \Exception $e ) {
                $this->updateGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, true );
            }
            return true;
        } else if ( $this->toggleStatusInactive( $toggleStatus ) ) {
            try {
                $this->insertGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, false );
            } catch ( \Exception $e ) {
                $this->updateGroupActivatedToggle( $toggleIdentifier, $groupIdentifier, false );;
            }
            return true;
        } else if ( $this->toggleStatusUnset( $toggleStatus ) ) {
            $this->deleteGroupActivatedToggle( $toggleIdentifier, $groupIdentifier );
            return true;
        } else {
            return false;
        }
    }
}