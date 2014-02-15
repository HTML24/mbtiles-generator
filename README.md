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

## Documentation

Read the documentation [here](https://bitbucket.org/HTML24DK/mbtiles-generator/src/master/docs/index.rst) or look at the examples in [tests](https://bitbucket.org/HTML24DK/mbtiles-generator/tests/)