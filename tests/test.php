<?php
/**
 * Created by HTML24 ApS.
 * User: dennis
 * Date: 2014-02-13
 * Time: 10:19
 */

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use MBTilesGenerator\MBTilesGenerator;

$tile_source = new \MBTilesGenerator\TileSources\RemoteCachingTileSource('http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg', array(1,2,3,4));
$tile_source->setAttribution('Data, imagery and map information provided by MapQuest, OpenStreetMap <http://www.openstreetmap.org/copyright> and contributors, ODbL <http://wiki.openstreetmap.org/wiki/Legal_FAQ#I_would_like_to_use_OpenStreetMap_maps._How_should_I_credit_you.#>.');

$tile_generator = new MBTilesGenerator($tile_source);

$bounding_box = new \MBTilesGenerator\Model\BoundingBox('12.6061,55.6615,12.6264,55.6705');

$tile_generator->generate($bounding_box, '/var/www/MBTilesGenerator/tests/output.mbtiles');

/*
$tile = $tile_generator->coordinatesToTile(12.6061, 55.6615, 4);
var_dump($tile);
var_dump(\MBTilesGenerator\Util\Calculator::flipYTmsToOsm($tile->y, $tile->z));
var_dump(\MBTilesGenerator\Util\Calculator::flipYTmsToOsm(5, 4));
*/

echo "\nMax memory usage: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MiB\n";