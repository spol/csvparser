<?php

namespace Spol\CSV;

interface Sheet
{
    public function getWidth();

    public function getHeight();

    public function getCell($x, $y);

    public function setCell($x, $y, $value);

    public function removeRow($y);

    public function insertRowBefore($y);

    public function removeColumn($x);

    public function insertColumnBefore($x);
}
