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
     * @param $row
     * @return string
     */
    protected function getDefaultForScreenshotUrl( $row )
    {
        return isset( $row[ 'screenshot_urls' ] ) ? $row[ 'screenshot_urls' ] : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfToggle( $row )
    {
        return isset( $row[ 'description_of_toggle' ] ) ? $row[ 'description_of_toggle' ] : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfFunctionality( $row )
    {
        return isset( $row[ 'description_of_functionality' ] ) ? $row[ 'description_of_functionality' ] : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfImplementationReason( $row )
    {
        return isset( $row[ 'description_of_implementation_reason' ] ) ? $row[ 'description_of_implementation_reason' ] : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfLocation( $row )
    {
        return isset( $row[ 'description_of_location' ] ) ? $row[ 'description_of_location' ] : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForGuideUrl( $row )
    {
        return isset( $row[ 'guide_url' ] ) ? $row[ 'guide_url' ] : null;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForAppNotificationCopyText( $row )
    {
        return isset( $row[ 'app_notification_copy_text' ] ) ? $row[ 'app_notification_copy_text' ] : null;
    }

    /**
     * @param $row
     * @return array
     * @internal param $toggles
     */
    protected function getToggleFromRow( $row )
    {
        return new Toggle(
            $row[ 'toggleId' ], $row[ 'name' ], $row[ 'release_id' ], (bool)$row[ 'visible' ], $this->getDefaultForScreenshotUrl( $row ), $this->getDefaultForDescriptionOfToggle( $row ), $this->getDefaultForDescriptionOfFunctionality( $row ), $this->getDefaultForDescriptionOfImplementationReason( $row ), $this->getDefaultForDescriptionOfLocation( $row ), $this->getDefaultForGuideUrl( $row ), $this->getDefaultForAppNotificationCopyText( $row )
        );
    }

    /**
     * @param array $data
     * @return Toggle[]
     */
    protected function getAllTogglesFromGivenSqlResult( array $data )
    {
        $toggles = [ ];
        foreach ( $data as $row ) {
            $toggles[] = $this->getToggleFromRow( $row );
        }
        return $toggles;
    }

}