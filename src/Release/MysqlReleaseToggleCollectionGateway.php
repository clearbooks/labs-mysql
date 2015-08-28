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
use Clearbooks\LabsMysql\Toggle\MysqlGetAllTogglesGateway;
use Doctrine\DBAL\Connection;

class MysqlReleaseToggleCollectionGateway extends MysqlGetAllTogglesGateway implements ReleaseToggleCollection
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlReleaseToggleCollectionGateway constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param string $releaseId
     * @return MarketableToggle[]
     */
    public function getTogglesForRelease( $releaseId )
    {
        $data = $this->connection->fetchAll( 'SELECT * FROM `toggle` LEFT JOIN `toggle_marketing_information` ON toggle.id = toggle_marketing_information.toggle_id WHERE release_id = ?',
            [ $releaseId ] );
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}