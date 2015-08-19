<?php

namespace Clearbooks\LabsMysql\User;

use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\Entity\Toggle;
use Clearbooks\LabsMysql\Toggle\MysqlActivatableToggleGateway;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;
use PHPUnit_Framework_TestCase;

/**
 * Created by PhpStorm.
 * User: Vovaxs
 * Date: 18/08/2015
 * Time: 11:57
 */
class MysqlUserToggleServiceTest extends PHPUnit_Framework_TestCase
{
    /**
     * @var Connection $connection
     */
    private $connection;


    /**
     * @var MysqlUserToggleService
     */
    private $gateway;

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
        $this->gateway = new MysqlUserToggleService( $this->connection );
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
    public function givenNoToggleAndNoUserFound_DuringActivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $response = $this->gateway->activateToggle( "123", 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenNoToggleAndNoUserFound_DuringDeActivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $response = $this->gateway->deActivateToggle( "123", 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenExistentUserWithNotActivatedGivenExistentToggle_DuringDeActivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $releaseName = 'Test user toggle service 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test1", $id, true );
        //toggle exists but not in the user_activated_toggle table becuase it has not been activated yet

        $response = $this->gateway->deActivateToggle($toggle_id, 1);
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenExistentUserWithNotActivatedGivenExistentToggle_DuringActivationAttempt_MysqlUserToggleService_ReturnsTrue()
    {
        $releaseName = 'Test user toggle service 2';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test2", $id, true );

        $response = $this->gateway->activateToggle( $toggle_id, 1 );
        $this->assertTrue( $response );
    }

    /**
     * @test
     */
    public function givenExistentUserWithActivatedGivenExistentToggle_DuringActivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $releaseName = 'Test user toggle service 3';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test3", $id, true );

        $this->addUserActivatedToggle($toggle_id, 1);

        $response = $this->gateway->activateToggle( $toggle_id, 1 );
        $this->assertFalse( $response );
    }

    /**
     * @test
     */
    public function givenExistentUserWithActivatedGivenExistentToggle_DuringDeActivationAttempt_MysqlUserToggleService_ReturnsTrue()
    {
        $releaseName = 'Test user toggle service 4';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $toggle_id = $this->addToggle( "test4", $id, true );

        $this->addUserActivatedToggle($toggle_id, 1);

        $response = $this->gateway->deActivateToggle( $toggle_id, 1 );
        $this->assertTrue( $response );

    }

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
     * @throws InvalidArgumentException
     */
    private function deleteAddedUserActivatedToggles()
    {
        $this->connection->delete( '`user_activated_toggle`', [ '*' ] );
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
     * @param int $toggle_type
     * @return string
     */
    private function addToggle( $name, $releaseId, $isActive = false, $toggle_type = 1 )
    {
        $this->addToggleToDatabase( $name, $releaseId, $isActive, $toggle_type );
        return $this->connection->lastInsertId( "`toggle`" );
    }

    /**
     * @param string $name
     * @param string $releaseId
     * @param bool $isActive
     * @param int $toggle_type
     * @return int
     */
    public function addToggleToDatabase( $name, $releaseId, $isActive, $toggle_type )
    {
        return $this->connection->insert( "`toggle`",
            [ 'name' => $name, 'release_id' => $releaseId, 'toggle_type' => $toggle_type, 'is_active' => $isActive ] );
    }

    private function addUserActivatedToggle( $toggle_id, $user_id )
    {
        $this->connection->insert( "`user_activated_toggle`", [ 'user_id' => $user_id, 'toggle_id' => $toggle_id ] );
    }
}
