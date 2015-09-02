<?php
/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 01/09/2015
 * Time: 12:48
 */

namespace Clearbooks\LabsMysql\Toggle;


use Clearbooks\Labs\Client\Toggle\UseCase\IsToggleActive;

class ToggleCheckerMock implements IsToggleActive
{
    /**
     * @var
     */
    private $userId;
    /**
     * @var
     */
    private $activatedToggles;

    /**
     * ToggleCheckerMock constructor.
     * @param string $userId
     * @param bool[] $activatedToggles
     */
    public function __construct( $userId, $activatedToggles )
    {
        $this->userId = $userId;
        $this->activatedToggles = $activatedToggles;
    }

    /**
     * @param string $toggleName
     * @return bool is it active
     */
    public function isToggleActive( $toggleName )
    {
        return $this->activatedToggles[ $toggleName ];
    }
}