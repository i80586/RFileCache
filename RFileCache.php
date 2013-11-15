<?php

namespace RLibrary;

/**
 * Class RFileCache
 * Simple class for file caching
 *
 * @author Rasim Ashurov <rasim.ashurov@gmail.com>
 * @date 6 November 2013
 */
class RFileCache
{

    /**
     * Error constants
     */
    const ERR_DIR_NOT_EXISTS = 1;
    const ERR_WRANG_DURATION = 2;

    /**
     * Default cache duration (one year)
     */
    const UNLIMITED_DURATION = 31104000;

    /**
     * Cache folder
     * @property string 
     */
    private $cacheDir;

    /**
     * Default expire time for all cache files
     * As seconds
     * @property integer 
     */
    private $expire = null;

    /**
     * Temporary cache identifier
     * @property string 
     */
    private $currentIdentifier;

    /**
     * Temporary cache duration 
     * @property integer 
     */
    private $currentDuration;

    /**
     * Class constructor
     * @param string $cacheFolder
     */
    public function __construct($cacheDir, $expire = null)
    {
        $this->setCacheDir($cacheDir);
        $this->expire = $expire;
    }

    /**
     * Returns cache dir
     * @return string
     */
    public function getCacheDir()
    {
        return $this->cacheDir;
    }

    /**
     * Sets cache dir
     * @param string $dir
     * @throws Exception
     */
    public function setCacheDir($dir)
    {
        $this->cacheDir = ($dir[strlen($dir) - 1] != DIRECTORY_SEPARATOR) ? ($dir . DIRECTORY_SEPARATOR) : $dir;
        if (!is_dir($this->cacheDir)) {
            throw new \Exception('Cache dir is not exists', self::ERR_DIR_NOT_EXISTS);
        }
    }

    /**
     * Generates cache hash
     * @param string $identifier
     * @return string
     */
    private function generateCacheHash($identifier)
    {
        return sha1($identifier);
    }

    /**
     * Save data in cache
     * @param string $identifier
     * @param mixed $data
     * @param integer $duration
     * @throws \Exception
     */
    public function set($identifier, $data, $duration = 0)
    {
        if (empty($identifier)) {
            throw new \Exception('Cache identifier is not set', self::ERR_EMPTY_IDENTIFIER);
        }

        $cacheDuration = (null !== $this->expire) ? $this->expire : $duration;

        if (!is_int($cacheDuration)) {
            throw new \Exception('Cache duration must be integer', self::ERR_WRANG_DURATION);
        }

        if (0 === $cacheDuration) {
            $cacheDuration = self::UNLIMITED_DURATION;
        }

        $cacheHash = $this->generateCacheHash($identifier);
        $this->writeData($this->cacheDir . $cacheHash, $data, $cacheDuration);
    }

    /**
     * Load data
     * @param string $identifier
     * @return mixed
     */
    public function get($identifier)
    {
        if (empty($identifier)) {
            throw new \Exception('Cache identifier is not set', self::ERR_EMPTY_IDENTIFIER);
        }

        $cacheHash = $this->generateCacheHash($identifier, $this->expire);
        $cacheData = $this->readData($this->cacheDir . $cacheHash);

        return $cacheData;
    }

    /**
     * Remove cache by identifier
     * Return true if file deleted and false if file not exists
     * @param string $identifier
     * @return boolean
     */
    public function drop($identifier)
    {
        if (empty($identifier)) {
            throw new \Exception('Cache identifier is not set', self::ERR_EMPTY_IDENTIFIER);
        }

        $cacheIdentifier = $this->generateCacheHash($identifier);

        if (is_file($this->cacheDir . $cacheIdentifier)) {
            unlink($this->cacheDir . $cacheIdentifier);
            return true;
        } else {
            return false;
        }
    }

    /**
     * Write data to file
     * @param string $filename
     * @param string $data
     * @param integer $duration
     */
    private function writeData($filename, $data, $duration)
    {
        file_put_contents($filename, (time() + $duration) . serialize($data), LOCK_EX);
    }

    /**
     * Read data from file
     * @param string $filename
     * @return mixed
     */
    private function readData($filename)
    {
        $output = false;

        if (is_file($filename)) {
            $fileContent = file_get_contents($filename);
            $expireTime = substr($fileContent, 0, 11);

            if ($expireTime > time()) {
                $output = unserialize(substr($fileContent, 10));
            } else
                unlink($filename);
        }

        return $output;
    }

    /**
     * Start reading from buffer
     * @param string $identifier
     * @param integer $duration
     * @throws \Exception
     */
    public function start($identifier, $duration = 0)
    {
        if (empty($identifier)) {
            throw new \Exception('Cache identifier is not set', self::ERR_EMPTY_IDENTIFIER);
        }

        $this->currentIdentifier = $this->generateCacheHash($identifier);
        $this->currentDuration = $duration;

        if (!is_int($this->currentDuration)) {
            throw new \Exception('Cache duration must be integer', self::ERR_WRANG_DURATION);
        }

        $cacheData = $this->readData($this->cacheDir . $this->currentIdentifier);

        if (false !== $cacheData) {
            echo $cacheData;
            return false;
        } else {
            return ob_start();
        }
    }

    /**
     * End reading from buffer
     * Save to cache
     */
    public function end()
    {
        $this->writeData($this->cacheDir . $this->currentIdentifier, ob_get_contents(), $this->currentDuration);
        ob_flush();
        ob_end_clean();
    }

}
