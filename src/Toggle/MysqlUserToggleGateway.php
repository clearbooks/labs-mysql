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
use Doctrine\DBAL\Connection;

class MysqlUserToggleGateway extends MysqlGetAllTogglesGateway implements UserToggleGateway
{
    /**
     * @var Connection|\Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * MysqlUserToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return UserToggle[]
     */
    public function getAllUserToggles()
    {
        $data = $this->connection->fetchAll( 'SELECT * FROM `toggle` JOIN `toggle_type` ON toggle.toggle_type = toggle_type.id WHERE type_name = ?',
            [ "user_toggle" ] );
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}