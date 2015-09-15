<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 13:13
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Bootstrap;
use Doctrine\DBAL\Connection;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

class MysqlReleaseToggleCollectionGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlReleaseToggleCollectionGateway
     */
    private $gateway;

    /**
     * @var Connection
     */
    private $connection;

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
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     * @param string $type
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $screenshotUrl = "", $descriptionOfToggle = "",
                                $descriptionOfFunctionality = "", $descriptionOfImplementationReason = "",
                                $descriptionOfLocation = "", $guideUrl = "", $appNotificationCopyText = "", $type = "simple" )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive, $type );
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
     * @param string $type
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive, $type )
    {
        return $this->connection->insert( "`toggle`", [
            'name' => $name,
            'release_id' => $releaseId,
            'type' => $type,
            'visible' => $isActive
        ] );
    }

    /**
     * @param Toggle $expectedToggle
     * @param Toggle $returnedToggle
     */
    private function assertGetters( $expectedToggle, $returnedToggle )
    {
        $this->assertEquals( $expectedToggle->getName(),
            $returnedToggle->getName() );
        $this->assertEquals( $expectedToggle->getRelease(),
            $returnedToggle->getRelease() );
        $this->assertEquals( $expectedToggle->isActive(),
            $returnedToggle->isActive() );
        $this->assertEquals( $expectedToggle->getScreenshotUrl(),
            $returnedToggle->getScreenshotUrl() );
        $this->assertEquals( $expectedToggle->getDescriptionOfToggle(),
            $returnedToggle->getDescriptionOfToggle() );
        $this->assertEquals( $expectedToggle->getDescriptionOfFunctionality(),
            $returnedToggle->getDescriptionOfFunctionality() );
        $this->assertEquals( $expectedToggle->getDescriptionOfImplementationReason(),
            $returnedToggle->getDescriptionOfImplementationReason() );
        $this->assertEquals( $expectedToggle->getDescriptionOfLocation(),
            $returnedToggle->getDescriptionOfLocation() );
        $this->assertEquals( $expectedToggle->getGuideUrl(),
            $returnedToggle->getGuideUrl() );
        $this->assertEquals( $expectedToggle->getAppNotificationCopyText(),
            $returnedToggle->getAppNotificationCopyText() );
        $this->assertEquals( $expectedToggle->getType(),
            $returnedToggle->getType() );
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

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $this->gateway = new MysqlReleaseToggleCollectionGateway( $this->connection );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    /**
     * @test
     */
    public function givenNoExistentRelease_ReturnEmptyArray()
    {
        $returnedToggles = $this->gateway->getTogglesForRelease( 'bloop' );
        $this->assertEquals( [ ], $returnedToggles );
    }

    /**
     * @test
     */
    public function givenNoExistentTogglesInTheExistentRelase_ReturnEmptyArray()
    {
        $id = $this->addRelease( 'Test release for toggle 1', 'a helpful url' );

        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( [ ], $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentTogglesInTheExistentRelease_ReturnArrayOfExistentToggles()
    {
        $id = $this->addRelease( 'Test release for toggle 2', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id );
        $toggleId2 = $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( $toggleId, "test1", $id );
        $expectedToggle2 = new Toggle( $toggleId2, "test2", $id );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }
    }

    /**
     * @test
     */
    public function givenExistentTogglesInTheExistentReleaseWithMarketingInformation_ReturnArrayOfExistentTogglesWithValidMarketingInformation()
    {
        $id = $this->addRelease( 'Test release for toggle 2', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, false, "this", "is", "a", "test", "of", "marketing", "information", "group" );
        $toggleId2 = $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( $toggleId, "test1", $id, false, "this", "is", "a", "test", "of", "marketing", "information", "group" );
        $expectedToggle2 = new Toggle( $toggleId2, "test2", $id );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }
    }

    /**
     * @test
     */
    public function givenExistentTogglesInDifferentReleasesWithDifferentMarketingInformation_ReturnArrayOfExistentTogglesForRequestedReleaseWithValidMarketingInformation()
    {
        $id = $this->addRelease( 'Test release for toggle 3.1', 'a helpful url' );
        $id2 = $this->addRelease( 'Test release for toggle 3.2', 'a helpful url2' );

        $toggleId = $this->addToggle( "test1", $id, true, "this", "is", "a", "test", "of", "marketing",
            "information" );
        $toggleId2 = $this->addToggle( "test2", $id, true );
        $this->addToggle( "test3", $id2, true );
        $this->addToggle( "test4", $id2, true );

        $expectedToggle = new Toggle( $toggleId, "test1", $id, true, "this", "is", "a", "test", "of", "marketing", "information" );
        $expectedToggle2 = new Toggle( $toggleId2, "test2", $id, true );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }
    }
}
