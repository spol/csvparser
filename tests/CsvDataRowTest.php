<?php

namespace Spol\Csv\Tests;

use Spol\Csv\CsvDataRow;
use Spol\Csv\CsvHeaderRow;
use Spol\Path\Path;
use ReflectionClass;

class CsvDataRowTest extends \PHPUnit_Framework_TestCase
{
    protected static function getMethod($name) {
      $class = new ReflectionClass('Spol\Csv\CsvDataRow');
      $method = $class->getMethod($name);
      $method->setAccessible(true);
      return $method;
    }

    public function testParseQuoted()
    {
        $row = '"One","Two","Three"';
        $parsedRow = new CsvDataRow($row);

        $this->assertEquals("One", $parsedRow[0]);
    }

    public function testShiftQuoted()
    {
        $foo = self::getMethod('shiftQuoted');
        $obj = new CsvDataRow("");

        $row = '"One","Two","Three"';
        list($field, $remainder) = $foo->invokeArgs($obj, array($row));

        $this->assertEquals("One", $field);
        $this->assertEquals("\"Two\",\"Three\"", $remainder);

        $row = "\"Two\",\"Three\"";

        list($field, $remainder) = $foo->invokeArgs($obj, array($row));
        $this->assertEquals("Two", $field);
        $this->assertEquals("\"Three\"", $remainder);

        $row = "\"Three\"";
        list($field, $remainder) = $foo->invokeArgs($obj, array($row));
        $this->assertEquals("Three", $field);
        $this->assertFalse($remainder);
    }

    /**
     * @expectedException Spol\Csv\CsvFormatException
     */
    public function testShiftQuotedError()
    {
        $foo = self::getMethod('shiftQuoted');
        $obj = new CsvDataRow("");

        $row = '"One"a,"Two","Three"';
        list($field, $remainder) = $foo->invokeArgs($obj, array($row));
    }

    public function testShiftQuotedMultiline()
    {
        $foo = self::getMethod('shiftQuoted');
        $obj = new CsvDataRow("");

        $row = '"One';
        list($field, $remainder) = $foo->invokeArgs($obj, array($row));
    }

    public function testFindQuote()
    {
        $foo = self::getMethod('findClosingQuote');
        $obj = new CsvDataRow("");

        $row = '"One"';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(4, $split);

        $row = '"O"';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(2, $split);

        $row = '""';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(1, $split);

        $row = '"One';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(null, $split);

        $row = '"One","Two"';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(4, $split);

        $row = '"One",Two';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(4, $split);

        $row = '"One""Two"';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(9, $split);

        $obj = new CsvDataRow("", array('escape' => '\\'));

        $row = '"One"';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(4, $split);

        $row = '"One\"Two"';
        $split = $foo->invokeArgs($obj, array($row));
        $this->assertEquals(9, $split);
    }

    public function testCount()
    {
        $row = new CsvDataRow('"One","Two","Three"');

        $this->assertEquals(3, count($row));
    }

    public function testArrayAccess()
    {
        $headers = new CsvHeaderRow('"One","Two","Three"');
        $row = new CsvDataRow('"Alpha","Beta","Gamma"', array('headers' => $headers));

        $this->assertEquals("Alpha", $row[0]);

        $this->assertEquals("Beta", $row['Two']);
    }
}
