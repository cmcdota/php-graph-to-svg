# PhpGraphToSvg - Force Directed Graph

[![Total Downloads](https://img.shields.io/packagist/dt/cmcdota/php-graph-to-svg.svg)](https://packagist.org/packages/cmcdota/php-graph-to-svg)
[![Latest Stable Version](https://img.shields.io/packagist/v/cmcdota/php-graph-to-svg.svg)](https://packagist.org/packages/cmcdota/php-graph-to-svg)


Base realisation of Force-Directed  Fruchterman–Reingold algorithm https://reingold.co/force-directed.pdf

## Installation

Install the latest version with

```bash
$ composer require cmcdota/php-graph-to-svg
```

## Basic Usage

```php
$array = [
    0 => ['name' => 'stage 0', 'edges' => [1, 3]],
    1 => ['name' => 'stage 1', 'edges' => []],
    2 => ['name' => 'stage 2', 'edges' => []],
    3 => ['name' => 'stage 3', 'edges' => []],
    4 => ['name' => 'stage 4', 'edges' => [1]],
];

$board = new Board($array, true);
echo "<html lang='EN'>";

for ($i = 1; $i <= 100; $i++) {
    $svg = $board->draw();
    file_put_contents("step{$i}.svg", $svg);
    echo "<img src='step{$i}.svg'  alt='next step'>";
    $board->calculateAndMove();
}
```
