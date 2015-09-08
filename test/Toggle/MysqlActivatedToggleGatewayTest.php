<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 27/08/2015
 * Time: 13:06
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\GroupStub;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\Entity\UserStub;
use Doctrine\DBAL\Connection;

class MysqlActivatedToggleGatewayTest extends \PHPUnit_Framework_TestCase
{
    use ToggleHelperMethods;

    /**
     * @var Connection
     */
    private $connection;

    /**
     * @var MysqlActivatedToggleGateway
     */
    private $gateway;

    CONST USER_ID = "userTest";

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
            'description_of_location' => $descriptionOfLocation,
            'guide_url' => $guideUrl,
            'app_notification_copy_text' => $appNotificationCopyText
        ] );
    }

    /**
     * @return array
     */
    private function addDataToDatabase()
    {
        $releaseId = $this->addRelease( 'Test ActivatedToggleGateway', 'a helpful url' );

        $activeToggleId = $this->addToggle( "test1", $releaseId, true );
        $notActivatedToggleId = $this->addToggle( "test2", $releaseId, true, "blah", "blah" );
        $activeToggleId2 = $this->addToggle( "test3", $releaseId, true );

        return array( $releaseId, $activeToggleId, $activeToggleId2, $notActivatedToggleId );
    }

    /**
     * @param string $releaseId
     * @param string $toggleId
     * @param string $toggleId2
     * @param string $toggleId3
     * @param string $notActivateToggleId
     */
    protected function validateDatabaseData( $releaseId, $toggleId, $toggleId2, $toggleId3, $notActivateToggleId )
    {
        $expectedResult = [
            new Toggle( $toggleId, "test1", $releaseId, true ),
            new Toggle( $notActivateToggleId, "test2", $releaseId, true, "blah", "blah" ),
            new Toggle( $toggleId2, "test3", $releaseId, true ),
            new Toggle( $toggleId3, "test4", $releaseId, true, "this", "is", "the", "test", "of",
                "marketing", "information" ) ];

        $data = $this->connection->fetchAll( 'SELECT *, toggle.id as toggleId FROM `toggle` LEFT JOIN `toggle_marketing_information` ON toggle.id = toggle_marketing_information.toggle_id WHERE toggle.name IN (?,?,?,?)',
            [ $expectedResult[ 0 ]->getName(), $expectedResult[ 1 ]->getName(), $expectedResult[ 2 ]->getName(), $expectedResult[ 3 ]->getName() ] );
        $actualResult = $this->getAllTogglesFromGivenSqlResult( $data );

        $this->assertEquals( $expectedResult, $actualResult );
    }

    public function setUp()
    {
        parent::setUp();

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $activatedToggles = [ "test1" => true, "test2" => false, "test3" => true, "test4" => true ];
        $this->gateway = new MysqlActivatedToggleGateway( $this->connection,
            new ToggleCheckerMock( $activatedToggles ), new UserStub(), new GroupStub() );

    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    /**
     * @test
     */
    public function givenNoActivatedTogglesFound_ReturnsEmptyArray()
    {
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );
        $this->assertEquals( [ ], $response );
    }

    /**
     * @test
     */
    public function givenExistentActivatedAndNotActivatedToggles_ReturnsArrayOfActivatedToggles()
    {
        list( $releaseId, $toggleId, $toggleId2 ) = $this->addDataToDatabase();

        $expectedResult = [ new Toggle( $toggleId, "test1", $releaseId, true ), new Toggle( $toggleId2, "test3",
            $releaseId, true ) ];
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );

        $this->assertEquals( $expectedResult, $response );
    }

    /**
     * @test
     */
    public function givenExistentActivatedAndNotActivatedTogglesWithMarketingInformation_ReturnsArrayOfActivatedTogglesWithValidInformation()
    {
        list( $releaseId, $toggleId, $toggleId2 ) = $this->addDataToDatabase();

        $toggleId3 = $this->addToggle( "test4", $releaseId, true, "this", "is", "the", "test", "of", "marketing",
            "information" );

        $expectedResult = [
            new Toggle( $toggleId, "test1", $releaseId, true ),
            new Toggle( $toggleId2, "test3", $releaseId, true ),
            new Toggle( $toggleId3, "test4", $releaseId, true, "this", "is", "the", "test", "of",
                "marketing", "information" ) ];
        $response = $this->gateway->getAllMyActivatedToggles( self::USER_ID );

        $this->assertEquals( $expectedResult, $response );
    }

    /**
     * @test
     */
    public function givenExistentActivatedAndNotActivatedToggles_afterGettingAllActivatedToggle_noOtherTogglesWillBeModified()
    {
        list( $releaseId, $toggleId, $toggleId2, $notActivateToggleId ) = $this->addDataToDatabase();

        $toggleId3 = $this->addToggle( "test4", $releaseId, true, "this", "is", "the", "test", "of", "marketing",
            "information" );

        $expectedGatewayResult = [
            new Toggle( $toggleId, "test1", $releaseId, true ),
            new Toggle( $toggleId2, "test3", $releaseId, true ),
            new Toggle( $toggleId3, "test4", $releaseId, true, "this", "is", "the", "test", "of",
                "marketing", "information" ) ];
        $gatewayResult = $this->gateway->getAllMyActivatedToggles( self::USER_ID );

        $this->assertEquals( $expectedGatewayResult, $gatewayResult );

        $this->validateDatabaseData( $releaseId, $toggleId, $toggleId2, $toggleId3, $notActivateToggleId );
    }
}
