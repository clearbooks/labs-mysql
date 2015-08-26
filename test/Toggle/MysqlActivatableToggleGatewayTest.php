<?php
use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\MysqlActivatableToggleGateway;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception\InvalidArgumentException;

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
     * @throws InvalidArgumentException
     */
    private function deleteAddedReleases()
    {
        $this->connection->delete( '`release`', [ '*' ] );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteAddedToggles()
    {
        $this->connection->delete( '`toggle`', [ '*' ] );
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
     * @return array
     */
    private function InsertDataIntoDatabase()
    {
        $id = $this->addRelease( 'Test activatable toggle 3', 'a helpful url' );
        $id2 = $this->addRelease( 'Test activatable toggle 3.1', 'a helpful url' );

        $toggle_id = $this->addToggle( "test1", $id, true );
        $toggle_id2 = $this->addToggle( "test2", $id, false );
        $toggle_id3 = $this->addToggle( "test3", $id2, true );
        $user_id = 1;

        $this->addUserActivatedToggle( $toggle_id, $user_id, true );
        $this->addUserActivatedToggle( $toggle_id2, $user_id, false );
        $this->addUserActivatedToggle( $toggle_id3, $user_id, true );

        $expected_toggle = new Toggle( "test1", $id, true );
        $expected_toggle2 = new Toggle( "test2", $id, false );
        $expected_toggle3 = new Toggle( "test3", $id2, true );
        return array( $expected_toggle, $expected_toggle2, $expected_toggle3 );
    }

    /**
     * @return array
     */
    private function getTogglesUsingActivatableToggleGateway()
    {
        $returned_toggle = $this->gateway->getActivatableToggleByName( "test1" );
        $returned_toggle2 = $this->gateway->getActivatableToggleByName( "test2" );
        $returned_toggle3 = $this->gateway->getActivatableToggleByName( "test3" );
        return array( $returned_toggle, $returned_toggle2, $returned_toggle3 );
    }

    /**
     * @param Toggle $expected_toggle
     * @param Toggle $returned_toggle
     * @param Toggle $expected_toggle2
     * @param Toggle $returned_toggle2
     * @param Toggle $expected_toggle3
     * @param Toggle $returned_toggle3
     */
    private function validateInsertedDatabaseData( $expected_toggle, $returned_toggle, $expected_toggle2,
                                                   $returned_toggle2, $expected_toggle3, $returned_toggle3 )
    {
        $this->assertEquals( $expected_toggle, $returned_toggle );
        $this->assertEquals( $expected_toggle2, $returned_toggle2 );
        $this->assertEquals( $expected_toggle3, $returned_toggle3 );
    }

    /**
     * @throws InvalidArgumentException
     */
    private function deleteAddedUserActivatedToggles()
    {
        $this->connection->delete( '`user_activated_toggle`', [ '*' ] );
    }

    /**
     * @param string $toggle_id
     * @param int $user_id
     * @param bool $status
     */
    private function addUserActivatedToggle( $toggle_id, $user_id, $status = false )
    {
        $this->connection->insert( "`user_activated_toggle`",
            [ 'user_id' => $user_id, 'toggle_id' => $toggle_id, 'is_active' => $status ] );
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
        $this->gateway = new MysqlActivatableToggleGateway( $this->connection );
    }

    public function tearDown()
    {
        $this->deleteAddedUserActivatedToggles();
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

    /**
     * @test
     */
    public function givenNoExistentToggleWithProvidedName_MysqlActivatableToggleGateway_ReturnsNull()
    {
        $returned_toggle = $this->gateway->getActivatableToggleByName( "test" );
        $this->assertEquals( null, $returned_toggle );
    }

    /**
     * @test
     */
    public function givenExistentToggleButNotActivated_MysqlActivatableToggleGateway_ReturnsNotActivatedToggle()
    {
        $id = $this->addRelease( 'Test activatable toggle 1', 'a helpful url' );

        $toggle_id = $this->addToggle( "test1", $id );
        $user_id = 1;
        $expected_toggle = new Toggle( "test1", $id );

        $this->addUserActivatedToggle( $toggle_id, $user_id, false );

        $returned_toggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expected_toggle, $returned_toggle );
        $this->assertEquals( $expected_toggle->isActive(), $returned_toggle->isActive() );
    }

    /**
     * @test
     */
    public function givenExistentActivatedToggle_MysqlActivatableToggleGateway_ReturnsExistentActivatedToggle()
    {
        $id = $this->addRelease( 'Test activatable toggle 2', 'a helpful url' );

        $toggle_id = $this->addToggle( "test1", $id, true );
        $user_id = 1;

        $this->addUserActivatedToggle( $toggle_id, $user_id, true );

        $expected_toggle = new Toggle( "test1", $id, true );

        $returned_toggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expected_toggle, $returned_toggle );
        $this->assertEquals( $expected_toggle->isActive(), $returned_toggle->isActive() );
    }

    /**
     * @test
     */
    public function givenMultipleExistentTogglesWithDifferentNames_MysqlActivatableToggleGateway_ReturnsRequestedExistentToggle()
    {
        $id = $this->addRelease( 'Test activatable toggle 3', 'a helpful url' );

        $toggle_id = $this->addToggle( "test1", $id, true );
        $user_id = 1;

        $this->addToggle( "test2", $id );

        $this->addUserActivatedToggle( $toggle_id, $user_id, true );

        $expected_toggle = new Toggle( "test1", $id, true );

        $returned_toggle = $this->gateway->getActivatableToggleByName( "test1" );

        $this->assertEquals( $expected_toggle, $returned_toggle );
    }

    /**
     * @test
     */
    public function givenMultipleExistentTogglesWithDifferentNames_DuringSelectAttempt_MysqlActivatableToggleGateway_DoesNotEffectOtherTogglesInTheDatabase()
    {
        list( $expected_toggle, $expected_toggle2, $expected_toggle3 ) = $this->InsertDataIntoDatabase();

        list( $returned_toggle, $returned_toggle2, $returned_toggle3 ) = $this->getTogglesUsingActivatableToggleGateway();

        $this->validateInsertedDatabaseData( $expected_toggle, $returned_toggle, $expected_toggle2, $returned_toggle2,
            $expected_toggle3, $returned_toggle3 );

    }
}
