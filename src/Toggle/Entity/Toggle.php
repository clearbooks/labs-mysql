<?php
namespace Clearbooks\LabsMysql\Toggle\Entity;

use Clearbooks\Labs\Toggle\Entity\ReleasableToggle;

/**
 * Created by PhpStorm.
 * User: Volodymyr
 * Date: 12/08/2015
 * Time: 14:14
 */
class Toggle implements \Clearbooks\Labs\Toggle\Entity\MarketableToggle, ReleasableToggle
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
     * Toggle constructor.
     * @param stirng $name
     * @param stirng $releaseId
     */
    public function __construct($name, $releaseId)
    {
        $this->name = $name;
        $this->releaseId = $releaseId;
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
}