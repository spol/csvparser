<?php

namespace Spol\Csv;

use Iterator;
use ArrayAccess;
use Countable;

class CsvRow implements ArrayAccess, Countable, Iterator
{
    protected $options = array(
        'delimiter' => ',',
        'enclosure' => '"',
        'escape'    => '"'
    );

    protected $incomplete = false;
    protected $remainder;
    public $fields = array();
    public $headers = array();
    public $headerRow = null;

    public function __construct($row, $options=array()) {
        $this->options = array_merge($this->options, $options);

        $this->parseRow(trim($row));

        if (!empty($this->options['headers'])) {
            if (is_a($this->options['headers'], __CLASS__ )) {
                $this->headerRow = $this->options['headers'];
                for ($i = 0; $i < count($this->headerRow); $i++) {
                    $this->headers[$this->headerRow[$i]] = $i;
                }
            }
            else {
                throw new \Exception("Provided headers are not an instance of CsvRow");
            }
        }
    }

    public function isIncomplete()
    {
        return $this->incomplete;
    }

    protected function parseRow($row) {
        $length = strlen($row);
        $lastDelimiter = 0;
        $i = 0;
        while (strlen($row) > 0) {
            if ($row[0] === $this->options['enclosure']) {
                list($field, $remainder) = $this->shiftQuoted($row);
            }
            else
            {
                list($field, $remainder) = $this->shiftUnquoted($row);
            }

            if ($field !== null) {
                $this->fields[] = $field;
                $row = $remainder;
            }
            else {
                break;
            }
        }
    }

    protected function shiftQuoted($row)
    {
        $i = 0;
        $length = strlen($row);
        $split = $this->findClosingQuote($row);

        if ($split === null) {
            $this->remainder = $row;
            $this->incomplete = true;
            return array(null, '');
        }

        if ($split < $length-1) {
            if ($row[$split+1] !== $this->options['delimiter']) {
                throw new CsvFormatException(); // TODO: provide appropriate message.
            }
        }

        $field = trim(substr($row, 0, $split+1), $this->options['enclosure']);
        $remainder = substr($row, $split+2);
        return array($field, $remainder);
    }

    public function findClosingQuote($row)
    {
        $length = strlen($row);
        $split = null;

        if ($this->options['escape'] === $this->options['enclosure']) {
            // quotes are escaped by doubling.
            for ($i = 1; $i < $length; $i++) {
                if ($row[$i] === $this->options['enclosure']) {
                    if ($i == $length-1 || $row[$i+1] !== $this->options['enclosure']) {
                        $split = $i;
                        break;
                    }
                    else {
                        $i++;
                    }
                }
            }
        }
        else {
            for ($i = 1; $i < $length; $i++) {
                if ($row[$i] === $this->options['enclosure']) {
                    if ($i == 0 || $row[$i-1] !== $this->options['escape']) {
                        $split = $i;
                        break;
                    }
                }
            }
        }
        return $split;
    }

    public function shiftUnquoted($row)
    {
        if (($delimiterPosition = strpos($row, $this->options['delimiter'])) !== false) {
            $field = substr($row, 0, $delimiterPosition);
            $remainder = substr($row, $delimiterPosition + 1);
        }
        else {
            $field = $row;
            $remainder = "";
        }
        return array($field, $remainder);
    }

    public function continueRow($row) {
        $this->incomplete = false;
        $this->parseRow(trim($this->remainder . PHP_EOL . $row));
    }

    // ArrayAccess methods
    public function offsetExists($offset) {
        return is_int($offset) ? $this->numOffsetExists($offset) : $this->namedOffsetExists($offset);
    }

    protected function numOffsetExists($offset) {
        return array_key_exists($offset, $this->fields);
    }

    protected function namedOffsetExists($offset) {
        if ($this->headerRow === null) {
            return false;
        }
        else {
            return array_key_exists($offset, $this->headers);
        }
    }

    public function offsetGet($offset) {
        return is_int($offset) ? $this->numOffsetGet($offset) : $this->namedOffsetGet($offset);
    }

    protected function numOffsetGet($offset) {
        return $this->fields[$offset];
    }

    protected function namedOffsetGet($offset) {
        if ($this->namedOffsetExists($offset)) {
            return $this->fields[$this->headers[$offset]];
        }
        else {
            throw new \Exception("Column doesn't exist");
        }
    }

    public function offsetSet($offset, $value) {
        if (is_int($offset)) {
            $this->numOffsetset($offset, $value);
        }
        else {
            $this->namedOffsetset($offset, $value);
        }
    }

    public function offsetUnset($offset) {
        if (is_int($offset)) {
            $this->numOffsetUnset($offset);
        }
        else {
            $this->namedOffsetUnset($offset);
        }
    }

    // Countable methods
    public function count() {
        return count($this->fields);
    }

    // Iterator methods
    protected $cursor = 0;
    public function rewind() {
        $this->cursor = 0;
    }
    public function current() {
        return $this->fields[$this->cursor];
    }

    public function key() {
        return $this->cursor;
    }

    public function next() {
        ++$this->cursor;
    }

    public function valid() {
        return isset($this->fields[$this->cursor]);
    }
}