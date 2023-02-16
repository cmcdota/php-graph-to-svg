<?php

namespace Cmcdota\PhpGraphToSvg;


use Exception;

/**
 * Board for future SVG
 */
class Board
{
    public array $Vertexes;
    public float $temperature;

    public int $defaultVertexWidth;
    public int $defaultVertexHeight;
    public int $boardWidth;
    public int $boardHeight;
    public int $perfectDistance;
    public bool $randomSpawn = true;

    private float $defaultSpace;


    private bool $debug = false;
    private bool $randomFill = false;
    private bool $drawArrows = false;
    private float $arrowScale = 10;


    public function __construct(array $vertexArray, array $params = null)
    {

        //Size of boxes
        $this->defaultVertexWidth = 120;
        $this->defaultVertexHeight = 50;

        //Board Initialization
        $this->boardWidth = $this->defaultVertexWidth * count($vertexArray) * 2;
        $this->boardHeight = $this->defaultVertexHeight * count($vertexArray) * 2;
        $boardAreaSize = $this->boardWidth * $this->boardHeight;


        //Calculating perfect distance
        //4) Расчет идеального размера расстояния.
        $this->perfectDistance = sqrt($boardAreaSize / count($vertexArray)) / 4;
        $this->defaultSpace = $this->perfectDistance * 2;


        //Default temperature that limits movement speed
        $this->temperature = $this->perfectDistance;

        if (is_array($params)) {
            //You may reassign any parameter
            $this->temperature = $params['temperature'] ?? $this->temperature;
            $this->defaultVertexWidth = $params['defaultVertexWidth'] ?? $this->defaultVertexWidth;
            $this->defaultVertexHeight = $params['defaultVertexHeight'] ?? $this->defaultVertexHeight;
            $this->boardWidth = $params['boardWidth'] ?? $this->boardWidth;
            $this->boardHeight = $params['boardHeight'] ?? $this->boardHeight;
            $this->perfectDistance = $params['perfectDistance'] ?? $this->perfectDistance;
            $this->defaultSpace = $params['defaultSpace'] ?? $this->defaultSpace;
            $this->debug = $params['debug'] ?? $this->debug;

            $this->randomSpawn = $params['randomSpawn'] ?? $this->randomSpawn;
            $this->randomFill = $params['randomFill'] ?? $this->randomFill;
            $this->drawArrows = $params['drawArrows'] ?? $this->drawArrows;
            $this->arrowScale = $params['arrowScale'] ?? $this->arrowScale;
        }

        //Init Vertexes
        //2) Создание вершин
        $this->Vertexes = $this->initVertexes($vertexArray);

        //Initialise edges between vertex
        //3) Создать между всеми элементами связи - притяжение либо отталкивание.
        $this->initEdges($vertexArray);

        //First Calculate forces
        //5) Функция расчета в каждой вершине каждой связи.
        $this->calculate();
    }

    /**
     * @param $array
     *
     * @return Vertex[] array
     * @throws Exception
     */
    public function initVertexes(array $array): array
    {
        //2) Приём массива, разместить первый элемент по центру, остальные по кругу.
        $startPointX = $this->boardWidth / 2;
        $startPointY = $this->boardHeight / 2;

        $Vertexes = [];
        $is_first = true;
        $turn = 0;
        $angle = 0;
        $turnRate = 360 / (count($array) - 1);
        foreach ($array as $key => $data) {
            if ($this->randomSpawn) {
                //Spawn randomly
                $x = random_int($this->defaultVertexWidth, $this->boardWidth - $this->defaultVertexWidth);
                $y = random_int($this->defaultVertexHeight, $this->boardHeight - $this->defaultVertexHeight);
            } else {
                //Spawn by circle:
                if ($is_first) {
                    $x = $startPointX;
                    $y = $startPointY;
                    $is_first = false;
                } else {
                    $angle = $turnRate * $turn;
                    $turn++;
                    $cos = cos($angle * pi() / 180);
                    $sin = sin($angle * pi() / 180);
                    $x = $startPointX + $this->defaultSpace * $cos;
                    $y = $startPointY + $this->defaultSpace * $sin;
                }
            }
            if ($this->randomFill) {
                $data['fill'] = null;
            } elseif (!isset($data['fill'])) {
                $data['fill'] = 16777215;
            }
            $Vertexes[$key] = new Vertex($x, $y, $data['name'], $this->defaultVertexWidth, $this->defaultVertexHeight, $this->perfectDistance, $data['fill'], $data);
        }
        return $Vertexes;
    }

    function initEdges(array $array)
    {
        foreach ($this->Vertexes as $key => $Vertex) {
            foreach ($this->Vertexes as $key2 => $Vertex2) {
                if ($key != $key2) {
                    if (in_array($key2, $array[$key]['edges'])) {
                        $Vertex->createEdge($Vertex2, true, true);
                    } elseif (in_array($key, $array[$key2]['edges'])) {
                        $Vertex->createEdge($Vertex2, true, false);
                    } else {
                        $Vertex->createEdge($Vertex2, false);
                    }
                }
            }
        }
    }


    private function calculate(): Board
    {
        foreach ($this->Vertexes as $Vertex) {
            $Vertex->calculateMoving();
        }
        return $this;
    }

