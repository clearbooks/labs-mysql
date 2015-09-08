<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 19/08/2015
 * Time: 14:51
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Toggle\Gateway\GroupToggleGateway;
use Doctrine\DBAL\Connection;

class MysqlGroupToggleGateway implements GroupToggleGateway
{

    use ToggleHelperMethods;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlGroupToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return GroupToggle[]
     */
    public function getAllGroupToggles()
    {
        $data = $this->connection->fetchAll( 'SELECT *, toggle.id as toggleId FROM `toggle` JOIN `toggle_type` ON toggle.toggle_type = toggle_type.id WHERE type_name = ?',
            [ "group_toggle" ] );
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}