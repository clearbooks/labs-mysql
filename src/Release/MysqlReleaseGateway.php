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
     * @param string $releaseName
     * @param string $url
     * @param bool $isVisible
     * @param \DateTimeInterface $releaseDate
     * @return int
     */
    public function addRelease( $releaseName, $url, $isVisible = true, $releaseDate = null )
    {
        $releaseDate = $releaseDate ?: new \DateTime();

        return $this->connection->insert( "`release`", [ 'name' => $releaseName, 'info' => $url,
            'visibility' => $isVisible, 'release_date' => $releaseDate->format('Y-m-d')] );
    }

    /**
     * @param $releaseId
     * @return Release
     */
    public function getRelease( $releaseId )
    {
        $releaseArray = $this->connection->fetchAssoc( 'SELECT * FROM `release` WHERE id = ?', [ $releaseId ] );
        if ( empty( $releaseArray ) ) {
            return null;
        }

        return new Release( $releaseArray[ 'id' ], $releaseArray[ 'name' ], $releaseArray[ 'info' ], new \DateTime(), (bool)$releaseArray['visibility'] );
    }

    /**
     * @return Release[]
     */
    public function getAllReleases()
    {
        $releases = [ ];
        foreach ( $this->connection->fetchAll( 'SELECT * FROM `release`' ) as $row ) {
            $releases[] = new Release( $row[ 'id' ], $row[ 'name' ], $row[ 'info' ], new \DateTime() );
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
        $queryBuilder->execute();
        return true;
    }

    /**
     * @return Release[]
     */
    public function getAllFutureVisibleReleases()
    {
        $rows = $this->connection->fetchAll(
            "SELECT * FROM `release` WHERE `visibility` = 1 AND `release_date` > ?", [(new \DateTime())->format('Y-m-d')]
        );
        $releases = [];
        foreach($rows as $release) {
            $releases[] = new Release(
                $release["id"], $release["name"],
                $release["info"], (new \DateTime($release["release_date"]))
            );
        }
        return $releases;
    }
}
//EOF MysqlReleaseGateway.php