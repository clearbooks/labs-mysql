<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 19/08/2015
 * Time: 14:51
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Toggle\Entity\GroupToggle;
use Clearbooks\Labs\Toggle\Gateway\GroupToggleGateway;
use Doctrine\DBAL\Connection;

class MysqlGroupToggleGateway extends MysqlGetAllTogglesGateway implements  GroupToggleGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlGroupToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return GroupToggle[]
     */
    public function getAllGroupToggles()
    {
        $data = $this->connection->fetchAll(
          'SELECT * FROM `toggle`
          JOIN `toggle_type` ON toggle.toggle_type = toggle_type.id
          LEFT JOIN `toggle_marketing_information` ON toggle.id = toggle_marketing_information.toggle_id
          WHERE type_name = ?',
            [ "group_toggle" ] );
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}