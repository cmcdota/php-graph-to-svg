<?php

require __DIR__ . "/vendor/autoload.php";

use Cmcdota\PhpGraphToSvg\Board;

$vertexes = [
    0 => ['name' => '0', 'fill'=> '80ff80', 'edges' => [1]],
    1 => ['name' => '1','fill'=> 'c0c0c0', 'edges' => [2]],
    2 => ['name' => '2', 'edges' => [3]],
    3 => ['name' => '3', 'edges' => [1]],
    5 => ['name' => '5', 'edges' => [0]],
    6 => ['name' => '6', 'edges' => [5]],
    7 => ['name' => '7', 'edges' => [6,3]],
    8 => ['name' => '8', 'edges' => [6,7]],
    9 => ['name' => '9', 'edges' => [5]],
    10 => ['name' => '10', 'edges' => [9]],
    11 => ['name' => '11', 'edges' => [10,8]],
    12 => ['name' => '12', 'edges' => [9,11]],
];
$params=[
    'randomSpawn' => false
];
$board = new Board($vertexes, $params);
echo "<html lang='EN'>";
for ($i = 1; $i <= 10; $i++) {
    $svg = $board->renderSVG();
    file_put_contents("step$i.svg", $svg);
    echo "<div style='width:50%'><img src='step$i.svg'  alt='next step' border='1'></div>";
    $board->calculateAndMove(10);
}