<?php
namespace Clearbooks\LabsMysql\Feedback;

use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/09/2015
 * Time: 13:19
 */
class MysqlInsertFeedbackForToggleGatewayTest extends LabsTest
{
    /**
     * @var MysqlInsertFeedbackForToggleGateway
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
            'type' => 1,
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

    /**
     * @param string $toggleId
     * @param array $expectedEntries
     */
    private function validateFeedbackForToggle( $toggleId, $expectedEntries )
    {
        $actualEntries = $this->getAllFeedbackForToggle( $toggleId );
        $this->assertEquals( $expectedEntries, $actualEntries );
    }

    /**
     * @param string $toggleId
     * @return array
     */
    private function getAllFeedbackForToggle( $toggleId )
    {
        return $this->connection->fetchAll( "SELECT toggle_id, mood, message, user_id, group_id FROM `feedback` WHERE toggle_id = ?",
            [ $toggleId ] );
    }

    /**
     * @param array $expectedEntries
     */
    private function validateDatebase( $expectedEntries )
    {
        $actualEntries = $this->getAllFeedback();
        $this->assertEquals( $expectedEntries, $actualEntries );
    }

    /**
     * @return array
     */
    private function getAllFeedback()
    {
        return $this->connection->fetchAll( "SELECT toggle_id, mood, message, user_id, group_id FROM `feedback`" );
    }

    private function insertDataToDatabase()
    {
        $releaseId = $this->addRelease( "adding feedback to database", "useful" );
        $toggleid1 = $this->addToggle( "test1", $releaseId, true );
        $toggleid2 = $this->addToggle( "test2", $releaseId, true );
        $toggleid3 = $this->addToggle( "test3", $releaseId, true );

        $this->gateway->addFeedbackForToggle( $toggleid1, true, "blahh balhh", "1", "1" );
        $this->gateway->addFeedbackForToggle( $toggleid2, false, "this toggle is bad. FUUUUUUUUUUUUUUUUUUUUUUUU", "1", "1" );
        $this->gateway->addFeedbackForToggle( $toggleid3, true, "I LOVE IT!!!", "1", "1" );

        return $this->getAllFeedback();
    }

    public function setUp()
    {
        parent::setUp();
        $this->gateway = new MysqlInsertFeedbackForToggleGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoExpectedToggleExist_NoDataWillBeAddedToTheDatabaseAndGatewayReturnsFalse()
    {
        $response = $this->gateway->addFeedbackForToggle( "1", true, "this is the test", "1", "1" );
        $this->validateFeedbackForToggle( "1", [ ] );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenExistentToggle_AddNewFeedbackToTheDatabaseAndGatewayReturnsTrue()
    {
        $releaseId = $this->addRelease( "Insert Feedabck test 1", "useful" );
        $toggleId = $this->addToggle( "test", $releaseId, true );
        $response = $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK", '1', '1' );
        $this->validateFeedbackForToggle( $toggleId,
            [ [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK", 'user_id' => "1", 'group_id' => '1' ] ] );
        $this->assertTrue( $response );
    }

    /**
     * @test
     */
    public function givenExistentToggle_duringAttemptToAddMultipleFeedbacksForTheSameToggle_AddNewFeedbacksToTheDatabaseAndGatewayReturnsTrue()
    {
        $releaseId = $this->addRelease( "Insert Feedabck test 1", "useful" );
        $toggleId = $this->addToggle( "test", $releaseId, true );
        $response1 = $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK", '1', '1' );
        $response2 = $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK is awesome", '1', '1' );
        $this->validateFeedbackForToggle( $toggleId,
            [
                [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK", 'user_id' => '1', 'group_id' => '1' ],
                [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK is awesome", 'user_id' => '1', 'group_id' => '1' ]
            ] );
        $this->assertTrue( $response1 );
        $this->assertTrue( $response2 );
    }

    /**
     * @test
     */
    public function givenExistentToggleAndMultipleFeedbacks_duringInsertionOfANewFeedback_NewFeedbackWillBeAddedAndTheRestWillNotBeModified()
    {
        $expectedEntries = $this->insertDataToDatabase();

        $releaseId = $this->addRelease( "Insert Feedabck test 1", "useful" );
        $toggleId = $this->addToggle( "test", $releaseId, true );

        $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK", '1', '1' );

        $this->validateFeedbackForToggle( $toggleId,
            [ [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK", 'user_id' => '1', 'group_id' => '1' ] ] );

        $expectedEntries[] = [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK", 'user_id' => '1', 'group_id' => '1' ];

        $this->validateDatebase( $expectedEntries );
    }
}
