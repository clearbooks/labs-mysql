<?php

namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/08/2015
 * Time: 15:12
 */
class MysqlUserToggleGatewayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlUserToggleGateway
     */
    private $gateway;

    /**
     * @var Connection $connection
     */
    private $connection;

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedReleases()
    {
        $this->connection->delete( '`release`', [ '*' ] );
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedToggles()
    {
        $this->connection->delete( '`toggle`', [ '*' ] );
    }

    /**
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedToggleMarketingInformation()
    {
        $this->connection->delete( '`toggle_marketing_information`', [ '*' ] );
    }

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
     * @param int $toggleType
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggleType = 1, $screenshotUrl = "",
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
            'toggle_type' => $toggleType,
            'is_active' => $isActive
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

        $connectionParams = array(
            'dbname' => 'labs',
            'user' => 'root',
            'password' => '',
            'host' => 'localhost',
            'driver' => 'pdo_mysql',
        );

        $this->connection = DriverManager::getConnection( $connectionParams, new Configuration() );
        $this->gateway = new MysqlUserToggleGateway( $this->connection );
    }

    public function tearDown()
    {
        $this->deleteAddedToggleMarketingInformation();
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
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
    public function givenExistentUserTogglesFound_ReturnsArrayOfUserToggles()
    {
        $id = $this->addRelease( 'Test user toggle 1', 'a helpful url' );

        $this->addToggle( "test1", $id, true );

        $expectedToggle = new Toggle( "test1", $id, true );

        $expectedToggles[] = $expectedToggle;
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentUserTogglesFoundWithMarketingInformation_ReturnsArrayOfUserTogglesWithValidMarketingInformation()
    {
        $id = $this->addRelease( 'Test user toggle 1', 'a helpful url' );

        $this->addToggle( "test1", $id, true, 1, "this", "is", "a", "test", "of", "marketing", "information" );
        $this->addToggle( "test2", $id, true, 1 );

        $expectedToggle = new Toggle( "test1", $id, true, "this", "is", "a", "test", "of", "marketing", "information" );
        $expectedToggle2 = new Toggle( "test2", $id, true );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentUserTogglesAndNonUserTogglesFound_ReturnsArrayOfUserTogglesOnly()
    {
        $id = $this->addRelease( 'Test user toggle 1', 'a helpful url' );

        //Parameters: name, release_id, is_activatable, toggle_type
        $this->addToggle( "test1", $id, true, 1 );
        $this->addToggle( "test2", $id, true, 1 );
        $this->addToggle( "test3", $id, true, 2 );
        $this->addToggle( "test4", $id, true, 2 );

        $expectedToggle = new Toggle( "test1", $id, true );
        $expectedToggle2 = new Toggle( "test2", $id, true );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getAllUserToggles();

        $this->assertEquals( $expectedToggles, $returnedToggles );
    }
}
