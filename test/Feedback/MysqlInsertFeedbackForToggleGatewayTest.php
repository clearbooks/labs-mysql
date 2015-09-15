<?php
namespace Clearbooks\LabsMysql\Feedback;

use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Doctrine\DBAL\Connection;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/09/2015
 * Time: 13:19
 */
class MysqlInsertFeedbackForToggleGatewayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection
     */
    private $connection;

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
        return $this->connection->fetchAll( "SELECT toggle_id, mood, message FROM `feedback` WHERE toggle_id = ?", [ $toggleId ] );
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
        return $this->connection->fetchAll( "SELECT toggle_id, mood, message FROM `feedback`" );
    }

    private function insertDataToDatabase()
    {
        $releaseId = $this->addRelease( "adding feedback to database", "useful" );
        $toggleid1 = $this->addToggle( "test1", $releaseId, true );
        $toggleid2 = $this->addToggle( "test2", $releaseId, true );
        $toggleid3 = $this->addToggle( "test3", $releaseId, true );

        $this->gateway->addFeedbackForToggle( $toggleid1, true, "blahh balhh" );
        $this->gateway->addFeedbackForToggle( $toggleid2, false, "this toggle is bad. FUUUUUUUUUUUUUUUUUUUUUUUU" );
        $this->gateway->addFeedbackForToggle( $toggleid3, true, "I LOVE IT!!!" );

        return $this->getAllFeedback();
    }

    public function setUp()
    {
        parent::setUp();

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $this->gateway = new MysqlInsertFeedbackForToggleGateway( $this->connection );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
    }

    /**
     * @test
     */
    public function givenNoExpectedToggleExist_NoDataWillBeAddedToTheDatabase()
    {
        $this->gateway->addFeedbackForToggle( "1", true, "this is the test" );
        $this->validateFeedbackForToggle( "1", [ ] );
    }

    /**
     * @test
     */
    public function givenExistentToggle_AddNewFeedbackToTheDatabase()
    {
        $releaseId = $this->addRelease( "Insert Feedabck test 1", "useful" );
        $toggleId = $this->addToggle( "test", $releaseId, true );
        $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK" );
        $this->validateFeedbackForToggle( $toggleId,
            [ [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK" ] ] );
    }

    /**
     * @test
     */
    public function givenExistentToggle_duringAttemptToAddMultipleFeedbacksForTheSameToggle_AddNewFeedbacksToTheDatabase()
    {
        $releaseId = $this->addRelease( "Insert Feedabck test 1", "useful" );
        $toggleId = $this->addToggle( "test", $releaseId, true );
        $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK" );
        $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK is awesome" );
        $this->validateFeedbackForToggle( $toggleId,
            [ [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK" ], [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK is awesome" ] ] );
    }

    /**
     * @test
     */
    public function givenExistentToggleAndMultipleFeedbacks_duringInsertionOfANewFeedback_NewFeedbackWillBeAddedAndTheRestWillNotBeModified()
    {
        $expectedEntries = $this->insertDataToDatabase();

        $releaseId = $this->addRelease( "Insert Feedabck test 1", "useful" );
        $toggleId = $this->addToggle( "test", $releaseId, true );

        $this->gateway->addFeedbackForToggle( $toggleId, true, "BROLLY FEEDBACK" );

        $this->validateFeedbackForToggle( $toggleId,
            [ [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK" ] ] );

        $expectedEntries[] = [ 'toggle_id' => $toggleId, 'mood' => true, 'message' => "BROLLY FEEDBACK" ];

        $this->validateDatebase( $expectedEntries );
    }
}