    private function move(): Board
    {
        foreach ($this->Vertexes as $key => $Vertex) {
            $Vertex->move($this->boardWidth, $this->boardHeight, $this->temperature);
        }
        $this->temperature = $this->temperature * 0.95;
        $this->calculate();
        return $this;
    }

    public function calculateAndMove(int $moves = 1): Board
    {
        for ($i = 1; $i <= $moves; $i++) {
            $this->calculate()->move();
        }
        return $this;
    }

    public function getMinimalMaximumXY(float $paddingX = 0,float  $paddingY = 0): array
    {
        $xMin = null;
        $yMin = null;
        $xMax = null;
        $yMax = null;
        foreach ($this->Vertexes as $Vertex) {
            list($x, $y) = $Vertex->getXY();
            if ($x > $xMax || $xMax === null) {
                $xMax = $x;
            }
            if ($y > $yMax || $yMax === null) {
                $yMax = $y;
            }
            if ($x < $xMin || $xMin === null) {
                $xMin = $x;
            }
            if ($y < $yMin || $yMin === null) {
                $yMin = $y;
            }
        }
        return [$xMin, $yMin, $xMax + $paddingX, $yMax + $paddingY, $xMax - $xMin + $paddingX, $yMax - $yMin + $paddingY + $paddingY + $paddingY + $paddingY];
    }

    /**
     * Generates SVG for given state
     * Feel free to create your renderer, just take vertexes from getVertexes();
     *
     * @return string
     */
    public function renderSVG(): string
    {
        //Get size and paddings:
        list($xMin, $yMin, $xMax, $yMax, $xWidth, $yHeight) = $this->getMinimalMaximumXY($this->defaultVertexWidth, $this->defaultVertexHeight);
        $svg = "<svg xmlns='http://www.w3.org/2000/svg'
             width='" . ($xWidth) . "px'
             height='" . ($yHeight) . "px' viewBox='$xMin $yMin $xWidth $yHeight'>
            <g>\n";

        //Edges:
        foreach ($this->Vertexes as $Vertex) {
            foreach ($Vertex->getedges() as $edge) {
                list($x1, $y1, $x2, $y2) = $edge->getX1Y1X2Y2(true);

                if ($this->debug) {
                    $x1 -= 10;
                    $y1 -= 10;
                    $x2 += 10;
                    $y2 += 10;
                    $rgb = '150, 0, 0';
                    $svg .= "<path d='M $x1 $y1 L $x2 $y2' fill='none' stroke='rgb($rgb)' stroke-miterlimit='10' pointer-events='stroke'/>\n";
                    $svg .= "<text font-size='6' x='" . (($x1 + $x2) / 2 - 30) . "' y='" . (($y1 + $y2) / 2 - 10) . "'>{$edge->getRepulseAttrString()} d=" . intval($edge->getDistance()) . "R{$edge->getMoveRepulse()} A{$edge->getMoveAttraction()}</text>\n";
                } elseif ($edge->isMainEdge()) {
                    if ($this->drawArrows) {

                        list($x2, $y2, $Vx1, $Vy1) = $edge->findTargetCollisionPoint();
                        //dump([$x2, $y2, $Vx1, $Vy1]);

                        $a = $edge->getAnArrow($x2,$y2, $Vx1, $Vy1, $this->arrowScale);
                        $svg .= "<path d='M {$a['x1']} {$a['y1']} L {$a['x2']} {$a['y2']} L {$a['x3']} {$a['y3']} Z' fill='#000' stroke='rgb(0,0,0)' />\n";


                    }
                    $rgb = '0, 0, 00';
                    $svg .= "<path d='M $x1 $y1 L $x2 $y2' fill='none' stroke='rgb($rgb)'/>\n";
                }
            }
        }

        //Vertexes:
        foreach ($this->Vertexes as $Vertex) {
            $svg .= "<rect x='{$Vertex->getX()}' y='{$Vertex->getY()}' width='$this->defaultVertexWidth' height='$this->defaultVertexHeight' rx='9' ry='9' fill='#{$Vertex->getFill()}' stroke='rgb(0, 0, 0)' pointer-events='all'/>\n";
            $svg .= "<text x='" . ($Vertex->getX() + 5) . "' y='" . ($Vertex->getY() + 15) . "'>{$Vertex->getName()}</text>\n";

            if ($this->debug) {
                $svg .= "<text font-size='6' x='" . $Vertex->getX() . "' y='" . (($Vertex->getY()) - 7) . "'>x{$Vertex->getX()} y{$Vertex->getY()}</text>\n";
                $svg .= "<text font-size='6' x='" . $Vertex->getX() . "' y='" . (($Vertex->getY()) - 2) . "'>d{$Vertex->getDisplaceX()} : d{$Vertex->getDisplaceY()}</text>\n";

            }
        }
        $svg .= '</g></svg>';
        return $svg;
    }

    /**
     * @param bool $debug
     */
    public function setDebug(bool $debug): void
    {
        $this->debug = $debug;
    }

    /**
     * Returns Vertexes, so you may draw it by yourself
     *
     * @return Vertex[] array
     */
    public function getVertexes(): array
    {
        return $this->Vertexes;
    }
}
