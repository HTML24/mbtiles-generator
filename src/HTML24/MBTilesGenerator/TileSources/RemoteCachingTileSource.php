<?php
/**
 * Created by HTML24 ApS.
 */

namespace HTML24\MBTilesGenerator\TileSources;


use HTML24\MBTilesGenerator\Exception\TileNotAvailableException;
use HTML24\MBTilesGenerator\Model\Tile;
use HTML24\MBTilesGenerator\Util\Calculator;

class RemoteCachingTileSource implements TileSourceInterface
{
    /**
     * @var array
     */
    protected $domains;

    /**
     * @var string
     */
    protected $cacheDir;

    /**
     * @var resource
     */
    protected $curl_multi;

    /**
     * @var array
     */
    protected $queue = array();

    /**
     * @var array
     */
    protected $active_requests = array();

    /**
     * @var string
     */
    protected $attribution;

    /**
     * @var int
     */
    protected $maxRequests = 5;

    /**
     * The format of the tiles, either jpg or png
     * @var string
     */
    protected $format;

    /**
     * @param string $url
     * @param string[] $subDomains
     */
    public function __construct($url, $subDomains = null)
    {
        $this->cacheDir = sys_get_temp_dir() . '/mbtiles-generator/' . preg_replace(
                array('/\s/', '/\.[\.]+/', '/[^\w_\.\-]/'),
                array('_', '.', '_'),
                $url
            );

        if ($subDomains) {
            $this->domains = array();
            foreach ($subDomains as $subDomain) {
                $this->domains[] = str_replace('{s}', $subDomain, $url);
            }
        } else {
            $this->domains = array($url);
        }

        // Attempt to figure out the format, automatically. Fallback to jpg.
        if (strtolower(substr($url, -3)) == 'png') {
            $this->format = 'png';
        } else {
            $this->format = 'jpg';
        }
    }

    /**
     * Set the attribution for this tile source
     * @param string $attribution
     */
    public function setAttribution($attribution)
    {
        $this->attribution = (string)$attribution;
    }

    /**
     * @return string
     */
    public function getAttribution()
    {
        return (string)$this->attribution;
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
        // Start CURL Multi handle
        $this->curl_multi = curl_multi_init();

        // Queue up downloads
        foreach ($tiles as $tile) {
            $this->queueTile($tile);
        }

        // Execute the download
        $this->downloadTiles();
    }

    /**
     * Download all tiles in the queue
     */
    protected function downloadTiles()
    {

        while (count($this->queue) > 0) {
            $this->waitForRequestsToDropBelow($this->maxRequests);

            $item = array_shift($this->queue);

            $ch = $this->newCurlHandle($item['url']);
            curl_multi_add_handle($this->curl_multi, $ch);

            $key = (int)$ch;
            $this->active_requests[$key] = $item;

            $this->checkForCompletedRequests();
        }
        // Wait for transfers to finnish
        $this->waitForRequestsToDropBelow(1);
    }

    /**
     * @param int $max
     */
    protected function waitForRequestsToDropBelow($max)
    {
        while (1) {
            $this->checkForCompletedRequests();
            if (count($this->active_requests) < $max) {
                break;
            }
            usleep(10000);
        }
    }

