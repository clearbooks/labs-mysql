<?php

namespace Clearbooks\LabsMysql\User;

use Clearbooks\Labs\Db\Table\GroupPolicy;
use Clearbooks\Labs\Db\Table\UseCase\StringifyableTable;
use Clearbooks\Labs\Db\Table\UserPolicy;
use Clearbooks\Labs\User\UseCase\ToggleStatusModifier;
use Clearbooks\Labs\User\UseCase\ToggleStatusModifierService;
use Doctrine\DBAL\Connection;

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
     * @var UserPolicy
     */
    private $userPolicyTable;

    /**
     * @var GroupPolicy
     */
    private $groupPolicyTable;

    /**
     * MysqlUserToggleService constructor.
     * @param Connection $connection
     * @param UserPolicy $userPolicyTable
     * @param GroupPolicy $groupPolicyTable
     */
    public function __construct( Connection $connection, UserPolicy $userPolicyTable, GroupPolicy $groupPolicyTable )
    {
        $this->connection = $connection;
        $this->userPolicyTable = $userPolicyTable;
        $this->groupPolicyTable = $groupPolicyTable;
    }

    /**
     * @param string $toggleIdentifier
     * @param string $groupIdentifier
     * @param string $actingUserIdentifier
     * @return bool
     */
    private function validateSetToggleStatusForGroupParameters( $toggleIdentifier, $groupIdentifier,
                                                                $actingUserIdentifier )
    {
        return empty( $toggleIdentifier ) || empty( $groupIdentifier ) || empty( $actingUserIdentifier );
    }

    /**
     * @param string $toggleIdentifier
     * @param string $userIdentifier
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
     * @param StringifyableTable $policyTable
     * @return string
     */
    private function getIdentityColumnByPolicyTable( StringifyableTable $policyTable )
    {
        $identityColumn = "";
        if ( $policyTable instanceof UserPolicy ) {
            $identityColumn = "user_id";
        }
        else if ( $policyTable instanceof GroupPolicy ) {
            $identityColumn = "group_id";
        }

        return $identityColumn;
    }

    /**
     * @param StringifyableTable $policyTable
     * @param int $toggleIdentifier
     * @param string $identity
     * @return bool
     */
    private function policyRecordExists( StringifyableTable $policyTable, $toggleIdentifier, $identity )
    {
        $identityColumn = $this->getIdentityColumnByPolicyTable( $policyTable );
        $numberOfPolicyRecords = $this->connection->createQueryBuilder()
                                                  ->select( "COUNT(toggle_id)" )
                                                  ->from( (string)$policyTable )
                                                  ->where( "toggle_id = ?" )
                                                  ->andWhere( $identityColumn . " = ?" )
                                                  ->setParameter( 0, $toggleIdentifier )
                                                  ->setParameter( 1, $identity )
                                                  ->execute()->fetchColumn();
        return $numberOfPolicyRecords > 0;
    }

    /**
     * @param StringifyableTable $policyTable
     * @param int $toggleIdentifier
     * @param string $identity
     * @param bool $isActive
     * @return int
     * @throws \Doctrine\DBAL\DBALException
     */
    private function replacePolicyRecord( StringifyableTable $policyTable, $toggleIdentifier, $identity, $isActive )
    {
        $identityColumn = $this->getIdentityColumnByPolicyTable( $policyTable );
        $data = [
                "toggle_id" => $toggleIdentifier,
                $identityColumn => $identity,
                "active" => $isActive ? 1 : 0
        ];

        $this->connection->connect();
        return $this->connection->executeUpdate(
                'REPLACE INTO ' . ( (string)$policyTable ) . ' (' . implode(', ', array_keys( $data ) ) . ')' .
                ' VALUES (' . implode( ', ', array_fill( 0, count( $data ), '?' ) ) . ')',
                array_values( $data )
        );
    }

    /**
     * @param StringifyableTable $policyTable
     * @param int $toggleIdentifier
     * @param string $identity
     * @return int
     */
    private function dropToggle( StringifyableTable $policyTable, $toggleIdentifier, $identity )
    {
        $identityColumn = $this->getIdentityColumnByPolicyTable( $policyTable );
        return $this->connection->delete(
                (string)$policyTable,
                [
                        "toggle_id" => $toggleIdentifier,
                        $identityColumn => $identity
                ]
        );
    }

    /**
     * @param bool $isGroupToggle
     * @return StringifyableTable
     */
    private function getPolicyTable( $isGroupToggle )
    {
        return $isGroupToggle ? $this->groupPolicyTable : $this->userPolicyTable;
    }

    /**
     * @param string $toggleStatus
     * @return bool
     */
    private function isValidStatus( $toggleStatus )
    {
        return $this->toggleStatusActive( $toggleStatus )
               || $this->toggleStatusInactive( $toggleStatus )
               || $this->toggleStatusUnset( $toggleStatus );
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
        if ( !$this->isValidStatus( $toggleStatus ) ) {
            return false;
        }

        $policyTable = $this->getPolicyTable( $isGroup );
        if ( $this->toggleStatusUnset( $toggleStatus ) ) {
            $this->dropToggle( $policyTable, $toggleIdentifier, $userOrGroupIdentifier );
            return true;
        }

        $this->replacePolicyRecord(
                $policyTable,
                $toggleIdentifier,
                $userOrGroupIdentifier,
                $this->toggleStatusActive( $toggleStatus )
        );
        return true;
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