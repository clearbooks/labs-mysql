<?php
/**
 * @author: Ryan Wood <ryanw@clearbooks.co.uk>
 * @created: 07/08/15
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Release\Gateway\ReleaseGateway;
use Clearbooks\Labs\Release\Release;
use Doctrine\DBAL\Driver\Connection;

class MysqlReleaseGateway implements ReleaseGateway
{

    /**
     * @var \Doctrine\DBAL\Connection
     */
    private $connection;

    /**
     * Construct this MysqlReleaseGateway.
     * @author Ryan Wood <ryanw@clearbooks.co.uk>
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param $releaseName
     * @param $url
     * @return int
     */
    public function addRelease( $releaseName, $url )
    {
        return $this->connection->insert( "`release`", [ 'name' => $releaseName, 'info' => $url ] );
    }

    /**
     * @param $releaseId
     * @return Release
     */
    public function getRelease( $releaseId )
    {
        $releaseArray = $this->connection->fetchAssoc( 'SELECT * FROM `release` WHERE id = ?', [ $releaseId ] );
        if ( empty( $releaseArray ) ){
            return null;
        }

        return new Release( $releaseArray['name'], $releaseArray['info'], new \DateTime() );
    }

    /**
     * @return Release[]
     */
    public function getAllReleases()
    {
        $releases = [];
        foreach ( $this->connection->fetchAll( 'SELECT * FROM `release`' ) as $row ){
            $releases[] = new Release( $row['name'], $row['info'], new \DateTime() );
        }
        return $releases;
    }
}
//EOF MysqlReleaseGateway.php