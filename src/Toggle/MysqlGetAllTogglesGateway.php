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

            list(
                $screenshotUrl,
                $descriptionOfToggle,
                $descriptionOfFunctionality,
                $descriptionOfImplementationReason,
                $descriptionOfLocation,
                $guideUrl,
                $appNotificationCopyText
                ) = $this->setDefaultMarketingInformationForToggle( $row );

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

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultMarketingInformationForToggle( $row )
    {
        $screenshotUrl = $this->setDefaultForScreenshotUrl( $row );
        $descriptionOfToggle = $this->setDefaultForDescriptionOfToggle( $row );
        $descriptionOfFunctionality = $this->setDefaultForDescriptionOfFunctionality( $row );
        $descriptionOfImplementationReason = $this->setDefaultForDescriptionOfImplementationReason( $row );
        $descriptionOfLocation = $this->setDefaultForDescriptionOfLocation( $row );
        $guideUrl = $this->setDefaultForGuideUrl( $row );
        $appNotificationCopyText = $this->setDefaultForAppNotificationCopyText( $row );

        return array( $screenshotUrl, $descriptionOfToggle, $descriptionOfFunctionality, $descriptionOfImplementationReason, $descriptionOfLocation, $guideUrl, $appNotificationCopyText );
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForScreenshotUrl( $row )
    {
        $screenshotUrl = isset( $row[ 'screenshot_urls' ] ) ? $row[ 'screenshot_urls' ] : null;
        return $screenshotUrl;
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForDescriptionOfToggle( $row )
    {
        $descriptionOfToggle = isset( $row[ 'description_of_toggle' ] ) ? $row[ 'description_of_toggle' ] : null;
        return $descriptionOfToggle;
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForDescriptionOfFunctionality( $row )
    {
        $descriptionOfFunctionality = isset( $row[ 'description_of_functionality' ] ) ? $row[ 'description_of_functionality' ] : null;
        return $descriptionOfFunctionality;
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForDescriptionOfImplementationReason( $row )
    {
        $descriptionOfImplementationReason = isset( $row[ 'description_of_implementation_reason' ] ) ? $row[ 'description_of_implementation_reason' ] : null;
        return $descriptionOfImplementationReason;
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForDescriptionOfLocation( $row )
    {
        $descriptionOfLocation = isset( $row[ 'description_of_location' ] ) ? $row[ 'description_of_location' ] : null;
        return $descriptionOfLocation;
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForGuideUrl( $row )
    {
        $guideUrl = isset( $row[ 'guide_url' ] ) ? $row[ 'guide_url' ] : null;
        return $guideUrl;
    }

    /**
     * @param $row
     * @return array
     */
    protected function setDefaultForAppNotificationCopyText( $row )
    {
        $appNotificationCopyText = isset( $row[ 'app_notification_copy_text' ] ) ? $row[ 'app_notification_copy_text' ] : null;
        return $appNotificationCopyText;
    }

}