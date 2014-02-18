<?php
/**
 * Created by HTML24 ApS.
 */

namespace HTML24\MBTilesGenerator\Model;


class BoundingBox
{
    /**
     * Latitude of top
     * @var float
     */
    protected $top;
    /**
     * Longitude of left
     * @var float
     */
    protected $left;
    /**
     * Latitude of bottom
     * @var float
     */
    protected $bottom;
    /**
     * Longitude of right
     * @var float
     */
    protected $right;

    /**
     * @param string $bounds as: "left, bottom, right, top"
     *
     * @throws \Exception
     */
    public function __construct($bounds = null)
    {
        if ($bounds !== null) {
            $coordinates = explode(',', $bounds);
            if (count($coordinates) !== 4) {
                throw new \Exception('Invalid $bounds input');
            }
            $this->left = floatval($coordinates[0]);
            $this->bottom = floatval($coordinates[1]);
            $this->right = floatval($coordinates[2]);
            $this->top = floatval($coordinates[3]);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->left . ',' . $this->bottom . ',' . $this->right . ',' . $this->top;
    }

    /**
     * @param float $bottom
     */
    public function setBottom($bottom)
    {
        $this->bottom = $bottom;
    }

    /**
     * @return float
     */
    public function getBottom()
    {
        return $this->bottom;
    }

    /**
     * @param float $left
     */
    public function setLeft($left)
    {
        $this->left = $left;
    }

    /**
     * @return float
     */
    public function getLeft()
    {
        return $this->left;
    }

    /**
     * @param float $right
     */
    public function setRight($right)
    {
        $this->right = $right;
    }

    /**
     * @return float
     */
    public function getRight()
    {
        return $this->right;
    }

    /**
     * @param float $top
     */
    public function setTop($top)
    {
        $this->top = $top;
    }

    /**
     * @return float
     */
    public function getTop()
    {
        return $this->top;
    }



}