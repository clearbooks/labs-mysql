<?php
namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\Toggle\Gateway\ActivatableToggleGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Doctrine\DBAL\Connection;

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
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param string $name The name of the toggle you seek
     * @return \Clearbooks\Labs\Toggle\Entity\ActivatableToggle
     */
    public function getActivatableToggleByName( $name )
    {
        $data = $this->connection->fetchAssoc( 'SELECT * FROM `toggle` WHERE name = ? AND is_active = ?',
            [ $name, true ] );
        if ( empty( $data ) ) {
            return null;
        }
        $toggle = new Toggle( $data[ 'name' ], $data[ 'release_id' ], (bool) $data[ 'is_active' ] );

        return $toggle;
    }
}