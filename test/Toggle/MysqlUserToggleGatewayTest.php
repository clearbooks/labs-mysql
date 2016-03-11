<?php

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/08/2015
 * Time: 15:12
 */
class MysqlUserToggleGatewayTest extends LabsTest
{
    /**
     * @var MysqlUserToggleGateway
     */
    private $gateway;

    /**
     * @param string $releaseName
     * @param string $url
     * @return string
     */
    private function addRelease( $releaseName, $url )
    {
        ( new MysqlReleaseGateway( $this->connection ) )->addRelease( $releaseName, $url );
        return $this->connection->lastInsertId( "`release`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param string $toggleType
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggleType = "simple", $screenshotUrl = "",
                                $descriptionOfToggle = "",
                                $descriptionOfFunctionality = "", $descriptionOfImplementationReason = "",
                                $descriptionOfLocation = "", $guideUrl = "", $appNotificationCopyText = "" )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive, $toggleType );
        $toggleId = $this->connection->lastInsertId( "`toggle`" );
        if (
            !empty( $screenshotUrl ) ||
            !empty( $descriptionOfToggle ) ||
            !empty( $descriptionOfFunctionality ) ||
            !empty( $descriptionOfImplementationReason ) ||
            !empty( $descriptionOfLocation ) ||
            !empty( $guideUrl ) ||
            !empty( $appNotificationCopyText )
        ) {
            $this->addToggleMarketingInformationToDatabase( $toggleId, $screenshotUrl, $descriptionOfToggle,
                $descriptionOfFunctionality, $descriptionOfImplementationReason,
                $descriptionOfLocation, $guideUrl, $appNotificationCopyText );
        }
        return $toggleId;
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggleType
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive, $toggleType )
    {
        return $this->connection->insert( "`toggle`", [
            'name' => $name,
            'release_id' => $releaseId,
            'type' => $toggleType,
            'visible' => $isActive
        ] );
    }

    private function addToggleMarketingInformationToDatabase( $toggleId, $screenshotUrl, $descriptionOfToggle,
                                                              $descriptionOfFunctionality,
                                                              $descriptionOfImplementationReason,
                                                              $descriptionOfLocation, $guideUrl,
                                                              $appNotificationCopyText )
    {
        return $this->connection->insert( "`toggle_marketing_information`", [
            'toggle_id' => $toggleId,
            'screenshot_urls' => $screenshotUrl,
            'description_of_toggle' => $descriptionOfToggle,
            'description_of_functionality' => $descriptionOfFunctionality,
            'description_of_implementation_reason' => $descriptionOfImplementationReason,
            'description_of_location' => $descriptionOfLocation, 'guide_url' => $guideUrl,
            'app_notification_copy_text' => $appNotificationCopyText
        ] );
    }

    public function setUp()
    {
        parent::setUp();
        $this->gateway = new MysqlUserToggleGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoUserTogglesFound_ReturnsEmptyArray()
    {
        $returnedToggle = $this->gateway->getAllUserToggles();
        $this->assertEquals( [ ], $returnedToggle );
    }

    /**
     * @test
     */
    public function givenUserTogglesFound_ReturnsArrayOfUserToggles()
    {
        $releaseId = $this->addRelease( 'Test user toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $releaseId, true, "simple" );

        $expectedToggle = new Toggle( $toggleId, "test1", $releaseId, true, "simple" );

        $expectedToggles[] = $expectedToggle;
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenUserTogglesFoundWithMarketingInformation_ReturnsArrayOfUserTogglesWithValidMarketingInformation()
    {
        $releaseId = $this->addRelease( 'Test user toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $releaseId, true, "simple", "this", "is", "a", "test", "of", "marketing",
            "information" );
        $toggleId2 = $this->addToggle( "test2", $releaseId, true, "simple" );;

        $expectedToggles = [ new Toggle( $toggleId, "test1", $releaseId, true, "simple", "this", "is", "a", "test", "of", "marketing",
            "information" ), new Toggle( $toggleId2, "test2", $releaseId, true ) ];
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenUserTogglesAndNonUserTogglesFound_ReturnsArrayOfUserTogglesOnly()
    {
        $releaseId = $this->addRelease( 'Test user toggle 1', 'a helpful url' );

        //Parameters: name, release_id, is_activatable, toggle_type
        $toggleId = $this->addToggle( "test1", $releaseId, true, "simple" );
        $toggleId2 = $this->addToggle( "test2", $releaseId, true, "simple" );
        $this->addToggle( "test3", $releaseId, true, "group" );
        $this->addToggle( "test4", $releaseId, true, "group" );

        $expectedToggle = new Toggle( $toggleId, "test1", $releaseId, true, "simple" );
        $expectedToggle2 = new Toggle( $toggleId2, "test2", $releaseId, true, "simple" );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }
}
