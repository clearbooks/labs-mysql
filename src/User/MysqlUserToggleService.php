<?php

namespace Clearbooks\LabsMysql\User;

use Clearbooks\Labs\User\UseCase\UserToggleService;
use Doctrine\DBAL\Connection;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 12:26
 */
class MysqlUserToggleService implements UserToggleService
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
}