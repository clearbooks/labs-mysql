<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 07/09/2015
 * Time: 11:16
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Doctrine\DBAL\Connection;

class MysqlMarketableToggleGatewayTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MysqlMarketableToggleGateway
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
     * @param string $toggleId
     * @param string $screenshotUrl
     * @param string $descriptionOfToggle
     * @param string $descriptionOfFunctionality
     * @param string $descriptionOfImplementationReason
     * @param string $descriptionOfLocation
     * @param string $guideUrl
     * @param string $appNotificationCopyText
     * @return string
     */
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

        $this->gateway = new MysqlMarketableToggleGateway( $this->connection );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    /**
     * @test
     */
    public function givenNoToggleId_NoMarketingInformationWillBeCreated()
    {
        $expected = [ ];
        $this->gateway->setMarketingInformationForToggle( null, [ ] );

        $this->assertEmpty( $this->validateMarketingInformationInTheDatabase( $expected, null ) );
    }

    /**
     * @test
     */
    public function givenAnIdOfExistentToggleButWithNoMarketingInformationAndEmptyMarketingInfromationProvided_EmptyMarketingInformationWillBeCreatedForGivenToggle()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 1', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 1", $releaseId, true );

        $expected = [ [
            'toggle_id' => $toggleId,
            'screenshot_urls' => '',
            'description_of_toggle' => '',
            'description_of_functionality' => '',
            'description_of_implementation_reason' => '',
            'description_of_location' => '',
            'guide_url' => '',
            'app_notification_copy_text' => ''
        ] ];
        $this->gateway->setMarketingInformationForToggle( $toggleId, [ ] );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenAnIdOfExistentToggleWithMarketingInformationAndEmptyMarketingInfromationProvided_NoPreviousMarketingInformationWillBeChangedForGivenToggle()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true, "this", "is", "the", "test", "of",
            "marketing", "information" );

        $expected = [ [
            'toggle_id' => $toggleId,
            'screenshot_urls' => 'this',
            'description_of_toggle' => 'is',
            'description_of_functionality' => 'the',
            'description_of_implementation_reason' => 'test',
            'description_of_location' => 'of',
            'guide_url' => 'marketing',
            'app_notification_copy_text' => 'information'
        ] ];
        $this->gateway->setMarketingInformationForToggle( $toggleId,
            [
                'screenshot_urls' => '',
                'description_of_toggle' => '',
                'description_of_functionality' => '',
                'description_of_implementation_reason' => '',
                'description_of_location' => '',
                'guide_url' => '',
                'app_notification_copy_text' => ''
            ] );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenAnIdOfExistentToggleWithNoMarketingInformationAndFullMarketingInfromationProvided_CreateNewMarketingInformationForGivenToggle()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true );

        $expected = [ [
            'toggle_id' => $toggleId,
            'screenshot_urls' => 'this',
            'description_of_toggle' => 'is',
            'description_of_functionality' => 'the',
            'description_of_implementation_reason' => 'test',
            'description_of_location' => 'of',
            'guide_url' => 'marketing',
            'app_notification_copy_text' => 'information'
        ] ];
        $this->gateway->setMarketingInformationForToggle( $toggleId,
            [
                'screenshot_urls' => 'this',
                'description_of_toggle' => 'is',
                'description_of_functionality' => 'the',
                'description_of_implementation_reason' => 'test',
                'description_of_location' => 'of',
                'guide_url' => 'marketing',
                'app_notification_copy_text' => 'information'
            ] );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenAnIdOfExistentToggleWithMarketingInformationAndFullMarketingInfromationProvided_AllTheMarketingInformationWillBeUpdatedForGivenToggle()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true, "this blah...", "is blah...", "the blah...", "test blah...", "of blah...",
            "marketing blah...", "information blah..." );

        $expected = [ [
            'toggle_id' => $toggleId,
            'screenshot_urls' => 'this',
            'description_of_toggle' => 'is',
            'description_of_functionality' => 'the',
            'description_of_implementation_reason' => 'test',
            'description_of_location' => 'of',
            'guide_url' => 'marketing',
            'app_notification_copy_text' => 'information'
        ] ];
        $this->gateway->setMarketingInformationForToggle( $toggleId,
            [
                'screenshot_urls' => 'this',
                'description_of_toggle' => 'is',
                'description_of_functionality' => 'the',
                'description_of_implementation_reason' => 'test',
                'description_of_location' => 'of',
                'guide_url' => 'marketing',
                'app_notification_copy_text' => 'information'
            ] );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    private function validateMarketingInformationInTheDatabase( $expected, $toggleId )
    {
        $actual = $this->connection->fetchAll( 'SELECT * FROM `toggle_marketing_information` WHERE toggle_id = ?',
            [ $toggleId ] );

        $this->assertEquals( $expected, $actual );
    }
}
