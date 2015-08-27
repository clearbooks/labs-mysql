<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:05
 */

namespace Clearbooks\LabsMysql\Toggle;


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
     * MysqlActivatedToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {

        $this->connection = $connection;
    }

    /**
     * @param string $userIdentifier
     * @return ActivatableToggle[]
     */
    public function getAllMyActivatedToggles( $userIdentifier )
    {
        //TODO: This gateway uses user_id as a group_id for group_activated_toggle. Later this has to be modified to get user with user_id and find his group_id and then get his grout_activated_toggle

        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->select( '*' )
            ->from( 'toggle' )
            ->where( 'u_a_t.user_id = ?' )
            ->andWhere( 'u_a_t.is_active = ?' )
            ->join( 'toggle', 'user_activated_toggle', 'u_a_t', 'toggle.id = u_a_t.toggle_id' )
            ->setParameter( 0, $userIdentifier )
            ->setParameter( 1, 1 );
        $data = $queryBuilder->execute()->fetchAll();

        $activatedToggles = $this->getAllTogglesFromGivenSqlResult( $data );

        $queryBuilder2 = new QueryBuilder( $this->connection );
        $queryBuilder2
            ->select( '*' )
            ->from( 'toggle' )
            ->where( 'g_a_t.group_id = ?' )
            ->andWhere( 'g_a_t.active = ?' )
            ->join( 'toggle', 'group_activated_toggle', 'g_a_t', 'toggle.id = g_a_t.toggle_id' )
            ->setParameter( 0, $userIdentifier )
            ->setParameter( 1, 1 );
        $data2 = $queryBuilder2->execute()->fetchAll();

        $activatedToggles = array_merge( $activatedToggles, $this->getAllTogglesFromGivenSqlResult( $data2 ) );

        return $activatedToggles;
    }
}