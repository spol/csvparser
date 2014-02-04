<?php

namespace Spol\Csv\Tests;

use Spol\Csv\CsvReader;
use Spol\Path\Path;

class CsvReaderTest extends \PHPUnit_Framework_TestCase
{
    public function testRead()
    {
        $file = Path::resolve(__DIR__, "../test_docs/tworow.csv");
        $reader = new CsvReader($file);

        foreach ($reader as $key => $line) {
            echo "LINE:", PHP_EOL;

            foreach ($line as $column => $cell) {
                echo "\t[", $column, ' : ' . $cell, "]", PHP_EOL;
            }
        }
    }

    public function testNoQuotes()
    {
        $file = Path::resolve(__DIR__, "../test_docs/noquotes.csv");
        $reader = new CsvReader($file);

        foreach ($reader as $key => $line) {
            echo "LINE:", PHP_EOL;

            foreach ($line as $column => $cell) {
                echo "\t[", $column, ' : ' . $cell, "]", PHP_EOL;
            }
        }
    }

    public function testMultilineRead()
    {
        $file = Path::resolve(__DIR__, "../test_docs/multiline.csv");
        $reader = new CsvReader($file);

        foreach ($reader as $key => $line) {
            echo "LINE:", PHP_EOL;

            foreach ($line as $column => $cell) {
                echo "\t[", $column, ' : ' . $cell, "]", PHP_EOL;
            }
        }
    }
}