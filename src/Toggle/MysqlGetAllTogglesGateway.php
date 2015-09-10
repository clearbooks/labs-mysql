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
     * @param Connection|mixed $connection
     */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @return MarketableToggle[]
     */
    public function getAllToggles()
    {
        $data = $this->connection->fetchAll( 'SELECT *, toggle.id as toggleId FROM `toggle`' );
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}