<?php
/**
 * Created by PhpStorm.
 * User: ryan
 * Date: 28/08/15
 * Time: 14:53
 */

namespace Clearbooks\LabsMysql\Release;


use Clearbooks\Labs\Release\Gateway\PublicReleaseGateway;
use Clearbooks\Labs\Release\Release;
use Clearbooks\Labs\Release\UseCase\GetPublicRelease\PublicRelease;
use Doctrine\DBAL\Connection;

class MysqlPublicReleaseGateway implements PublicReleaseGateway
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
     * @return PublicRelease[]
     */
    public function getAllPublicReleases()
    {
        $rows = $this->connection->executeQuery( 'SELECT * FROM `release` WHERE visibility <> 0 OR release_date <= ?', [ date( 'Y-m-d' ) ] )->fetchAllAssociative();

        $releases = [];
        foreach ( $rows as $row  ) {
            $dateTime = empty($row['release_date'])?new \DateTime():new \DateTime( $row['release_date'] );
            $releases[] = new Release( $row['id'], $row['name'], $row['info'], $dateTime, $row['visibility'] );
        }

        return $releases;

    }
}
