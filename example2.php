<?php

require __DIR__ . "/vendor/autoload.php";

use Cmcdota\PhpGraphToSvg\Board;

$vertexes=[
    258 => ['name' => '[258]', 'fill'=> 'B9F6CA', 'edges' => [260,285]],
    259 => ['name' => '[259]', 'fill'=> 'FF8A65', 'edges' => [258]],
    260 => ['name' => '[260]', 'fill'=> 'dddddd', 'edges' => [261,266,272,276,290,268,277]],
    261 => ['name' => '[261]', 'fill'=> 'dddddd', 'edges' => [262,263,266,259,258,268]],
    262 => ['name' => '[262]', 'fill'=> 'B9F6CA', 'edges' => [268]],
    263 => ['name' => '[263]', 'fill'=> 'B9F6CA', 'edges' => [268]],
    264 => ['name' => '[264]', 'fill'=> 'dddddd', 'edges' => [265]],
    265 => ['name' => '[265]', 'fill'=> 'B9F6CA', 'edges' => [266,258]],
    266 => ['name' => '[266]', 'fill'=> 'FF8A65', 'edges' => [290]],
    267 => ['name' => '[267]', 'fill'=> 'B9F6CA', 'edges' => [266,274,281,285]],
    268 => ['name' => '[268]', 'fill'=> 'FF8A65', 'edges' => [269,297,266]],
    269 => ['name' => '[269]', 'fill'=> 'FF8A65', 'edges' => [270,271]],
    270 => ['name' => '[270]', 'fill'=> 'dddddd', 'edges' => [282,284,273]],
    271 => ['name' => '[271]', 'fill'=> 'FF8A65', 'edges' => [273,284,282,299]],
    272 => ['name' => '[272]', 'fill'=> 'B9F6CA', 'edges' => [273]],
    273 => ['name' => '[273]', 'fill'=> 'B9F6CA', 'edges' => [281,274,282,277]],
    274 => ['name' => '[274]', 'fill'=> 'dddddd', 'edges' => [275,282,276]],
    275 => ['name' => '[275]', 'fill'=> 'FFCA28', 'edges' => [259,276]],
    276 => ['name' => '[276]', 'fill'=> 'dddddd', 'edges' => [273,282]],
    277 => ['name' => '[277]', 'fill'=> 'FF8A65', 'edges' => [278]],
    278 => ['name' => '[278]', 'fill'=> 'FFCA28', 'edges' => [282,291]],
    279 => ['name' => '[279]', 'fill'=> 'dddddd', 'edges' => [280]],
    280 => ['name' => '[280]', 'fill'=> 'FFCA28', 'edges' => [282]],
    281 => ['name' => '[281]', 'fill'=> 'dddddd', 'edges' => [273,284,283,277]],
    282 => ['name' => '[282]', 'fill'=> 'dddddd', 'edges' => [267,283,284,266,290]],
    283 => ['name' => '[283]', 'fill'=> 'B9F6CA', 'edges' => [285,282,274,281]],
    284 => ['name' => '[284]', 'fill'=> 'B9F6CA', 'edges' => [704,929]],
    285 => ['name' => '[285]', 'fill'=> 'dddddd', 'edges' => [286]],
    286 => ['name' => '[286]', 'fill'=> 'B9F6CA', 'edges' => [266,260,287,259]],
    287 => ['name' => '[287]', 'fill'=> 'B9F6CA', 'edges' => [288,289]],
    288 => ['name' => '[288]', 'fill'=> 'B9F6CA', 'edges' => [289,281]],
    289 => ['name' => '[289]', 'fill'=> 'dddddd', 'edges' => [286]],
    290 => ['name' => '[290]', 'fill'=> 'FF8A65', 'edges' => [298,281]],
    291 => ['name' => '[291]', 'fill'=> 'FF8A65', 'edges' => [282]],
    296 => ['name' => '[296]', 'fill'=> 'FFCA28', 'edges' => [268,258,287]],
    297 => ['name' => '[297]', 'fill'=> 'dddddd', 'edges' => [258]],
    298 => ['name' => '[298]', 'fill'=> 'dddddd', 'edges' => [286]],
    299 => ['name' => '[299]', 'fill'=> 'B9F6CA', 'edges' => [268,287]],
    300 => ['name' => '[300]', 'fill'=> 'B9F6CA', 'edges' => [296]],
    311 => ['name' => '[311]', 'fill'=> 'FFCA28', 'edges' => [296]],
    321 => ['name' => '[321]', 'fill'=> 'FFCA28', 'edges' => [268,296]],
    322 => ['name' => '[322]', 'fill'=> 'FFCA28', 'edges' => [268,296]],
    389 => ['name' => '[389]', 'fill'=> 'dddddd', 'edges' => [278]],
    655 => ['name' => '[655]', 'fill'=> 'B9F6CA', 'edges' => [695,260]],
    656 => ['name' => '[656]', 'fill'=> 'B9F6CA', 'edges' => [285]],
    695 => ['name' => '[695]', 'fill'=> 'dddddd', 'edges' => []],
    704 => ['name' => '[704]', 'fill'=> 'dddddd', 'edges' => []],
    929 => ['name' => '[929]', 'fill'=> 'dddddd', 'edges' => []],
    1392 => ['name' => '[1392]', 'fill'=> 'B9F6CA', 'edges' => [260,1395]],
    1395 => ['name' => '[1395]', 'fill'=> 'FF8A65', 'edges' => []],
];

$params=[
    'randomSpawn' => false
];
$board = new Board($vertexes, $params);
echo "<html lang='EN'>";
for ($i = 1; $i <= 10; $i++) {
    $svg = $board->renderSVG();
    file_put_contents("step$i.svg", $svg);
    echo "<div style='width:50%;height: 50%'>
        <img src='step$i.svg'  alt='next step' border='1' width='100%' height='100%'>
        </div>";
    $board->calculateAndMove(1);
}