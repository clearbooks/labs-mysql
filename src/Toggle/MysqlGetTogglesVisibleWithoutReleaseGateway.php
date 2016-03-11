<?php
namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\Db\Table\Toggle;
use Clearbooks\Labs\Toggle\Entity\MarketableToggle;
use Clearbooks\Labs\Toggle\Gateway\GetGroupTogglesVisibleWithoutReleaseGateway;
use Clearbooks\Labs\Toggle\Gateway\GetUserTogglesVisibleWithoutReleaseGateway;
use Doctrine\DBAL\Connection;

class MysqlGetTogglesVisibleWithoutReleaseGateway implements GetUserTogglesVisibleWithoutReleaseGateway, GetGroupTogglesVisibleWithoutReleaseGateway
{
    use ToggleHelperMethods;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var Toggle
     */
    private $toggleTable;

    /**
     * @param Connection $connection
     * @param Toggle $toggleTable
     */
    public function __construct( Connection $connection, Toggle $toggleTable )
    {
        $this->connection = $connection;
        $this->toggleTable = $toggleTable;
    }

    /**
     * @param bool $isGroupToggle
     * @return MarketableToggle[]
     */
    private function getTogglesVisibleWithoutRelease( $isGroupToggle )
    {
        $queryBuilder = $this->connection->createQueryBuilder();
        $queryBuilder->select( "*" )
                     ->from( (string)$this->toggleTable )
                     ->where( "release_id IS NULL AND visible = 1 AND visible_without_release = 1 AND type = ?" );

        $queryBuilder->setParameter( 0, $isGroupToggle ? "group" : "simple" );

        $results = $queryBuilder->execute()->fetchAll();
        return $this->getAllTogglesFromGivenSqlResult( $results );
    }

    /**
     * @return MarketableToggle[]
     */
    public function getUserTogglesVisibleWithoutRelease()
    {
        return $this->getTogglesVisibleWithoutRelease( false );
    }

    /**
     * @return MarketableToggle[]
     */
    public function getGroupTogglesVisibleWithoutRelease()
    {
        return $this->getTogglesVisibleWithoutRelease( true );
    }
}
