<?php
namespace Clearbooks\LabsMysql\Toggle\Entity;

use Clearbooks\Labs\Toggle\Entity\ActivatableToggle;
use Clearbooks\Labs\Toggle\Entity\UserToggle;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 14:14
 */
class Toggle implements \Clearbooks\Labs\Toggle\Entity\MarketableToggle, UserToggle, ActivatableToggle
{
    /**
     * @var
     */
    private $name;
    /**
     * @var
     */
    private $releaseId;
    /**
     * @var
     */
    private $isActive;

    /**
     * Toggle constructor.
     * @param stirng $name
     * @param stirng $releaseId
     * @param bool $isActive
     */
    public function __construct( $name, $releaseId, $isActive = false )
    {
        $this->name = $name;
        $this->releaseId = $releaseId;
        $this->isActive = $isActive;
    }


    /**
     * @return string
     */
    public function getName()
    {
        return $this->name;
    }

    /**
     * @return string
     */
    public function getRelease()
    {
        return $this->releaseId;
    }

    /**
     * @return bool
     */
    public function isActive()
    {
        return $this->releaseId;
    }
}