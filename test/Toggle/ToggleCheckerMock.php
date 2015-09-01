<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 01/09/2015
 * Time: 12:48
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Client\Toggle\UseCase\IsToggleActive;

class ToggleCheckerMock implements IsToggleActive
{
    /**
     * @var
     */
    private $userId;
    /**
     * @var
     */
    private $activatedToggles;

    /**
     * ToggleCheckerMock constructor.
     * @param string $userId
     * @param bool[] $activatedToggles
     */
    public function __construct($userId, $activatedToggles)
    {
        $this->userId = $userId;
        $this->activatedToggles = $activatedToggles;
    }

    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName )
    {
        if ($this->activatedToggles[$toggleName]){
            return true;
        }else{
            return fase;
        }
    }

    /**
     * @param string $toggleName
     * @param string $userId
     * @return array
     */
    private function getUserActivatedToggleEntry( $toggleName, $userId )
    {
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->select( '*' )
            ->from( 'toggle' )
            ->where( 'toggle.name = ?' )
            ->join( 'toggle', 'user_activated_toggle', 'u_a_t', 'toggle.id = u_a_t.toggle_id' )
            ->setParameter( 0, $name );
        $data = $queryBuilder->execute()->fetch();
        if ( empty( $data ) ) {
            return [ ];
        }
        $entry = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'user_id' ], 3 => $data[ 'is_active' ] ];
        return $entry;
    }

    /**
     * @param string $toggleId
     * @param string $groupId
     * @return array
     */
    private function getGroupActivatedToggleEntry( $toggleId, $groupId )
    {
        $data = $this->connection->fetchAssoc( 'SELECT * FROM `group_activated_toggle` WHERE toggle_id = ? AND group_id = ?',
            [ $toggleId, $groupId ] );
        if ( empty( $data ) ) {
            return [ ];
        }
        $entry = [ 1 => $data[ 'toggle_id' ], 2 => $data[ 'group_id' ], 3 => $data[ 'active' ] ];
        return $entry;
    }
}