<?php

namespace MBTilesGenerator;

use MBTilesGenerator\Core\MBTileFile;
use MBTilesGenerator\Exception\TileNotAvailableException;
use MBTilesGenerator\TileSources\TileSourceInterface;
use MBTilesGenerator\Model\BoundingBox;
use MBTilesGenerator\Util\Calculator;
use MBTilesGenerator\Model\Tile;

class MBTilesGenerator
{
    /**
     * @var TileSources\TileSourceInterface
     */
    protected $tileSource;

    /**
     * The maximum number of tiles we want in the file
     * if the bounding box and zoom needs more than this
     * the zoom will be stepped down, until we are within this limit.
     * @var int
     */
    protected $tileLimit = 2000;

    /**
     * The zoom we are aiming for
     * @var int
     */
    protected $maxZoom = 18;

    /**
     * The last actual zoom accomplished
     * @var int
     */
    protected $effectiveZoom = -1;

    /**
     * How many percentage of the tiles are allowed to fail,
     * while still considering generation a success.
     *
     * @var int
     */
    protected $allowedFail = 5;

    /**
     * @param TileSourceInterface $tileSource
     */
    public function __construct(TileSourceInterface $tileSource)
    {
        $this->tileSource = $tileSource;
    }

    /**
     * Prepares the tiles by getting the tileSource to generate or download them.
     *
     * @param BoundingBox $boundingBox
     * @param string $destination File destination to write the mbtiles file to
     *
     * @throws
     * @return bool
     */
    public function generate(BoundingBox $boundingBox, $destination)
    {
        if (file_exists($destination)) {
            throw new \Exception('Destination file already exists');
        }
        $tiles = $this->generateTileList($boundingBox);

        $this->tileSource->cache($tiles);

        // Start constructing our file
        $mbtiles = new MBTileFile($destination);

        // Set the required meta data
        $this->addMetaDataToDB($mbtiles, $boundingBox);

        // Add tiles to the database
        $this->addTilesToDB($mbtiles, $tiles);

    }

    /**
     * Set maximum zoom on this instance, defaults to 18.
     * @param int $zoom
     */
    public function setMaxZoom($zoom) {
        $this->maxZoom = $zoom;
    }

    /**
     * Sets the allowed failures
     * @param $allowedFail
     */
    public function setAllowedFail($allowedFail) {
        $this->allowedFail = $allowedFail;
    }

    /**
     * Returns the effective zoom we accomplished on last run.
     * @return int
     */
    public function getEffectiveZoom() {
        return $this->effectiveZoom;
    }

    /**
     * @param MBTileFile $mbtiles
     * @param Tile[] $tiles
     * @throws
     */
    protected function addTilesToDB(MBTileFile $mbtiles, $tiles)
    {
        $failed_tiles = 0;

        foreach ($tiles as $tile) {
            try {
                $mbtiles->addTile($tile->z, $tile->x, $tile->y, $this->tileSource->getTile($tile));
            } catch (TileNotAvailableException $e) {
                $failed_tiles++;
                continue;
            }
        }

        $failed_percentage = ceil(($failed_tiles / count($tiles)) * 100);

        if ($failed_percentage > $this->allowedFail) {
            // Too many tiles failed
            throw new \Exception($failed_percentage . '% of the tiles failed, which is more than the allowed ' . $this->allowedFail . '%');
        }
    }


    protected function addMetaDataToDB(MBTileFile $mbtiles, BoundingBox $boundingBox)
    {
        $mbtiles->addMeta('name', 'bla');
        $mbtiles->addMeta('type', 'baselayer');
        $mbtiles->addMeta('version', '1');
        $mbtiles->addMeta('format', $this->tileSource->getFormat());
        $mbtiles->addMeta('attribution', $this->tileSource->getAttribution());

        $mbtiles->addMeta('bounds', (string)$boundingBox);
        $mbtiles->addMeta('minzoom', 0);
        $mbtiles->addMeta('maxzoom', $this->effectiveZoom);
    }

    /**
     * @param BoundingBox $boundingBox
     * @return Tile[]
     */
    protected function generateTileList(BoundingBox $boundingBox)
    {
        $tiles = array();
        for ($i = 0; $i <= $this->maxZoom; $i++) {
            $zoom_tiles = $this->generateTileListForZoom($boundingBox, $i);
            if (count($tiles) + count($zoom_tiles) < $this->tileLimit) {
                $tiles = array_merge($tiles, $zoom_tiles);
                $this->effectiveZoom = $i;
            } else {
                // We got to many tiles, so no more zoom levels.
                break;
            }
        }

        return $tiles;
    }

    /**
     * @param BoundingBox $boundingBox
     * @param int $zoom
     * @return Tile[]
     */
    protected function generateTileListForZoom(BoundingBox $boundingBox, $zoom)
    {
        $tiles = array();
        $start_tile = $this->coordinatesToTile(
            $boundingBox->getTopLeftLongitude(),
            $boundingBox->getTopLeftLatitude(),
            $zoom
        );

        $end_tile = $this->coordinatesToTile(
            $boundingBox->getBottomRightLongitude(),
            $boundingBox->getBottomRightLatitude(),
            $zoom
        );

        for ($x = $start_tile->x; $x <= $end_tile->x; $x++) {
            for ($y = $start_tile->y; $y <= $end_tile->y; $y++) {
                $tiles[] = new Tile($x, $y, $zoom);
            }
        }

        return $tiles;
    }

    /**
     * @param float $longitude
     * @param float $latitude
     * @param int $zoom
     * @return Tile
     */
    protected function coordinatesToTile($longitude, $latitude, $zoom)
    {
        $tile = new Tile();
        $tile->z = $zoom;
        $tile->x = Calculator::longitudeToX($longitude, $zoom);
        $tile->y = Calculator::latitudeToY($latitude, $zoom);

        return $tile;
    }
}