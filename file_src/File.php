<?php

namespace Spol\Filesystem;

use Spol\Filesystem\FilesystemException;
use Spol\Path\Path;

class File
{
    protected $path;
    protected $handle;
    protected $mode;

    public function __construct($path, $mode = 'rb')
    {
        $path = Path::resolve(getcwd(), $path);

        if (!self::exists($path)) {
            throw new FileNotFoundException($path);
        }

        $this->path = $path;
        $this->handle = @fopen($path, $mode);

        if ($this->handle === false) {
            throw new FilesystemException($path);
        }
        $this->mode = $mode;
    }

    public function getContents()
    {
        $this->seek(0);
        $data = $this->read($this->getLength());
        $this->seek(0);
    }

    public function getLine()
    {
        return fgets($this->handle);
    }

    /* Low-level functions. */
    public function seek($position)
    {
        fseek($this->handle, $position);
    }

    public function position()
    {
        return ftell($this->handle);
    }

    public function endOfFile()
    {
        return feof($this->handle);
    }

    public function read($length)
    {
        return fread($this->handle, $length);
    }

    public function truncate($size = 0)
    {
        ftruncate($this->handle, $size);
    }

    public function getLength()
    {
        clearstatcache(true, $this->path);
        return filesize($this->path);
    }

    public static function delete($path)
    {
        return file_exists($path) && unlink($path);
    }

    public static function exists($path)
    {
        return file_exists($path) && is_file($path);
    }
}