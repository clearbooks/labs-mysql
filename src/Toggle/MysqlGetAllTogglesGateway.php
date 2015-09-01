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
        $screenshotUrl = isset( $row[ 'screenshot_urls' ] ) ? $row[ 'screenshot_urls' ] : null;
        return $screenshotUrl;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfToggle( $row )
    {
        $descriptionOfToggle = isset( $row[ 'description_of_toggle' ] ) ? $row[ 'description_of_toggle' ] : null;
        return $descriptionOfToggle;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfFunctionality( $row )
    {
        $descriptionOfFunctionality = isset( $row[ 'description_of_functionality' ] ) ? $row[ 'description_of_functionality' ] : null;
        return $descriptionOfFunctionality;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfImplementationReason( $row )
    {
        $descriptionOfImplementationReason = isset( $row[ 'description_of_implementation_reason' ] ) ? $row[ 'description_of_implementation_reason' ] : null;
        return $descriptionOfImplementationReason;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForDescriptionOfLocation( $row )
    {
        $descriptionOfLocation = isset( $row[ 'description_of_location' ] ) ? $row[ 'description_of_location' ] : null;
        return $descriptionOfLocation;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForGuideUrl( $row )
    {
        $guideUrl = isset( $row[ 'guide_url' ] ) ? $row[ 'guide_url' ] : null;
        return $guideUrl;
    }

    /**
     * @param $row
     * @return string
     */
    protected function getDefaultForAppNotificationCopyText( $row )
    {
        $appNotificationCopyText = isset( $row[ 'app_notification_copy_text' ] ) ? $row[ 'app_notification_copy_text' ] : null;
        return $appNotificationCopyText;
    }

    /**
     * @param $row
     * @return array
     * @internal param $toggles
     */
    protected function getToggleFromRow( $row )
    {
        return new Toggle(
            $row[ 'name' ],
            $row[ 'release_id' ],
            (bool) $row[ 'is_active' ],
            $this->getDefaultForScreenshotUrl( $row ),
            $this->getDefaultForDescriptionOfToggle( $row ),
            $this->getDefaultForDescriptionOfFunctionality( $row ),
            $this->getDefaultForDescriptionOfImplementationReason( $row ),
            $this->getDefaultForDescriptionOfLocation( $row ),
            $this->getDefaultForGuideUrl( $row ),
            $this->getDefaultForAppNotificationCopyText( $row )
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