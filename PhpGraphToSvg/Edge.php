<?php

namespace Cmcdota\PhpGraphToSvg;

class Edge
{

    private Vertex $sourceVertex;
    private Vertex $targetVertex;
    private bool $attractive;
    private float $moveAttraction = 0;
    private float $moveRepulse = 0;
    private float $RepulseX = 0;
    private float $RepulseY = 0;
    private float $attractionX = 0;
    private float $attractionY = 0;
    private bool $isMainEdge;

    private float $perfectDistance;

    public function __construct(Vertex $sourceVertex, Vertex $targetVertex, bool $attractive = false, float $perfectDistance = 100, bool $isMain = false)
    {
        $this->sourceVertex = $sourceVertex;
        $this->targetVertex = $targetVertex;
        $this->attractive = $attractive;
        $this->perfectDistance = $perfectDistance;
        $this->isMainEdge = $isMain;
    }

    /**
     * Calculates Repulse and Attraction Forces
     * Считаем, насколько в процентах нужно сдвинуть
     * текущую вершину по отношению к другой вершине
     * для достижения идеального расстояния $perfectDistance
     *
     * @return array
     */
    public function calculateMovingEdgeVertexes(): array
    {
        $this->calculateRepulse();
        $this->calculateAttraction();
        return [$this->moveRepulse, $this->moveAttraction];
    }

    /**
     * Calculates repulse vertex value
     * Возвращает целевое значение расстояния - какое должно быть по его мнению
     *
     * @return float
     */
    public function calculateRepulse(): float
    {
        $distance = $this->getDistance();
        if ($distance == 0) {
            return 0;
        }
        $this->moveRepulse = $this->perfectDistance * $this->perfectDistance / $distance;
        return $this->moveRepulse;
    }

    /**
     * Возвращает целевое значение расстояния - какое должно быть по его мнению
     *
     * @return float
     */
    public function calculateAttraction(): float
    {
        $distance = $this->getDistance();
        $this->moveAttraction = 0;
        if ($this->isAttractive()) {
            return $this->moveAttraction = $distance * $distance / $this->perfectDistance;
        }
        return $this->moveAttraction;
    }

    public function getX1Y1X2Y2($centered = false): array
    {
        list($x1, $y1) = $this->sourceVertex->getXY($centered);
        list($x2, $y2) = $this->targetVertex->getXY($centered);
        return [$x1, $y1, $x2, $y2];
    }

    public function getDistance($centered = true): float
    {
        list($x1, $y1) = $this->sourceVertex->getXY($centered);
        list($x2, $y2) = $this->targetVertex->getXY($centered);
        return round(sqrt(($x1 - $x2) * ($x1 - $x2) + ($y1 - $y2) * ($y1 - $y2)), 1);
    }

    public function getGetLen(): float
    {
        list($x1, $y1) = $this->sourceVertex->getXY(true);
        list($x2, $y2) = $this->targetVertex->getXY(true);
        return round(sqrt(($x1) * ($x1) + ($y1) * ($y1)), 1);
    }

    /**
     * Возвращает скалярное произведение двух векторов
     *
     * @return float
     */
    public function getScalar(): float
    {
        list($x1, $y1) = $this->sourceVertex->getXY(true);
        list($x2, $y2) = $this->targetVertex->getXY(true);
        return $x1 * $x2 + $y1 * $y2;
    }


    /**
     * Находит точку соприкосновения с Target'ом, чтобы там нарисовать стрелку.
     *
     * @return array
     */
    public function findTargetCollisionPoint($sourceX = null, $sourceY = null, $targetX = null, $targetY = null, $borderSizeW = null, $borderSizeH = null): array
    {
        if (!isset($sourceX)) {
            list($sourceX, $sourceY, $targetX, $targetY) = $this->getX1Y1X2Y2(true);
            $borderSizeW = $this->targetVertex->getWidth() / 2;
            $borderSizeH = $this->targetVertex->getHeight() / 2;
        }

        //Будем считать угол, если смотреть из центра Target в сторону Source, чтобы найти точку соприкосновения изнутри Target'а.
        $Vx = $sourceX - $targetX;
        $Vy = $sourceY - $targetY;

        $vDistance = sqrt($Vx * $Vx + $Vy * $Vy);

        //Нормализация, приводим к единице:
        $Vx1 = $Vx / $vDistance;
        $Vy1 = $Vy / $vDistance;

        //Считаем, как быстрее мы доберемся до какой границы (вертикальной/горизонтальной)
        if (abs($Vx1) / $borderSizeW > abs($Vy1) / $borderSizeH) {
            $distanceToBorder = $borderSizeW;
            $bestSpeedToBorder = abs($Vx1);
        } else {
            $distanceToBorder = $borderSizeH;
            $bestSpeedToBorder = abs($Vy1);
        }
        $disposeX = $Vx1 * $distanceToBorder / $bestSpeedToBorder;
        $disposeY = $Vy1 * $distanceToBorder / $bestSpeedToBorder;

        return [$targetX + $disposeX, $targetY + $disposeY, $Vx1, $Vy1];
    }

