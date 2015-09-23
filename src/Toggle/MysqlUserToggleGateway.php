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

class MysqlUserToggleGateway implements UserToggleGateway
{

    use ToggleHelperMethods;

    /**
     * @var Connection
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
        $data = $this->connection->fetchAll( 'SELECT *, toggle.id as toggleId FROM `toggle` LEFT JOIN `toggle_marketing_information` ON toggle.id = toggle_marketing_information.toggle_id WHERE type = ?',
            [ "simple" ] );
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}