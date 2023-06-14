<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 09/09/2015
 * Time: 16:15
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Toggle\Entity\MarketableToggle;
use Clearbooks\Labs\Toggle\Gateway\GetAllTogglesGateway;
use Doctrine\DBAL\Connection;

class MysqlGetAllTogglesGateway implements GetAllTogglesGateway
{
    use ToggleHelperMethods;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlGetAllTogglesGateway constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return MarketableToggle[]
     */
    public function getAllToggles()
    {
        $data = $this->connection->executeQuery( 'SELECT toggle.*, toggle.id as toggleId, toggle_marketing_information.toggle_title  FROM `toggle`  LEFT JOIN `toggle_marketing_information` ON `toggle`.`id` = `toggle_marketing_information`.`toggle_id`' )->fetchAllAssociative();
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}
