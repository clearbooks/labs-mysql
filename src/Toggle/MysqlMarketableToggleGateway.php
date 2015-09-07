<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 07/09/2015
 * Time: 11:20
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Toggle\Gateway\MarketableToggleGateway;
use Doctrine\DBAL\Connection;

class MysqlMarketableToggleGateway implements MarketableToggleGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlMarketableToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct( Connection $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param string $toggleId
     * @param string[] $marketingInformation
     */
    public function setMarketingInformationForToggle( $toggleId, $marketingInformation )
    {
        if ( isset( $toggleId ) ) {
            $data = $this->connection->fetchAssoc( 'SELECT * FROM `toggle_marketing_information` WHERE toggle_id = ?',
                [ $toggleId ] );
            if ( $data !== false ) {
                $this->updateMarketingInformationForToggle( $toggleId, $marketingInformation );
            } else {
                $this->connection->insert( "`toggle_marketing_information`",
                    array_merge( [ 'toggle_id' => $toggleId ], $marketingInformation ) );
            }
        }
    }

    /**
     * @param $toggleId
     * @param $marketingInformation
     */
    private function updateMarketingInformationForToggle( $toggleId, $marketingInformation )
    {
        foreach ( $marketingInformation as $marketingKey => $marketingInfo ) {
            if ( !empty( $marketingInfo ) ) {
                $this->connection->update( "`toggle_marketing_information`",
                    [ $marketingKey => $marketingInfo ], [ 'toggle_id' => $toggleId ] );
            }
        }
    }
}