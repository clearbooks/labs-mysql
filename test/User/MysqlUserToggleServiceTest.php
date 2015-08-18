<?php


use Clearbooks\LabsMysql\Release\MysqlReleaseGateway;
use Clearbooks\LabsMysql\Toggle\MysqlActivatableToggleGateway;
use Clearbooks\LabsMysql\User\MysqlUserToggleService;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Driver\Connection;
use Doctrine\DBAL\DriverManager;

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
     * @throws \Doctrine\DBAL\DBALException
     */
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
        $this->deleteAddedToggles();
        $this->deleteAddedReleases();
    }

    /**
     * @test
     */
    public function givenNoToggleAndNoUserFound_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenNoToggleFoundButWithExistentUser_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenNoUserFoundButWithExistentToggle_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithNotActivatedGivenExistentToggle_DuringDeactivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithNotActivatedGivenExistentToggle_DuringActivationAttempt_MysqlUserToggleService_ReturnsTrue()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithActivatedGivenExistentToggle_DuringActivationAttempt_MysqlUserToggleService_ReturnsFalse()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
    }

    /**
     * @test
     */
    public function givenExistentUserWithActivatedGivenExistentToggle_DuringDeactivationAttempt_MysqlUserToggleService_ReturnsTrue()
    {
        $response = (new MysqlUserToggleService())->activateToggle( "123", 123);
        $this->assertEquals(false, $response);
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
}
