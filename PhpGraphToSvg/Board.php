<?php

namespace Cmcdota\PhpGraphToSvg;


class Board
{
    public array $Vertexes;
    public float $temperature;
    public int $steps;
    public int $defaultVertexWidth;
    public int $defaultVertexHeight;
    public int $boardWidth;
    public int $boardHeight;
    public int $perfectDistance;

    private float $defaultSpace;

    private bool $debug;

    private float $temperatureDecrease;


    public function __construct($array, bool $debug = false)
    {
        $this->debug = $debug;
        $this->temperature = 15;
        $this->steps = 100;
        $this->temperatureDecrease = $this->temperature / $this->steps;

        $this->defaultVertexWidth = 100;
        $this->defaultVertexHeight = 50;

        //1) Инициализация доски, расчет размера доски.
        $this->boardWidth = $this->defaultVertexWidth * count($array) * 2;
        $this->boardHeight = $this->defaultVertexHeight * count($array) * 2;
        $boardSize = $this->boardWidth * $this->boardHeight;

        $this->defaultSpace = sqrt($boardSize) / 4;

        //4) Расчет идеального размера расстояния.
        $this->perfectDistance = sqrt($boardSize / (count($array))) / 2;

        //2) Создание вершин
        $this->Vertexes = $this->initVertexes($array);

        //3) Создать между всеми элементами связи - притяжение либо отталкивание.
        $this->initVertexedges($array);


        //5) Функция расчета в каждой вершине каждого connection.
        $this->calculate();
    }

    /**
     * @param $boardSize
     * @param $array
     * @return Vertex[] array
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
            $Vertexes[$key] = new Vertex($x, $y, $data['name'], $this->defaultVertexWidth, $this->defaultVertexHeight, $this->perfectDistance);
        }
        return $Vertexes;
    }

    function initVertexedges($array)
    {
        foreach ($this->Vertexes as $key => $Vertex) {
            foreach ($this->Vertexes as $key2 => $Vertex2) {
                if ($key != $key2) {
                    if (in_array($key2, $array[$key]['edges']) || in_array($key, $array[$key2]['edges'])) {
                        $Vertex->createedge($Vertex2, true);
                    } else {
                        $Vertex->createedge($Vertex2, false);
                    }
                }
            }
        }
    }


    public function calculate()
    {
        foreach ($this->Vertexes as $Vertex) {
            $Vertex->calculateMoving($this->temperature);
        }
        return $this;
    }

    public function move()
    {
        foreach ($this->Vertexes as $key => $Vertex) {
            $Vertex->move($this->boardWidth, $this->boardHeight, $this->temperature);
        }
        $this->temperature -= $this->temperatureDecrease;
        $this->calculate();
        return $this;
    }

    public function calculateAndMove()
    {
        $this->calculate();
        $this->move();
    }


    public function draw()
    {
        $svg = '<svg xmlns="http://www.w3.org/2000/svg"
             width="' . ($this->boardWidth + 100) . 'px"
             height="' . ($this->boardHeight + 100) . 'px" viewBox="-5 -5 ' . $this->boardWidth . ' ' . $this->boardHeight . '">
            <g>';

        foreach ($this->Vertexes as $Vertex) {
            $svg .= "<rect x='{$Vertex->getX()}' y='{$Vertex->getY()}' width='{$this->defaultVertexWidth}' height='{$this->defaultVertexHeight}' rx='9' ry='9' fill='rgb(255, 255, 255)' stroke='rgb(0, 0, 0)'
                      pointer-events='all'/>";
            $svg .= "<text x='" . $Vertex->getX(true) . "' y='" . (($Vertex->getY(true))) . "'>{$Vertex->getName()}</text>";

            if ($this->debug) {
                $svg .= "<text x='" . $Vertex->getX() . "' y='" . (($Vertex->getY()) - 5) . "'>x{$Vertex->getX()} y{$Vertex->getY()}</text>";
                $svg .= "<text x='" . $Vertex->getX() . "' y='" . (($Vertex->getY()) + 15) . "'>d{$Vertex->getDispatchX()} : d{$Vertex->getDispatchY()}</text>";

            }
        }

        $only_one = false;
        foreach ($this->Vertexes as $key => $Vertex) {
            foreach ($Vertex->getedges() as $edge) {
                list($x1, $y1, $x2, $y2) = $edge->getX1Y1X2Y2(true);
                $rgb = '150, 0, 0';
                $attractive = $edge->isAttractive();
                if ($attractive) {
                    $rgb = '50, 250, 50';
                } else {
                    $x1 += 15;
                    $y1 += 15;
                    $x2 -= 15;
                    $x2 -= 15;
                }
                //if ($this->debug and $key==1 || $attractive) {
                if ($this->debug) {
                    $svg .= "<path d='M $x1 $y1 L $x2 $y2' fill='none' stroke='rgb($rgb)' stroke-miterlimit='10'
                      pointer-events='stroke'/>";
                    $svg .= "<text x='" . (($x1 + $x2) / 2 - 30) . "' y='" . (($y1 + $y2) / 2) . "'>dist={$edge->calculateDistanceBetweenVertexes()}</text>";
                    $svg .= "<text x='" . (($x1 + $x2) / 2 - 50) . "' y='" . (($y1 + $y2) / 2 + 15) . "'>R{$edge->getMoveRepel()} A{$edge->getMoveAttraction()}</text>";
                }
            }
            $only_one = false;
        }

        /*
        $svg2 .= '       
                 <path d="M 320 60 L 320 133.63" fill="none" stroke="rgb(0, 0, 0)" stroke-miterlimit="10"
                      pointer-events="stroke"/>
                <rect x="260" y="0" width="120" height="60" rx="9" ry="9" fill="rgb(255, 255, 255)" stroke="rgb(0, 0, 0)"
                      pointer-events="all"/>
                
                <path d="M 380 170 L 453.63 170" fill="none" stroke="rgb(0, 0, 0)" stroke-miterlimit="10"
                      pointer-events="stroke"/>
                
                <rect x="260" y="140" width="120" height="60" rx="9" ry="9" fill="rgb(255, 255, 255)" stroke="rgb(0, 0, 0)"
                      pointer-events="all"/>
                    ';
        */
        $svg .= '</g></svg>';
        return $svg;
    }


}