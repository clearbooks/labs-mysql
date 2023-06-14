<?php
/**
 * @author: Ryan Wood <ryanw@clearbooks.co.uk>
 * @created: 07/08/15
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Release\Gateway\ReleaseGateway;
use Clearbooks\Labs\Release\Release;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Query\QueryBuilder;

class MysqlReleaseGateway implements ReleaseGateway
{

    /**
     * @var Connection
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
        $releaseArray = $this->connection->executeQuery( 'SELECT * FROM `release` WHERE id = ?', [ $releaseId ] )->fetchAssociative();
        if ( empty( $releaseArray ) ) {
            return null;
        }

        return new Release( $releaseArray[ 'id' ], $releaseArray[ 'name' ], $releaseArray[ 'info' ], (new \DateTime())->modify('midnight'), (bool)$releaseArray['visibility'] );
    }

    /**
     * @return Release[]
     */
    public function getAllReleases()
    {
        $releases = [ ];
        foreach ( $this->connection->executeQuery( 'SELECT * FROM `release`' )->fetchAllAssociative() as $row ) {
            $releases[] = new Release( $row[ 'id' ], $row[ 'name' ], $row[ 'info' ], (new \DateTime())->modify('midnight') );
        }
        return $releases;
    }

    /**
     * @param string $releaseId
     * @param string $releaseName
     * @param string $releaseUrl
     * @return bool
     */
    public function editRelease( $releaseId, $releaseName, $releaseUrl )
    {
        if ( empty( $this->getRelease( $releaseId ) ) ) {
            return false;
        }
        $queryBuilder = new QueryBuilder( $this->connection );
        $queryBuilder
            ->update( '`release`' )
            ->set( 'name', '?' )
            ->set( 'info', '?' )
            ->where( 'id = ?' )
            ->setParameter( 0, $releaseName )
            ->setParameter( 1, $releaseUrl )
            ->setParameter( 2, $releaseId );
        $queryBuilder->executeStatement();
        return true;
    }

    /**
     * @return Release[]
     */
    public function getAllFutureVisibleReleases()
    {
    }
}
//EOF MysqlReleaseGateway.php