    /**
     * Check and process any completed requests
     */
    protected function checkForCompletedRequests()
    {
        do {
            $mrc = curl_multi_exec($this->curl_multi, $active);
        } while ($mrc == CURLM_CALL_MULTI_PERFORM);

        while ($active && $mrc == CURLM_OK) {
            if (curl_multi_select($this->curl_multi) != -1) {
                do {
                    $mrc = curl_multi_exec($this->curl_multi, $active);
                } while ($mrc == CURLM_CALL_MULTI_PERFORM);
            } else {
                return;
            }
        }

        // Grab information about completed requests
        while ($info = curl_multi_info_read($this->curl_multi)) {

            $ch = $info['handle'];

            $ch_array_key = (int)$ch;

            if (!isset($this->active_requests[$ch_array_key])) {
                die("Error - handle wasn't found in requests: '$ch' in " .
                    print_r($this->active_requests, true));
            }

            $request = $this->active_requests[$ch_array_key];
            $status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            if ($info['result'] !== CURLE_OK) {
                //echo 'Error on tile: ' . $request['url'] . ' - ' . curl_error($ch) . "\n";
            } else if ($status != 200) {
                //echo 'Error on tile: ' . $request['url'] . ' - HTTP Status: ' . $status . "\n";
            } else {
                // Create destination
                static::createPath($request['destination'], true);

                // Write content to destination
                file_put_contents($request['destination'], curl_multi_getcontent($ch));
            }


            unset($this->active_requests[$ch_array_key]);

            curl_multi_remove_handle($this->curl_multi, $ch);
        }
    }

    /**
     * Adds a tile to the download queue
     * @param Tile $tile
     */
    protected function queueTile(Tile $tile)
    {
        $tile_destination = $this->tileDestination($tile);

        // Check if we already have the tile in cache
        if (file_exists($tile_destination)) {
            //echo 'Cache hit for tile: ' . $tile->z . '/' . $tile->x . '/' . $tile->y . "\n";

            return;
        }

        // The tile was not in the cache, add a queue item
        $this->queue[] = array(
            'url' => $this->tileUrl($tile),
            'destination' => $this->tileDestination($tile),
        );

        //echo 'Cache miss for tile: ' . $tile->z . '/' . $tile->x . '/' . $tile->y . "\n";
    }

    /**
     * @param Tile $tile
     * @return string
     */
    protected function tileDestination(Tile $tile)
    {
        return $this->cacheDir . '/' . $tile->z . '/' . $tile->x . '/' . $tile->y . '.' . $this->getFormat();
    }

    /**
     * @param Tile $tile
     * @return string
     */
    protected function tileUrl(Tile $tile)
    {
        $url = $this->domains[array_rand($this->domains)];
        $url = str_replace('{z}', $tile->z, $url);
        $url = str_replace('{x}', $tile->x, $url);
        $url = str_replace('{y}', Calculator::flipYTmsToOsm($tile->y, $tile->z), $url);

        return $url;
    }

    /**
     * Returns a new curl handle
     * @param string $url
     * @return resource
     */
    protected function newCurlHandle($url)
    {
        $ch = curl_init();

        curl_setopt_array(
            $ch,
            array(
                CURLOPT_URL => $url,
                CURLOPT_RETURNTRANSFER => 1,
                CURLOPT_TIMEOUT => 10,
            )
        );

        return $ch;
    }

    /**
     * Function to recursively create a file path
     * @param string $path
     * @param bool $is_filename
     * @return bool
     */
    protected static function createPath($path, $is_filename = false)
    {
        if ($is_filename) {
            $path = substr($path, 0, strrpos($path, '/'));
        }

        if (is_dir($path) || empty($path)) {
            return true;
        }

        if (static::createPath(substr($path, 0, strrpos($path, '/')))) {
            if (!file_exists($path)) {
                return mkdir($path);
            }
        }

        return false;
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
        $tile_path = $this->tileDestination($tile);
        if (!file_exists($tile_path)) {
            throw new TileNotAvailableException();
        }

        return file_get_contents($tile_path);
    }

    /**
     * Should return the format of the tiles, either 'jpg' or 'png'
     *
     * @return string
     */
    public function getFormat()
    {
        return $this->format;
    }

    /**
     * Manually override the format for this source.
     * Either 'jpg' or 'png'
     *
     * @param string $format
     * @throws
     */
    public function setFormat($format) {
        if ($format === 'jpg' || $format === 'png') {
            $this->format = $format;
        } else {
            throw new \Exception('Unknown format ' . $format . ' supplied');
        }
    }


}