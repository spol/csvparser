<?php

namespace Spol\Csv;

use Spol\Filesystem\File;
use Iterator;

class CsvReader implements Iterator
{

    protected $filename;
    protected $currentRow;

    protected $options = array(
        'headers'   => true
    );

    protected $headers;

    public function __construct($filename, array $options=array())
    {
        $this->filename = $filename;
        $this->file = new File($this->filename);

        $this->options = array_merge($this->options, $options);

        // $this->rewind();
    }

    public function current()
    {
        return $this->currentRow;
    }

    public function rewind()
    {
        $this->file->seek(0);
        if ($this->options['headers']) {
            $headers = $this->file->getLine();
            if ($headers) {
                $this->headers = new CsvHeaderRow($headers, $this->options);
                while ($this->headers->isIncomplete()) {
                    $nextLine = $this->file->getLine();
                    if ($nextLine) {
                        $this->headers->continueRow($nextLine);
                    }
                    else {
                        throw new CsvFormatException("Unexpected file end.");
                    }
                }
            }
        }
    }

    public function key()
    {
        return $this->file->position();
    }

    public function next()
    {
        $line = $this->file->getLine();
        if ($line) {
            $this->currentRow = new CsvDataRow($line, array('headers' => $this->headers));

            while ($this->currentRow->isIncomplete()) {
                die('more data needed');
                $this->currentRow->continueRow($this->file->getLine());
            }
        }
        else {
            $this->currentRow = null;
        }
    }

    public function valid()
    {
        return !!$this->currentRow;
    }

    public function getSheet()
    {

    }

    public function getRegion($x, $y, $width, $height)
    {

    }
}