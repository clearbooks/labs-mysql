<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 14/09/2015
 * Time: 13:22
 */

namespace Clearbooks\LabsMysql\Feedback;


use Clearbooks\Labs\Feedback\Gateway\InsertFeedbackForToggleGateway;
use Doctrine\DBAL\Connection;

class MysqlInsertFeedbackForToggleGateway implements InsertFeedbackForToggleGateway
{
    /**
     * @var Connection
     */
    private $connection;

    /**
     * MysqlInsertFeedbackForToggleGateway constructor.
     * @param Connection $connection
     */
    public function __construct( $connection )
    {
        $this->connection = $connection;
    }

    /**
     * @param string $toggleId
     * @param bool $mood
     * @param string $message
     * @return void
     */
    public function addFeedbackForToggle( $toggleId, $mood, $message )
    {
        try {
            $this->connection->insert( '`feedback`',
                [ 'toggle_id' => $toggleId, 'mood' => $mood, 'message' => $message ] );
        }catch (\Exception $e){

        }
    }
}