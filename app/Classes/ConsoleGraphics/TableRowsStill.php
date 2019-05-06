<?php

/*
 * This file is part of the Symfony package.
 *
 * (c) Fabien Potencier <fabien@symfony.com>
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace App\Classes\ConsoleGraphics;

/**
 * @internal
 */
class TableRowsStill implements \IteratorAggregate
{
    private $generator;

    public function __construct(callable $generator)
    {
        $this->generator = $generator;
    }

    public function getIterator()
    {
        $g = $this->generator;

        return $g();
    }
}
