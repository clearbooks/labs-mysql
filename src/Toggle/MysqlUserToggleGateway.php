<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/08/2015
 * Time: 15:29
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Toggle\Entity\UserToggle;
use Clearbooks\Labs\Toggle\Gateway\UserToggleGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

class MysqlUserToggleGateway implements UserToggleGateway
{
    /**
     * @var Connection|\Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * MysqlUserToggleGateway constructor.
     * @param \Doctrine\DBAL\Connection|Connection $connection
     */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return UserToggle[]
     */
    public function getAllUserToggles()
    {
        $toggles = [ ];
        $data = $this->connection->fetchAll( 'SELECT * FROM `toggle` JOIN `toggle_type` ON toggle.toggle_type = toggle_type.id WHERE type_name = ?',
            [ "user_toggle" ] );
        foreach ( $data as $row ) {
            $toggles[] = new Toggle( $row[ 'name' ], $row[ 'release_id' ], (bool) $row[ 'is_active' ] );
        }
        return $toggles;
    }
}