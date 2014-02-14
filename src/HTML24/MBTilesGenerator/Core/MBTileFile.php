<?php
/**
 * Created by HTML24 ApS.
 * User: dennis
 * Date: 2014-02-13
 * Time: 15:30
 */

namespace HTML24\MBTilesGenerator\Core;


class MBTileFile
{
    /**
     * @var \PDO
     */
    protected $db;

    /**
     * @var \PDOStatement
     */
    protected $addMetaStmt;

    /**
     * @var \PDOStatement
     */
    protected $addTileStmt;

    /**
     * @param string $file
     */
    public function __construct($file)
    {
        $this->db = new \PDO('sqlite:' . $file);

        $this->db->exec('CREATE TABLE metadata (name TEXT, value TEXT);');
        $this->db->exec(
            'CREATE TABLE tiles (zoom_level INTEGER, tile_column INTEGER, tile_row INTEGER, tile_data BLOB);'
        );
        $this->db->exec('CREATE UNIQUE INDEX tile_index ON tiles (zoom_level, tile_column, tile_row);');

        $this->db->beginTransaction();

        $this->addMetaStmt = $this->db->prepare('INSERT INTO metadata (name, value) VALUES (:name, :value)');
        $this->addTileStmt = $this->db->prepare(
            'INSERT INTO tiles (zoom_level, tile_column, tile_row, tile_data) VALUES (:z, :x, :y, :data)'
        );
    }

    public function __destruct()
    {
        $this->db->commit();
    }

    /**
     * @param string $name
     * @param string $value
     */
    public function addMeta($name, $value)
    {
        $this->addMetaStmt->bindValue(':name', $name, \PDO::PARAM_STR);
        $this->addMetaStmt->bindValue(':value', $value, \PDO::PARAM_STR);
        $this->addMetaStmt->execute();
    }

    /**
     * @param int $zoom_level
     * @param int $x
     * @param int $y
     * @param string $data
     */
    public function addTile($zoom_level, $x, $y, $data)
    {
        $this->addTileStmt->bindValue(':z', $zoom_level, \PDO::PARAM_INT);
        $this->addTileStmt->bindValue(':x', $x, \PDO::PARAM_INT);
        $this->addTileStmt->bindValue(':y', $y, \PDO::PARAM_INT);
        $this->addTileStmt->bindValue(':data', $data, \PDO::PARAM_LOB);
        $this->addTileStmt->execute();
    }
}