<?php

namespace Cmcdota\PhpGraphToSvg;

class Edge
{

    private Vertex $sourceVertex;
    private Vertex $targetVertex;
    private bool $attractive;
    private float $moveAttraction = 0;
    private float $moveRepel = 0;
    private float $moveCoefficient = 0;

    public function __construct(Vertex $sourceVertex, Vertex $targetVertex, bool $attractive = false, float $perfectDistance = 100)
    {
        $this->sourceVertex = $sourceVertex;
        $this->targetVertex = $targetVertex;
        $this->attractive = $attractive;
        $this->perfectDistance = $perfectDistance;
    }

    /**
     * Считаем, насколько в процентах нужно сдвинуть
     * текущую вершину по отношению к другой вершине
     * для достижения идеального расстояния $perfectDistance
     *
     * @param float $perfectDistance
     * @return float
     */
    public function calculateMovingEdgeVertexes(): array
    {
        $this->moveAttraction = 0;
        //Во сколько раз нужно отодвинуться
        $this->calculateRepel();
        if ($this->isAttractive()) {
            //Во сколько раз нужно сблизиться
            $this->calculateAttraction();
        }
        //$this->moveCoefficient = ($this->moveRepel + $this->moveAttraction);


        return [$this->moveRepel, $this->moveAttraction];
    }

    /**
     * Возвращает целевое значение расстояния - какое должно быть по его мнению
     *
     * @return float
     */
    public function calculateRepel(): float
    {
        $distance = $this->calculateDistanceBetweenVertexes();
        $this->moveRepel = $this->perfectDistance * $this->perfectDistance / max($distance, 1);
        return $this->moveRepel;
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
            //return $perfectDistance * $perfectDistance / $distance;
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
        //get source X, Y
        //get target X, Y
        list($x1, $y1) = $this->sourceVertex->getXY($centered);
        list($x2, $y2) = $this->targetVertex->getXY($centered);
        $x = $x1 - $x2;
        $y = $y1 - $y2;

        return round(sqrt($x * $x + $y * $y), 1);
    }

    /**
     * @return bool
     */
    public function isAttractive(): bool
    {
        return $this->attractive;
    }

    /**
     * @param bool $attractive
     */
    public function setAttractive(bool $attractive): void
    {
        $this->attractive = $attractive;
    }

    /**
     * @return float
     */
    public function getMoveAttraction(): float
    {
        return round($this->moveAttraction, 2);
    }

    /**
     * @return float
     */
    public function getMoveRepel(): float
    {
        return round($this->moveRepel, 2);
    }

    /**
     * @return float
     */
    public function getMoveCoefficient(): float
    {
        return round($this->moveCoefficient, 3);
    }
}