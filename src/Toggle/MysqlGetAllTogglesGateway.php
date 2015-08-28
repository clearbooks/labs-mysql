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
    protected function getAllTogglesFromGivenSqlResult( array $data )
    {
        $toggles = [ ];
        foreach ( $data as $row ) {
            $toggles[] = new Toggle( $row[ 'name' ], $row[ 'release_id' ], (bool) $row[ 'is_active' ], $row['screenshot_urls'], $row['description_of_toggle'], $row['description_of_functionality'], $row['description_of_implementation_reason'], $row['description_of_location'], $row['guide_url'],$row['app_notification_copy_text'] );
        }
        return $toggles;
    }

}