    public function getAnArrow(float $x, float $y, float $Vx1, float $Vy1, float $scale)
    {
        $arrowPoints = [
            0 => [0, 0],
            1 => [3, 1],
            2 => [3, -1],
        ];

        $ret = [
            'x1' => $arrowPoints[0][0] * $scale,
            'y1' => $arrowPoints[0][1] * $scale,

            'x2' => $arrowPoints[1][0] * $scale,
            'y2' => $arrowPoints[1][1] * $scale,

            'x3' => $arrowPoints[2][0] * $scale,
            'y3' => $arrowPoints[2][1] * $scale,
        ];

        $ret2 = [];
        $ret2['x1'] = $x + $ret['x1'] * $Vx1 - $ret['y1'] * $Vy1;
        $ret2['y1'] = $y + $ret['x1'] * $Vy1 + $ret['y1'] * $Vx1;

        $ret2['x2'] = $x + $ret['x2'] * $Vx1 - $ret['y2'] * $Vy1;
        $ret2['y2'] = $y + $ret['x2'] * $Vy1 + $ret['y2'] * $Vx1;

        $ret2['x3'] = $x + $ret['x3'] * $Vx1 - $ret['y3'] * $Vy1;
        $ret2['y3'] = $y + $ret['x3'] * $Vy1 + $ret['y3'] * $Vx1;


        return $ret2;
    }

    public function subtractDistance(float $points): array
    {
        $scale = 1;
        $distance = $this->getDistance();
        if ($distance == 0) {
            return $this->scaleDistance(0);
        } else {
            $newDistance = $distance - $points;
            return $this->scaleDistance($newDistance / $distance);
        }
    }

    public function scaleDistance(float $scale): array
    {
        list($sourceX, $sourceY, $targetX, $targetY) = $this->getX1Y1X2Y2(true);

        $xV = $targetX - $sourceX;
        $xV = $xV * $scale;
        $xV = $xV + $sourceX;

        $yV = $targetY - $sourceY;
        $yV = $yV * $scale;
        $yV = $yV + $sourceY;
        return [$xV, $yV];
    }

    /**
     * Is attractive or not?
     * Притягивается или отталкивается?
     *
     * @return bool
     */
    public function isAttractive(): bool
    {
        return $this->attractive;
    }


    /**
     * @return float
     */
    public function getMoveAttraction(): float
    {
        return round($this->moveAttraction, 2);
    }

    /**
     * Returns repulse force value
     *
     * @return float
     */
    public function getMoveRepulse(): float
    {
        return round($this->moveRepulse, 2);
    }

    /**
     * @return float
     */
    public function getRepulseX(): float
    {
        return $this->RepulseX;
    }

    /**
     * @param float $RepulseX
     */
    public function setRepulseX(float $RepulseX): void
    {
        $this->RepulseX = $RepulseX;
    }

    /**
     * @return float
     */
    public function getRepulseY(): float
    {
        return $this->RepulseY;
    }

    /**
     * @param float $RepulseY
     */
    public function setRepulseY(float $RepulseY): void
    {
        $this->RepulseY = $RepulseY;
    }

    public function getRepulseAttrString(): string
    {
        $Rx = intval($this->getRepulseX());
        $Ry = intval($this->getRepulseY());

        $Ax = intval($this->getAttractionX());
        $Ay = intval($this->getAttractionY());

        return "r{$Rx}r$Ry a{$Ax}a$Ay";
    }

    /**
     * @return float
     */
    public function getAttractionX(): float
    {
        return $this->attractionX;
    }

    /**
     * @param float $attractionX
     */
    public function setAttractionX(float $attractionX): void
    {
        $this->attractionX = $attractionX;
    }

    /**
     * @return float
     */
    public function getAttractionY(): float
    {
        return $this->attractionY;
    }

    /**
     * @param float $attractionY
     */
    public function setAttractionY(float $attractionY): void
    {
        $this->attractionY = $attractionY;
    }

    /**
     * For future to draw an arrow
     * На будущее чтобы рисовать стрелки в конце
     *
     * @return bool
     */
    public function isMainEdge(): bool
    {
        return $this->isMainEdge;
    }
}
