<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 01/09/2015
 * Time: 12:48
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Client\Toggle\Entity\Group;
use Clearbooks\Labs\Client\Toggle\Entity\User;
use Clearbooks\Labs\Client\Toggle\UseCase\ToggleChecker;

class ToggleCheckerMock implements ToggleChecker
{
    /**
     * @var string
     */
    private $userId;
    /**
     * @var bool[]
     */
    private $activatedToggles;

    /**
     * ToggleCheckerMock constructor.
     * @param bool[] $activatedToggles
     */
    public function __construct( $activatedToggles )
    {
        $this->activatedToggles = $activatedToggles;
    }

    /**
     * @param string $toggleName
     * @param User $user
     * @param Group $group
     * @return bool is it active
     */
    public function isToggleActive( $toggleName, User $user, Group $group )
    {
        if ( $this->activatedToggles[ $toggleName ] ) {
            return true;
        }
        return false;
    }
}