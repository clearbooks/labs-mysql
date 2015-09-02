<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:05
 */

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\Client\Toggle\UseCase\IsToggleActive;
use Clearbooks\Labs\Toggle\Entity\ActivatableToggle;
use Clearbooks\Labs\Toggle\Gateway\ActivatedToggleGateway;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class MysqlActivatedToggleGateway extends MysqlGetAllTogglesGateway implements ActivatedToggleGateway
{
    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var IsToggleActive
     */
    private $toggleChecker;

    /**
     * MysqlActivatedToggleGateway constructor.
     * @param Connection $connection
     * @param IsToggleActive $toggleChecker
     */
    public function __construct( Connection $connection, IsToggleActive $toggleChecker )
    {

        $this->connection = $connection;
        $this->toggleChecker = $toggleChecker;
    }

    /**
     * @param string $userIdentifier
     * @return ActivatableToggle[]
     */
    public function getAllMyActivatedToggles( $userIdentifier )
    {
        $activatedToggles = [ ];

        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->select( '*' )
            ->from( 'toggle' );
        $data = $queryBuilder->execute()->fetchAll();
        $toggles = $this->getAllTogglesFromGivenSqlResult( $data );

        foreach ( $toggles as $toggle ) {
            if ( $this->toggleChecker->isToggleActive( $toggle->getName() ) ) {
                $activatedToggles[] = $toggle;
            }
        }

        return $activatedToggles;
    }
}