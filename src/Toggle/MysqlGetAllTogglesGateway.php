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

            $screenshotUrl = isset( $row[ 'screenshot_urls' ] ) ? $row[ 'screenshot_urls' ] : "";
            $descriptionOfToggle = isset( $row[ 'description_of_toggle' ] ) ? $row[ 'description_of_toggle' ] : "";
            $descriptionOfFunctionality = isset( $row[ 'description_of_functionality' ] ) ? $row[ 'description_of_functionality' ] : "";
            $descriptionOfImplementationReason = isset( $row[ 'description_of_implementation_reason' ] ) ? $row[ 'description_of_implementation_reason' ] : "";
            $descriptionOfLocation = isset( $row[ 'description_of_location' ] ) ? $row[ 'description_of_location' ] : "";
            $guideUrl = isset( $row[ 'guide_url' ] ) ? $row[ 'guide_url' ] : "";
            $appNotificationCopyText = isset( $row[ 'app_notification_copy_text' ] ) ? $row[ 'app_notification_copy_text' ] : "";

            $toggles[] = new Toggle(
                $row[ 'name' ],
                $row[ 'release_id' ],
                (bool) $row[ 'is_active' ],
                $screenshotUrl,
                $descriptionOfToggle,
                $descriptionOfFunctionality,
                $descriptionOfImplementationReason,
                $descriptionOfLocation,
                $guideUrl,
                $appNotificationCopyText
            );
        }
        return $toggles;
    }

}