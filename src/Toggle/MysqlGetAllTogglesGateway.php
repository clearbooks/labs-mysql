<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/08/2015
 * Time: 16:11
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

abstract class MysqlGetAllTogglesGateway
{
    /**
     * @param array $data
     * @return array
     */
    protected function getAllTogglesForGivenSqlStatement( array $data )
    {
        $toggles = [ ];
        foreach ( $data as $row ) {
            $toggles[] = new Toggle( $row[ 'name' ], $row[ 'release_id' ], (bool) $row[ 'is_active' ] );
        }
        return $toggles;
    }

}