<?php
namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\Toggle\Gateway\ActivatableToggleGateway;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 13/08/2015
 * Time: 15:45
 */
class MysqlActivatableToggleGateway implements ActivatableToggleGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlActivatableToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param string $name The name of the toggle you seek
     * @return \Clearbooks\Labs\Toggle\Entity\ActivatableToggle
     */
    public function getActivatableToggleByName( $name )
    {
        return [];
    }
}