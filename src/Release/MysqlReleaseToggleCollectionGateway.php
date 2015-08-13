<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 13:24
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Release\Gateway\ReleaseToggleCollection;
use Clearbooks\Labs\Toggle\Entity\MarketableToggle;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

class MysqlReleaseToggleCollectionGateway implements ReleaseToggleCollection
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlReleaseToggleCollectionGateway constructor.
     * @param Connection $connection
     */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param string $releaseId
     * @return MarketableToggle[]
     */
    public function getTogglesForRelease( $releaseId )
    {
        $toggles = [];
        $data = $this->connection->fetchAll( 'SELECT * FROM `toggle` WHERE release_id = ?', [ $releaseId ] );
        foreach ( $data as $row ){
            $toggles[] = new Toggle($row['name'], $releaseId);
        }
        return $toggles;
    }
}