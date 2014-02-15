<?php
/**
 * Created by HTML24 ApS.
 * User: dennis
 * Date: 2014-02-15
 * Time: 21:21
 */

require_once __DIR__ . '/../vendor/autoload.php'; // Autoload files using Composer autoload

use HTML24\MBTilesGenerator\MBTilesGenerator;
use HTML24\MBTilesGenerator\TileSources\MBTilesTileSource;
use HTML24\MBTilesGenerator\Model\BoundingBox;

$tile_source = new MBTilesTileSource('/var/www/MBTilesGenerator/tests/larger-output.mbtiles');

$tile_generator = new MBTilesGenerator($tile_source);

$bounding_box = new BoundingBox('12.6061,55.6615,12.6264,55.6705');

$tile_generator->setMaxZoom(18);
$tile_generator->generate($bounding_box, '/var/www/MBTilesGenerator/tests/sub-output.mbtiles');


echo "\nMax memory usage: " . number_format(memory_get_peak_usage() / 1024 / 1024, 2) . " MiB\n";