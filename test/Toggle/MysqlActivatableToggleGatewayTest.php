<?php
namespace Clearbooks\LabsMysql\Toggle;

use Clearbooks\Labs\LabsTest;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 13/08/2015
 * Time: 14:38
 */
class MysqlActivatableToggleGatewayTest extends LabsTest
{
    /**
     * @var MysqlActivatableToggleGateway
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
     * @param bool $isVisible
     * @return string
     */
    private function addToggle( $name, $releaseId, $isVisible = false )
    {
        $this->addToggleToDatebase( $name, $releaseId, $isVisible );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isVisible
     * @return int
     */
    public function addToggleToDatebase( $name, $releaseId, $isVisible )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'type' => 1, 'visible' => $isVisible ] );
    }

    /**
     * @param string $toggleId
     * @param int $userId
     * @param bool $status
     */
    private function setActivatedStatusToggleForUser( $toggleId, $userId, $status = false )
    {
        $this->connection->insert( "`user_policy`",
            [ 'user_id' => $userId, 'toggle_id' => $toggleId, 'active' => $status ] );
    }

    /**
     * @return array
     */
    private function InsertDataIntoDatabase()
    {
        $id = $this->addRelease( 'Test activatable toggle 3', 'a helpful url' );
        $id2 = $this->addRelease( 'Test activatable toggle 3.1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true );
        $toggleId2 = $this->addToggle( "test2", $id, false );
        $toggleId3 = $this->addToggle( "test3", $id2, true );
        $userId = 1;

        $this->setActivatedStatusToggleForUser( $toggleId, $userId, true );
        $this->setActivatedStatusToggleForUser( $toggleId2, $userId, false );
        $this->setActivatedStatusToggleForUser( $toggleId3, $userId, true );

        $expectedToggle = new Toggle( $toggleId, "test1", $id, true );
        $expectedToggle2 = new Toggle( $toggleId2, "test2", $id, false );
        $expectedToggle3 = new Toggle( $toggleId3, "test3", $id2, true );
        return array( $expectedToggle, $expectedToggle2, $expectedToggle3 );
    }

    /**
     * @return array
     */
    private function getTogglesUsingActivatableToggleGateway()
    {
        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );
        $returnedToggle2 = $this->gateway->getActivatableToggleByName( "test2" );
        $returnedToggle3 = $this->gateway->getActivatableToggleByName( "test3" );
        return array( $returnedToggle, $returnedToggle2, $returnedToggle3 );
    }

    /**
     * @param Toggle $expectedToggle
     * @param Toggle $returnedToggle
     * @param Toggle $expectedToggle2
     * @param Toggle $returnedToggle2
     * @param Toggle $expectedToggle3
     * @param Toggle $returnedToggle3
     */
    private function validateInsertedDatabaseData( $expectedToggle, $returnedToggle, $expectedToggle2,
                                                   $returnedToggle2, $expectedToggle3, $returnedToggle3 )
    {
        $this->assertEquals( $expectedToggle, $returnedToggle );
        $this->assertEquals( $expectedToggle2, $returnedToggle2 );
        $this->assertEquals( $expectedToggle3, $returnedToggle3 );
    }

    public function setUp(): void
    {
        parent::setUp();
        $this->gateway = new MysqlActivatableToggleGateway( $this->connection );
    }

    /**
     * @test
     */
    public function givenNoExistentToggleWithProvidedName_MysqlActivatableToggleGateway_ReturnsNull()
    {
        $returnedToggle = $this->gateway->getActivatableToggleByName( "test" );
        $this->assertEquals( null, $returnedToggle );
    }

    /**
     * @test
     */
    public function givenExistentToggleButNotActivated_MysqlActivatableToggleGateway_ReturnsNotActivatedToggle()
    {
        $id = $this->addRelease( 'Test activatable toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true );
        $userId = 1;

        $status = false;
        $expectedToggle = new Toggle( $toggleId, "test1", $id, $status );

        $this->setActivatedStatusToggleForUser( $toggleId, $userId, $status );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggle, $returnedToggle );
        $this->assertEquals( $expectedToggle->isActive(), $returnedToggle->isActive() );
    }

    /**
     * @test
     */
    public function givenExistentNonVisibleToggleButNotActivated_MysqlActivatableToggleGateway_ReturnsNotActivatedToggle()
    {
        $id = $this->addRelease( 'Test invisible activatable toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, false );
        $userId = 1;

        $status = false;
        $expectedToggle = new Toggle( $toggleId, "test1", $id, $status );

        $this->setActivatedStatusToggleForUser( $toggleId, $userId, $status );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggle, $returnedToggle );
        $this->assertEquals( $expectedToggle->isActive(), $returnedToggle->isActive() );
    }

    /**
     * @test
     */
    public function givenExistentActivatedToggle_MysqlActivatableToggleGateway_ReturnsExistentActivatedToggle()
    {
        $id = $this->addRelease( 'Test activatable toggle 2', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true );
        $userId = 1;

        $status = true;
        $this->setActivatedStatusToggleForUser( $toggleId, $userId, $status );

        $expectedToggle = new Toggle( $toggleId, "test1", $id, $status );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggle, $returnedToggle );
        $this->assertEquals( $expectedToggle->isActive(), $returnedToggle->isActive() );
    }

    /**
     * @test
     */
    public function givenExistentButNonVisibleToggle_MysqlActivatableToggleGateway_ReturnsActivatedToggle()
    {
        $id = $this->addRelease( 'Test invisible activatable toggle 1', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, false );
        $userId = 1;

        $status = true;
        $expectedToggle = new Toggle( $toggleId, "test1", $id, $status );

        $this->setActivatedStatusToggleForUser( $toggleId, $userId, $status );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggle, $returnedToggle );
        $this->assertEquals( $expectedToggle->isActive(), $returnedToggle->isActive() );
    }

    /**
     * @test
     */
    public function givenMultipleExistentTogglesWithDifferentNames_MysqlActivatableToggleGateway_ReturnsRequestedExistentToggle()
    {
        $id = $this->addRelease( 'Test activatable toggle 3', 'a helpful url' );

        $toggleId = $this->addToggle( "test1", $id, true );
        $userId = 1;

        $this->addToggle( "test2", $id );

        $this->setActivatedStatusToggleForUser( $toggleId, $userId, true );

        $expectedToggle = new Toggle( $toggleId, "test1", $id, true );

        $returnedToggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expectedToggle, $returnedToggle );
    }

    /**
     * @test
     */
    public function givenMultipleExistentTogglesWithDifferentNames_DuringSelectAttempt_MysqlActivatableToggleGateway_DoesNotEffectOtherTogglesInTheDatabase()
    {
        list( $expectedToggle, $expectedToggle2, $expectedToggle3 ) = $this->InsertDataIntoDatabase();

        list( $returnedToggle, $returnedToggle2, $returnedToggle3 ) = $this->getTogglesUsingActivatableToggleGateway();

        $this->validateInsertedDatabaseData( $expectedToggle, $returnedToggle, $expectedToggle2, $returnedToggle2,
            $expectedToggle3, $returnedToggle3 );

    }
}
