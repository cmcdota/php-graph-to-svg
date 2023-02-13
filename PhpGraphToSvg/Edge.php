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
        $distance = $this->calculateDistanceBetweenVertexes();
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
        $distance = $this->calculateDistanceBetweenVertexes();
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

    public function calculateDistanceBetweenVertexes($centered = true): float
    {
        list($x1, $y1) = $this->sourceVertex->getXY($centered);
        list($x2, $y2) = $this->targetVertex->getXY($centered);
        return round(sqrt(($x1 - $x2) * ($x1 - $x2) + ($y1 - $y2) * ($y1 - $y2)), 1);
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