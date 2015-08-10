<?php
/**
 * @author: Ryan Wood <ryanw@clearbooks.co.uk>
 * @created: 10/08/15
 */

namespace Clearbooks\LabsMysql\Release;


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
        $this->gateway->addRelease( $releaseName, $url );
        $id = $this->connection->lastInsertId( "`release`" );
        $expectedRelease = array(
            'id' => $id,
            'name' => $releaseName,
            'info' => $url
        );
        $this->assertEquals( $expectedRelease, $this->connection->fetchAssoc( 'SELECT * FROM `release` WHERE `id` = ?', [ $id ] ) );
    }
}
//EOF MysqlReleaseGatewayTest.php