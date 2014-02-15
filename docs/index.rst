Welcome to MBTilesGenerator's documentation!
============================================

Installation
------------

.. code:: console

    composer require html24/mbtiles-generator dev-master

Usage
-----

This example code will download the tiles necessary for a small area in Copenhagen, Denmark and output a
mbtiles file as output.mbtiles.

.. code:: php
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

