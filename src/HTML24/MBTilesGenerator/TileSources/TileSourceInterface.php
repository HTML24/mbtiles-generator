<?php
/**
 * Created by HTML24 ApS.
 */

namespace HTML24\MBTilesGenerator\TileSources;


use HTML24\MBTilesGenerator\Exception\TileNotAvailableException;
use HTML24\MBTilesGenerator\Model\Tile;

interface TileSourceInterface
{

    /**
     * This method will be called before actually requesting single tiles.
     *
     * Use this to batch generate/download tiles.
     * @param Tile[] $tiles
     * @return void
     */
    public function cache($tiles);

    /**
     * For every tile needed, this function will be called
     *
     * Return the blob of this image
     * @param Tile $tile
     * @throws TileNotAvailableException
     * @return string Blob of this image
     */
    public function getTile(Tile $tile);

    /**
     * Return the attribution text as HTML/text
     *
     * @return string
     */
    public function getAttribution();

    /**
     * Should return the format of the tiles, either 'jpg' or 'png'
     *
     * @return string
     */
    public function getFormat();
} 