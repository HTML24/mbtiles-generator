# MBTilesGenerator

A PHP library for generating MBTiles files.

Supports fetching tiles from a web resource, implementing the [Slippy map tilenames specification](http://wiki.openstreetmap.org/wiki/Slippy_map_tilenames).

Also supports fetching tiles from another MBTiles file, for doing a subset of tiles.

Using a custom source, is as easy as implementing the `TileSourceInterface`.

## What is a MBTiles file ?

An MBTiles file is a file format for storing map tiles, for easy transfer and storing.
It is optimal for doing offline maps in mobile apps.

This library's output has been tested for usage with [Nutiteq 3D Android mapping SDK](https://github.com/nutiteq/hellomap3d) and [Mapbox iOS SDK](https://github.com/mapbox/mapbox-ios-sdk).

[MBTiles specification](https://github.com/mapbox/mbtiles-spec)

## Installation

    composer require html24/mbtiles-generator dev-master

## Usage

This example code will download the tiles necessary for a small area in Copenhagen, Denmark and output a
mbtiles file as output.mbtiles.

    <?php
    use HTML24\MBTilesGenerator\MBTilesGenerator;
    use HTML24\MBTilesGenerator\TileSources\RemoteCachingTileSource;
    use HTML24\MBTilesGenerator\Model\BoundingBox;

    $tile_source = new RemoteCachingTileSource('http://otile{s}.mqcdn.com/tiles/1.0.0/map/{z}/{x}/{y}.jpg', array(1,2,3,4));
    $tile_source->setAttribution('Data, imagery and map information provided by MapQuest, OpenStreetMap <http://www.openstreetmap.org/copyright> and contributors, ODbL <http://wiki.openstreetmap.org/wiki/Legal_FAQ#I_would_like_to_use_OpenStreetMap_maps._How_should_I_credit_you.#>.');


    $tile_generator = new MBTilesGenerator($tile_source);

    $bounding_box = new BoundingBox('12.6061,55.6615,12.6264,55.6705');

    $tile_generator->setMaxZoom(18);
    $tile_generator->generate($bounding_box, 'output.mbtiles');

