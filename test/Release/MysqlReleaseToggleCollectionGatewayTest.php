<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 13:13
 */

namespace Clearbooks\LabsMysql\Release;


use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\DriverManager;
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
        $this->gateway = new MysqlReleaseToggleCollectionGateway( $this->connection );
    }

    public function tearDown()
    {
        $this->deleteAddedTogglesMarketingInformation();
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

    /**
     * @test
     */
    public function givenNoExistentRelease_ReleaseToggleCollection_ReturnsEmptyArray()
    {
        $returnedToggles = $this->gateway->getTogglesForRelease( 'bloop' );
        $this->assertEquals( [ ], $returnedToggles );
    }

    /**
     * @test
     */
    public function givenNoExistentTogglesInTheExistentRelase_ReleaseToggleCollection_ReturnsEmptyArray()
    {
        $releaseName = 'Test release for toggle 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( [ ], $returnedToggles );
    }

    /**
     * @test
     */
    public function givenExistentTogglesInTheExistentRelease_ReleaseToggleCollection_ReturnsArrayOfExistentToggles()
    {
        $releaseName = 'Test release for toggle 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $this->addToggle( "test1", $id );
        $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( "test1", $id );
        $expectedToggle2 = new Toggle( "test2", $id );

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
    public function givenExistentTogglesInTheExistentReleaseWithMarketingInformation_ReleaseToggleCollection_ReturnsArrayOfExistentTogglesWithMarketingInformation()
    {
        $releaseName = 'Test release for toggle 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $this->addToggle( "test1", $id, false, "this", "is", "a", "test", "of", "marketing", "information" );
        $this->addToggle( "test2", $id );

        $expectedToggle = new Toggle( "test1", $id, false, "this", "is", "a", "test", "of", "marketing",
            "information" );
        $expectedToggle2 = new Toggle( "test2", $id );

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
    public function givenExistentTogglesInDifferentReleases_ReleaseToggleCollection_ReturnsArrayOfExistentTogglesForRequestedRelease()
    {
        $releaseName = 'Test release for toggle 3.1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $releaseName2 = 'Test release for toggle 3.2';
        $url2 = 'a helpful url2';
        $id2 = $this->addRelease( $releaseName2, $url2 );

        $this->addToggle( "test1", $id );
        $this->addToggle( "test2", $id );
        $this->addToggle( "test3", $id2 );
        $this->addToggle( "test4", $id2 );

        $expectedToggle = new Toggle( "test1", $id );
        $expectedToggle2 = new Toggle( "test2", $id );

        $expectedToggles = [ $expectedToggle, $expectedToggle2 ];
        $returnedToggles = $this->gateway->getTogglesForRelease( $id );

        $this->assertEquals( $expectedToggles, $returnedToggles );

        foreach ( $expectedToggles as $key => $value ) {
            $this->assertGetters( $value, $returnedToggles[ $key ] );
        }


    }

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
    private function deleteAddedTogglesMarketingInformation()
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
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $screenshotUrl = "", $descriptionOfToggle = "",
                                $descriptionOfFunctionality = "", $descriptionOfImplementationReason = "",
                                $descriptionOfLocation = "", $guideUrl = "", $appNotificationCopyText = "" )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive );
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
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive )
    {
        return $this->connection->insert( "`toggle`", [
            'name' => $name,
            'release_id' => $releaseId,
            'toggle_type' => 1,
            'is_active' => $isActive
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
}
