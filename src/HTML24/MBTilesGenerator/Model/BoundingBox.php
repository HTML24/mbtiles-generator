<?php
/**
 * Created by HTML24 ApS.
 */

namespace HTML24\MBTilesGenerator\Model;


class BoundingBox
{
    /**
     * @var float
     */
    protected $top_left_latitude;
    /**
     * @var float
     */
    protected $top_left_longitude;
    /**
     * @var float
     */
    protected $bottom_right_latitude;
    /**
     * @var float
     */
    protected $bottom_right_longitude;

    /**
     * @param string $bounds
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
            $this->top_left_longitude = floatval($coordinates[0]);
            $this->top_left_latitude = floatval($coordinates[1]);
            $this->bottom_right_longitude = floatval($coordinates[2]);
            $this->bottom_right_latitude = floatval($coordinates[3]);
        }
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return $this->top_left_longitude . ',' . $this->top_left_latitude . ',' . $this->bottom_right_longitude . ',' . $this->bottom_right_latitude;
    }

    /**
     * @param float $bottom_right_latitude
     */
    public function setBottomRightLatitude($bottom_right_latitude)
    {
        $this->bottom_right_latitude = $bottom_right_latitude;
    }

    /**
     * @return float
     */
    public function getBottomRightLatitude()
    {
        return $this->bottom_right_latitude;
    }

    /**
     * @param float $bottom_right_longitude
     */
    public function setBottomRightLongitude($bottom_right_longitude)
    {
        $this->bottom_right_longitude = $bottom_right_longitude;
    }

    /**
     * @return float
     */
    public function getBottomRightLongitude()
    {
        return $this->bottom_right_longitude;
    }

    /**
     * @param float $top_left_latitude
     */
    public function setTopLeftLatitude($top_left_latitude)
    {
        $this->top_left_latitude = $top_left_latitude;
    }

    /**
     * @return float
     */
    public function getTopLeftLatitude()
    {
        return $this->top_left_latitude;
    }

    /**
     * @param float $top_left_longitude
     */
    public function setTopLeftLongitude($top_left_longitude)
    {
        $this->top_left_longitude = $top_left_longitude;
    }

    /**
     * @return float
     */
    public function getTopLeftLongitude()
    {
        return $this->top_left_longitude;
    }

}