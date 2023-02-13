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


    public function __construct(array $vertexArray, array $params = null)
    {
        //Default temperature that limits movement speed
        $this->temperature = 100;

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
    public function initVertexes($array): array
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

    function initEdges($array)
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

    public function calculateAndMove($moves = 1): Board
    {
        for ($i = 1; $i <= $moves; $i++) {
            $this->calculate()->move();
        }
        return $this;
    }

    /**
     * Generates SVG for given state
     * Feel free to create your renderer, just take vertexes from getVertexes();
     *
     * @return string
     */
    public function renderSVG(): string
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"
             width="' . ($this->boardWidth + 100) . 'px"
             height="' . ($this->boardHeight + 100) . 'px" viewBox="-5 -5 ' . $this->boardWidth . ' ' . $this->boardHeight . '">
            <g>
            ';

        foreach ($this->Vertexes as $Vertex) {
            foreach ($Vertex->getedges() as $edge) {
                list($x1, $y1, $x2, $y2) = $edge->getX1Y1X2Y2(true);
                $rgb = '150, 0, 0';
                if ($edge->isAttractive()) {
                    $rgb = '0, 0, 00';
                }
                if ($this->debug) {
                    $x1 -= 10;
                    $y1 -= 10;
                    $x2 += 10;
                    $y2 += 10;
                    $svg .= "<path d='M $x1 $y1 L $x2 $y2' fill='none' stroke='rgb($rgb)' stroke-miterlimit='10'
                      pointer-events='stroke'/>";
                    $svg .= "<text font-size='6' x='" . (($x1 + $x2) / 2 - 30) . "' y='" . (($y1 + $y2) / 2 - 10) . "'>{$edge->getRepulseAttrString()} d=" . intval($edge->calculateDistanceBetweenVertexes()) . "R{$edge->getMoveRepulse()} A{$edge->getMoveAttraction()}</text>";
                } elseif ($edge->isMainEdge()) {
                    $svg .= "<path d='M $x1 $y1 L $x2 $y2' fill='none' stroke='rgb($rgb)' stroke-miterlimit='10'
                      pointer-events='stroke'/>";
                }
            }
        }

        foreach ($this->Vertexes as $Vertex) {
            $svg .= "<rect x='{$Vertex->getX()}' y='{$Vertex->getY()}' width='$this->defaultVertexWidth' height='$this->defaultVertexHeight' rx='9' ry='9' fill='#{$Vertex->getFill()}' stroke='rgb(0, 0, 0)' pointer-events='all'/>";
            $svg .= "<text x='" . ($Vertex->getX() + 5) . "' y='" . ($Vertex->getY() + 15) . "'>{$Vertex->getName()}</text>";

            if ($this->debug) {
                $svg .= "<text font-size='6' x='" . $Vertex->getX() . "' y='" . (($Vertex->getY()) - 7) . "'>x{$Vertex->getX()} y{$Vertex->getY()}</text>";
                $svg .= "<text font-size='6' x='" . $Vertex->getX() . "' y='" . (($Vertex->getY()) - 2) . "'>d{$Vertex->getDisplaceX()} : d{$Vertex->getDisplaceY()}</text>";

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
