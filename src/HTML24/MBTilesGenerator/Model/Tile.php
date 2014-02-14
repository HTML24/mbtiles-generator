<?php
/**
 * Created by HTML24 ApS.
 * User: dennis
 * Date: 2014-02-13
 * Time: 11:31
 */

namespace HTML24\MBTilesGenerator\Model;


class Tile
{
    /**
     * X Coordinate in the TMS Mercator
     * @var int
     */
    public $x;
    /**
     * Y Coordinate in the TMS Mercator
     * @var int
     */
    public $y;
    /**
     * @var int
     */
    public $z;

    public function __construct($x = null, $y = null, $z = null)
    {
        $this->x = $x;
        $this->y = $y;
        $this->z = $z;
    }


} 