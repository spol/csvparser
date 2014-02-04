<?php

namespace Spol\Csv;

class CsvHeaderRow extends CsvRow
{
    public function __construct($row, $options=array()) {
        unset($options['headers']);

        parent::__construct($row, $options);
    }
}