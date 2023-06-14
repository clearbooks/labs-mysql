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
use Clearbooks\LabsMysql\Toggle\ToggleHelperMethods;
use Doctrine\DBAL\Connection;

class MysqlReleaseToggleCollectionGateway implements ReleaseToggleCollection
{

    use ToggleHelperMethods;

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
        $data = $this->connection->executeQuery( 'SELECT *, toggle.id as toggleId FROM `toggle` LEFT JOIN `toggle_marketing_information` ON toggle.id = toggle_marketing_information.toggle_id WHERE release_id = ?',
            [ $releaseId ] )->fetchAllAssociative();
        return $this->getAllTogglesFromGivenSqlResult( $data );
    }
}
