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
     * @param string $toggleIdentifier
     * @param int $userIdentifier
     * @return bool
     */
    public function activateToggle( $toggleIdentifier, $userIdentifier )
    {
        try {
            $this->connection->insert( "`user_activated_toggle`",
                [ 'user_id' => $userIdentifier, 'toggle_id' => $toggleIdentifier ] );
        } catch ( \Exception $e ) {
            return false;
        }
        return true;

    }

    /**
     * @param string $toggleIdentifier
     * @param int $userIdentifier
     * @return bool
     */
    public function deActivateToggle( $toggleIdentifier, $userIdentifier )
    {
        $checkResult = $this->connection->fetchAll( 'SELECT * FROM `user_activated_toggle` WHERE toggle_id = ? AND user_id = ?',
            [ $toggleIdentifier, $userIdentifier ] );
        if ( empty( $checkResult ) ) {
            return false;
        }

        $this->connection->delete( "`user_activated_toggle`",
            [ 'toggle_id' => $toggleIdentifier, 'user_id' => $userIdentifier ] );
        return true;
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
        if ( $toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_ACTIVE ) {
            try {
                $this->connection->insert( "`user_activated_toggle`",
                    [ 'user_id' => $userIdentifier, 'toggle_id' => $toggleIdentifier, 'is_active' => 1 ] );
            } catch ( \Exception $e ) {
                $queryBuilder = new QueryBuilder($this->connection);
                $queryBuilder
                    ->update('user_activated_toggle')
                    ->set('is_active', 1)
                    ->where('toggle_id', '?')
                    ->andWhere('user_id', '?')
                    ->setParameter(0, $toggleIdentifier)
                    ->setParameter(1, $userIdentifier);
                $queryBuilder->execute();
            }
            return true;
        } else if ($toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_INACTIVE) {
            $queryBuilder = new QueryBuilder($this->connection);
            $queryBuilder
                ->update('user_activated_toggle')
                ->set('is_active', 0)
                ->where('toggle_id', '?')
                ->andWhere('user_id', '?')
                ->setParameter(0, $toggleIdentifier)
                ->setParameter(1, $userIdentifier);
            $queryBuilder->execute();
            return true;
        } else if ($toggleStatus === ToggleStatusModifier::TOGGLE_STATUS_UNSET) {
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
        // TODO: Implement setToggleStatusForGroup() method.
    }
}