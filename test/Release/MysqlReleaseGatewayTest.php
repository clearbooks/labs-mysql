<?php
/**
 * @author: Ryan Wood <ryanw@clearbooks.co.uk>
 * @created: 10/08/15
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Release\Release;
use Doctrine\DBAL\Configuration;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DriverManager;

class MysqlReleaseGatewayTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var MysqlReleaseGateway
     */
    private $gateway;

    /**
     * @var Connection
     */
    private $connection;

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
        $this->gateway = new MysqlReleaseGateway( $this->connection );
    }

    public function testAddRelease()
    {
        $releaseName = 'Test release 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $expectedRelease = array(
            'id' => $id,
            'name' => $releaseName,
            'info' => $url
        );
        $this->assertEquals( $expectedRelease, $this->connection->fetchAssoc( 'SELECT * FROM `release` WHERE `id` = ?', [ $id ] ) );

        // Teardown
        $this->deleteAddedRelease( $id );
    }

    /**
     * @test
     */
    public function givenNoReleases_getReleaseReturnsNull()
    {
        $this->assertNull( $this->gateway->getRelease( 'blergh' ) );
    }

    /**
     * @test
     */
    public function givenRelease_WhenGetReleaseCalledWithWrongId_ReturnsNull()
    {
        $id = $this->addRelease( 'Test release 1', 'a helpful url' );
        $this->assertNull( $this->gateway->getRelease( 'blergh' ) );
        $this->deleteAddedRelease( $id );
    }

    /**
     * @test
     */
    public function givenRelease_getReleaseWithCorrectId_ReturnsRelease()
    {
        $releaseName = 'Test release 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $release = $this->gateway->getRelease( $id );
        $expectedRelease = new Release( $releaseName, $url, new \DateTime() );

        $this->assertEquals( $expectedRelease->getReleaseName(), $release->getReleaseName() );
        $this->assertEquals( $expectedRelease->getReleaseInfoUrl(), $release->getReleaseInfoUrl() );

        $this->deleteAddedRelease( $id );
    }

    /**
     * @test
     */
    public function givenNoReleases_getAllReleasesReturnsEmptyArray()
    {
        $this->assertEquals( [], $this->gateway->getAllReleases() );
    }

    /**
     * @test
     */
    public function givenRelease_getAllReleasesReturnsReleaseInArray()
    {
        $releaseName = 'Test release 1';
        $url = 'a helpful url';
        $id = $this->addRelease( $releaseName, $url );

        $expectedRelease = new Release( $releaseName, $url, new \DateTime() );
        $releases =  $this->gateway->getAllReleases();

        $this->assertEquals( $expectedRelease->getReleaseName(), $releases[0]->getReleaseName() );
        $this->assertEquals( $expectedRelease->getReleaseInfoUrl(), $releases[0]->getReleaseInfoUrl() );

        $this->deleteAddedRelease( $id );
    }

    /**
     * @test
     */
    public function givenMultipleReleases_getAllReleasesReturnsArrayOfReleases()
    {
        $releasesToDelete = array(
              $this->addRelease( 'Test release 1', 'a helpful url' ),
              $this->addRelease( 'Test release 2', 'another helpful url' ),
              $this->addRelease( 'Test release 3', 'a third helpful url' )
        );
        $releases =  $this->gateway->getAllReleases();

        $this->assertCount( 3, $releases );

        foreach ( $releasesToDelete as $id ){
            $this->deleteAddedRelease( $id );
        }
    }

    /**
     * @param $id
     * @throws \Doctrine\DBAL\Exception\InvalidArgumentException
     */
    private function deleteAddedRelease( $id )
    {
        $this->connection->delete( '`release`', [ 'id' => $id ] );
    }

    /**
     * @param $releaseName
     * @param $url
     * @return string
     */
    private function addRelease( $releaseName, $url )
    {
        $this->gateway->addRelease( $releaseName, $url );
        return $this->connection->lastInsertId( "`release`" );
    }
}
//EOF MysqlReleaseGatewayTest.php