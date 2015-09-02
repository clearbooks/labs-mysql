<?php
use Clearbooks\Labs\Bootstrap;
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\MysqlActivatableToggleGateway;
use Doctrine\DBAL\Connection;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 13/08/2015
 * Time: 14:38
 */
class MysqlActivatableToggleGatewayTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlActivatableToggleGateway
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
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false )
    {
        $this->addToggleToDatebase( $name, $releaseId, $isActive );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @return int
     */
    public function addToggleToDatebase( $name, $releaseId, $isActive )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => 1, 'is_active' => $isActive ] );
    }

    /**
     * @param string $toggleId
     * @param int $userId
     * @param bool $status
     */
    private function addUserActivatedToggle( $toggleId, $userId, $status = false )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $userId, 'toggle_id' => $toggleId, 'is_active' => $status ] );
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

        $this->addUserActivatedToggle( $toggleId, $userId, true );
        $this->addUserActivatedToggle( $toggleId2, $userId, false );
        $this->addUserActivatedToggle( $toggleId3, $userId, true );

        $expectedToggle = new Toggle( "test1", $id, true );
        $expectedToggle2 = new Toggle( "test2", $id, false );
        $expectedToggle3 = new Toggle( "test3", $id2, true );
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

    public function setUp()
    {
        parent::setUp();

        $this->connection = Bootstrap::getInstance()->getDIContainer()
            ->get( Connection::class );

        $this->connection->beginTransaction();
        $this->connection->setRollbackOnly();

        $this->gateway = new MysqlActivatableToggleGateway( $this->connection );
    }

    public function tearDown()
    {
        parent::tearDown();
        $this->connection->rollBack();
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

        $toggleId = $this->addToggle( "test1", $id );
        $userId = 1;
        $expectedToggle = new Toggle( "test1", $id );

        $this->addUserActivatedToggle( $toggleId, $userId, false );

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

        $this->addUserActivatedToggle( $toggleId, $userId, true );

        $expectedToggle = new Toggle( "test1", $id, true );

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

        $this->addUserActivatedToggle( $toggleId, $userId, true );

        $expectedToggle = new Toggle( "test1", $id, true );

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
