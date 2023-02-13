<?php

require __DIR__ . "/vendor/autoload.php";

use Cmcdota\PhpGraphToSvg\Board;

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