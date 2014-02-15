<?php
/**
 * Created by HTML24 ApS.
 */

namespace HTML24\MBTilesGenerator\Util;


class Calculator
{
    /**
     * This is a static only class
     */
    private function __construct()
    {
    }


    /**
     * Returns the X coordinate in the TMS/OSM Mercator
     *
     * @param float $longitude
     * @param int $zoom
     * @return int
     */
    public static function longitudeToX($longitude, $zoom)
    {
        return (int)floor((($longitude + 180) / 360) * pow(2, $zoom));
    }

    /**
     * Returns the Y coordinate in the TMS Mercator
     *
     * @param float $latitude
     * @param int $zoom
     * @return number
     */
    public static function latitudeToY($latitude, $zoom)
    {
        return pow(2, $zoom) - (int)floor(
            (1 - log(tan(deg2rad($latitude)) + 1 / cos(deg2rad($latitude))) / pi()) / 2 * pow(2, $zoom)
        ) - 1;
    }

    /**
     * Flips a Y coordinate between OSM and TMS Mercator
     *
     * @param int $y
     * @param int $zoom
     * @return number
     */
    public static function flipYTmsToOsm($y, $zoom)
    {
        return pow(2, $zoom) - $y - 1;
    }
} 