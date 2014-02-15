<?php
/**
 * Created by HTML24 ApS.
 */

namespace HTML24\MBTilesGenerator\TileSources;


use HTML24\MBTilesGenerator\Exception\TileNotAvailableException;
use HTML24\MBTilesGenerator\Model\Tile;

class MBTilesTileSource implements TileSourceInterface
{

    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var \PDOStatement
     */
    protected $tile_fetcher;

    /**
     * @param $source_file
     * @throws \Exception
     */
    public function __construct($source_file)
    {
        if (!file_exists($source_file)) {
            throw new \Exception('Source not found');
        }

        $this->db = new \PDO('sqlite:' . $source_file);

        $this->validateTable();

        $this->tile_fetcher = $this->db->prepare(
            'SELECT tile_data FROM tiles WHERE tile_column = :x AND tile_row = :y LIMIT 1'
        );
    }

    /**
     * @throws \Exception
     */
    protected function validateTable()
    {
        // Check if this looks like a mbtiles database

        $tables = $this->db->query(
            'SELECT tbl_name FROM sqlite_master WHERE type = \'table\' OR type = \'view\''
        )->fetchAll(\PDO::FETCH_COLUMN, 0);

        if (!in_array('metadata', $tables) || !in_array('tiles', $tables)) {
            throw new \Exception('This does not look like a valid mbtiles file');
        }

        // Check for the necessary columns
        $metadata = $this->db->query('PRAGMA table_info(metadata)')->fetchAll(\PDO::FETCH_COLUMN, 1);
        $tiles = $this->db->query('PRAGMA table_info(tiles)')->fetchAll(\PDO::FETCH_COLUMN, 1);

        if (
            !in_array('name', $metadata) ||
            !in_array('value', $metadata)
        ) {
            throw new \Exception('Metadata table/view is of incorrect format');
        }

        if (
            !in_array('zoom_level', $tiles) ||
            !in_array('tile_column', $tiles) ||
            !in_array('tile_row', $tiles) ||
            !in_array('tile_data', $tiles)
        ) {
            throw new \Exception('Tiles table/view is of incorrect format');
        }
    }

    /**
     * This method will be called before actually requesting single tiles.
     *
     * Use this to batch generate/download tiles.
     * @param Tile[] $tiles
     * @return void
     */
    public function cache($tiles)
    {
        // This source does not need to cache anything
    }

    /**
     * For every tile needed, this function will be called
     *
     * Return the blob of this image
     * @param Tile $tile
     * @throws TileNotAvailableException
     * @return string Blob of this image
     */
    public function getTile(Tile $tile)
    {
        $this->tile_fetcher->bindValue(':x', $tile->x);
        $this->tile_fetcher->bindValue(':y', $tile->y);

        $this->tile_fetcher->execute();

        $tile_data = $this->tile_fetcher->fetchColumn();
        if ($tile_data === false) {
            throw new TileNotAvailableException();
        }

        return $tile_data;
    }

    /**
     * Return the attribution text as HTML/text
     *
     * @return string
     */
    public function getAttribution()
    {
        $attribution = $this->db->query('SELECT value FROM metadata WHERE name = \'attribution\'');
        if ($attribution->columnCount() > 0) {
            return $attribution->fetchColumn();
        } else {
            return '';
        }
    }

    /**
     * Should return the format of the tiles, either 'jpg' or 'png'
     *
     * @return string
     */
    public function getFormat()
    {
        $format = $this->db->query('SELECT value FROM metadata WHERE name = \'format\'');
        if ($format->columnCount() > 0) {
            return $format->fetchColumn();
        } else {
            return 'jpg';
        }
    }

}
