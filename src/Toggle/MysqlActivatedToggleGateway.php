<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:05
 */

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\UseCase\ToggleChecker;
use Clearbooks\Labs\Toggle\Entity\ActivatableToggle;
use Clearbooks\Labs\Toggle\Gateway\ActivatedToggleGateway;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class MysqlActivatedToggleGateway implements ActivatedToggleGateway
{

    use ToggleHelperMethods;

    /**
     * @var Connection
     */
    private $connection;
    /**
     * @var ToggleChecker
     */
    private $toggleChecker;

    /**
     * @var Group
     */
    private $group;

    /**
     * @var User
     */
    private $user;

    /**
     * MysqlActivatedToggleGateway constructor.
     * @param Connection $connection
     * @param ToggleChecker $toggleChecker
     * @param User $user
     * @param Group $group
     */
    public function __construct( Connection $connection, ToggleChecker $toggleChecker, User $user, Group $group )
    {

        $this->connection = $connection;
        $this->toggleChecker = $toggleChecker;
        $this->group = $group;
        $this->user = $user;
    }

    /**
     * @param string $userIdentifier
     * @return ActivatableToggle[]
     */
    public function getAllMyActivatedToggles( $userIdentifier )
    {
        $toggles = [ ];

        $data = $this->connection->fetchAll(
            'SELECT *, toggle.id as toggleId
             FROM `toggle`
             LEFT JOIN `toggle_marketing_information`
             ON toggle.id = toggle_marketing_information.toggle_id
             ORDER BY toggle.id ASC' );

        foreach ( $data as $row ) {
            if ( $this->toggleChecker->isToggleActive( $row[ 'name' ], $this->user, $this->group ) ) {
                $toggles[] = $row;
            }
        }
        $activatedToggles = $this->getAllTogglesFromGivenSqlResult( $toggles );

        return $activatedToggles;
    }
}