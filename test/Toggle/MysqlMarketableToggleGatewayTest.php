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
                                $descriptionOfLocation = "", $guideUrl = "", $appNotificationCopyText = "", $marketingToggleTitle = "" )
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
            !empty( $appNotificationCopyText ) ||
            !empty( $marketingToggleTitle)
        ) {
            $this->addToggleMarketingInformationToDatabase( $toggleId, $screenshotUrl, $descriptionOfToggle,
                $descriptionOfFunctionality, $descriptionOfImplementationReason,
                $descriptionOfLocation, $guideUrl, $appNotificationCopyText, $marketingToggleTitle );
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
            'type' => 1,
            'visible' => $isActive
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
     * @param $marketingToggleTitle
     * @return string
     */
    private function addToggleMarketingInformationToDatabase( $toggleId, $screenshotUrl, $descriptionOfToggle,
                                                              $descriptionOfFunctionality,
                                                              $descriptionOfImplementationReason,
                                                              $descriptionOfLocation, $guideUrl,
                                                              $appNotificationCopyText, $marketingToggleTitle )
    {
        return $this->connection->insert( "`toggle_marketing_information`", [
            'toggle_id' => $toggleId,
            'screenshot_urls' => $screenshotUrl,
            'description_of_toggle' => $descriptionOfToggle,
            'description_of_functionality' => $descriptionOfFunctionality,
            'description_of_implementation_reason' => $descriptionOfImplementationReason,
            'description_of_location' => $descriptionOfLocation, 'guide_url' => $guideUrl,
            'app_notification_copy_text' => $appNotificationCopyText, 'toggle_title' => $marketingToggleTitle
        ] );
    }

    /**
     * @param array $expected
     * @param string $toggleId
     */
    private function validateMarketingInformationInTheDatabase( $expected, $toggleId )
    {
        $actual = $this->connection->fetchAll( 'SELECT * FROM `toggle_marketing_information` WHERE toggle_id = ?',
            [ $toggleId ] );

        $this->assertEquals( $expected, $actual );
    }

    /**
     * @param $toggleId
     * @param $url
     * @param $toggleDesc
     * @param $toggleFunctionalityDesc
     * @param $implementationDesc
     * @param $locationDesc
     * @param $guideUrl
     * @param $appNotificationText
     * @param $marketingToggleTitle
     * @return array
     */
    private function makeExpectedMarketingInformation( $toggleId, $url, $toggleDesc, $toggleFunctionalityDesc,
                                                       $implementationDesc, $locationDesc, $guideUrl,
                                                       $appNotificationText, $marketingToggleTitle )
    {
        $expected = [ [
            'toggle_id' => $toggleId,
            'screenshot_urls' => $url,
            'description_of_toggle' => $toggleDesc,
            'description_of_functionality' => $toggleFunctionalityDesc,
            'description_of_implementation_reason' => $implementationDesc,
            'description_of_location' => $locationDesc,
            'guide_url' => $guideUrl,
            'app_notification_copy_text' => $appNotificationText,
            'toggle_title' => $marketingToggleTitle
        ] ];
        return $expected;
    }

    /**
     * @param $url
     * @param $toggleDesc
     * @param $toggleFunctionalityDesc
     * @param $implementationDesc
     * @param $locationDesc
     * @param $guideUrl
     * @param $appNotificationText
     * @param $marketingToggleTitle
     * @return array
     */
    private function makeMarketingInformation( $url, $toggleDesc, $toggleFunctionalityDesc,
                                               $implementationDesc, $locationDesc, $guideUrl,
                                               $appNotificationText, $marketingToggleTitle )
    {
        $expected = [
            'screenshot_urls' => $url,
            'description_of_toggle' => $toggleDesc,
            'description_of_functionality' => $toggleFunctionalityDesc,
            'description_of_implementation_reason' => $implementationDesc,
            'description_of_location' => $locationDesc,
            'guide_url' => $guideUrl,
            'app_notification_copy_text' => $appNotificationText,
            'toggle_title' => $marketingToggleTitle
        ];
        return $expected;
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

        $this->validateMarketingInformationInTheDatabase( $expected, null );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithNoMarketingInformation_whenNoInformationProvided_EmptyInformationWillBeCreatedForGivenToggle()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 1', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 1", $releaseId, true );

        $expected = $this->makeExpectedMarketingInformation( $toggleId, "", "", "", "", "", "", "", "" );
        $this->gateway->setMarketingInformationForToggle( $toggleId, [ ] );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithAllMarketingInformation_whenNoInformationProvided_NoOriginalInformationWillBeChanged()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true,
            "this", "is", "the", "test", "of", "marketing", "information", "quack" );

        $expected = $this->makeExpectedMarketingInformation( $toggleId,
            "this", "is", "the", "test", "of", "marketing", "information", "quack" );

        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( '', '', '', '', '', '', '', '' ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithNoMarketingInformation_whenAllInformationProvided_CreateNewInformationForGivenToggle()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true );

        $expected = $this->makeExpectedMarketingInformation( $toggleId,
            'this', 'is', 'the', 'test', 'of', 'marketing', 'information', "quack" );
        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( 'this', 'is', 'the', 'test', 'of', 'marketing', 'information', "quack" ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithAllMarketingInformation_whenAllInformationProvided_AllTheInformationWillBeUpdated()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true,
            "this blah...", "is blah...",
            "the blah...", "test blah...", "of blah...",
            "marketing blah...", "information blah..." );

        $expected = $this->makeExpectedMarketingInformation( $toggleId,
            'this', 'is', 'the', 'test', 'of', 'marketing', 'information', 'quack' );

        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( 'this', 'is', 'the', 'test', 'of', 'marketing', 'information', 'quack' ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithAllMarketingInformation_whenSomeInformationProvided_OnlyProvidedInformationWillBeUpdated()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true, "this blah...", "is blah...",
            "the blah...", "test blah...", "of blah...",
            "marketing blah...", "information blah..." );

        $expected = $this->makeExpectedMarketingInformation( $toggleId,
            'this blah...', 'is', 'the blah...', 'test', 'of blah...', 'marketing', 'information blah...', 'meow' );

        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( '', 'is', '', 'test', '', 'marketing', '', 'meow' ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithNoMarketingInformation_whenSomeInformationProvided_newInformationCreatedForGivenToggleWithProvidedInformation()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true );

        $expected = $this->makeExpectedMarketingInformation( $toggleId, '', 'is', '', 'test', '', 'marketing', '', '' );
        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( '', 'is', '', 'test', '', 'marketing', '', '' ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithSomeMarketingInformation_whenNoInformationProvided_NoOriginalInformationWillBeChanged()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true, "this blah...", "is blah..." );

        $expected = $this->makeExpectedMarketingInformation( $toggleId,
            "this blah...", "is blah...", "", "", "", "", "", "" );

        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( '', '', '', '', '', '', '', '' ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }

    /**
     * @test
     */
    public function givenExistantToggleIdWithSomeMarketingInformation_whenSomeInformationProvided_OnlyProvidedInformationWillBeUptated()
    {
        $releaseId = $this->addRelease( 'Test Marketing Information for toggle 2', "Very Very useful" );
        $toggleId = $this->addToggle( "MarketingToggleTest 2", $releaseId, true, "this blah...", "is blah...", "", "",
            "this isi sisiis" );

        $expected = $this->makeExpectedMarketingInformation( $toggleId,
            'this', 'is blah...', 'the', '', 'this isi sisiis', '', 'test', '' );

        $this->gateway->setMarketingInformationForToggle( $toggleId,
            $this->makeMarketingInformation( 'this', '', 'the', '', '', '', 'test', '' ) );

        $this->validateMarketingInformationInTheDatabase( $expected, $toggleId );
    }
}
