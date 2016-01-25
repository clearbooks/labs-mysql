<?php
/**
 * Created by PhpStorm.
 * User: daniel
 * Date: 22/01/16
 * Time: 11:39
 */

namespace Clearbooks\LabsMysql\Feedback;


use Clearbooks\Labs\Feedback\Gateway\GetFeedbackForTogglesGateway;
use Clearbooks\LabsMysql\Feedback\Entity\ToggleFeedback;
use Doctrine\DBAL\Connection;

class MysqlGetAllFeedbackForToggles implements GetFeedbackForTogglesGateway
{

    /** @var Connection */
    private $connection;

    /**
     * MysqlGetAllFeedbackForToggles constructor.
     * @param Connection $connection
     */
    public function __construct(Connection $connection)
    {
        $this->connection = $connection;
    }

    /**
     * @return ToggleFeedback[]
     */
    public function getFeedbackForToggles()
    {
        $toggleFeedbackResults = $this->connection->fetchAll(
            'SELECT `toggle`.`name`, `feedback`.* FROM `feedback` JOIN `toggle` ON `toggle`.`id` = `feedback`.`toggle_id`;'
        );

        $feedback = [];
        foreach($toggleFeedbackResults as $toggleFeedback) {
            $feedback[] = $this->newToggleFeedbackFromQuery($toggleFeedback);
        }
        return $feedback;
    }

    /**
     * @param $feedback
     * @return ToggleFeedback
     */
    public function newToggleFeedbackFromQuery($feedback)
    {
        return new ToggleFeedback($feedback['name'], $feedback['mood'], $feedback['message'],
            $feedback['user_id'], $feedback['group_id']);
    }
}