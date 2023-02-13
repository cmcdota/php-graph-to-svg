<?php

require __DIR__ . "/vendor/autoload.php";

use Cmcdota\PhpGraphToSvg\Board;

$array = [
    0 => [
        'name' => 'stage 0'
        , 'edges' => [1,  3]
    ],
    1 => [
        'name' => 'stage 1'
        , 'edges' => []
    ],
    2 => [
        'name' => 'stage 2'
        , 'edges' => []
    ],
    3 => [
        'name' => 'stage 3'
        , 'edges' => []
    ],
    4 => [
        'name' => 'stage 4'
        , 'edges' => [1]
    ],

];


$board = new Board($array);
$svg = $board->draw();

file_put_contents("step1.svg", $svg);
echo "<html>
<img src='step1.svg' border='5'>
";

for ($i = 2; $i <= 100; $i++) {
    $board->calculateAndMove();
    $svg = $board->draw();
    file_put_contents("step{$i}.svg", $svg);
    echo "<img src='step{$i}.svg' border='5'>";
}