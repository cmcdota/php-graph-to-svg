<?php

namespace Cmcdota\PhpGraphToSvg;


class Vertex
{

    private string $name;
    private string $desc;
    public float $x;
    public float $y;

    public array $edges;
    private int $height;
    private int $width;
    private bool $calculated = false;
    private float $dispatchX;
    private float $dispatchY;
    private float $perfectDistance;

    public function __construct($x, $y, $name, $width, $height, $perfectDistance)
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->x = $x;
        $this->y = $y;
        $this->perfectDistance = $perfectDistance;
        $this->desc = "Description for future";
        return $this;
    }

    public function calculateMoving(): array
    {
        //Проходимся по всем линкам, считаем насколько их надо сдвинуть

        $this->dispatchX = 0;
        $this->dispatchY = 0;
        foreach ($this->getedges() as $edge) {

            $distance = $edge->calculateDistanceBetweenVertexes();
            //Считаем, насколько нужно сдвинуться в сторону вектора
            list($repel, $attraction) = $edge->calculateMovingEdgeVertexes();

            //$repel - показывает на столько пикселей нужно отодвинуть друг от друга две вершины
            //$attraction - на какую дистанцию нужно придвинуть друг к другу две вершины

            list($sourceX, $sourceY, $targetX, $targetY) = $edge->getX1Y1X2Y2();
            $this->dispatchX += $this->smallerOrBigger($sourceX, $targetX) * $repel / $distance / 10;
            $this->dispatchY += $this->smallerOrBigger($sourceY, $targetY) * $repel / $distance / 10;

            $this->dispatchX -= $this->smallerOrBigger($sourceX, $targetX) * $attraction / $distance / 10;
            $this->dispatchY -= $this->smallerOrBigger($sourceY, $targetY) * $attraction / $distance / 10;
        }
        //$this->dispatchX = $this->dispatchX ;
        //$this->dispatchY = $this->dispatchY ;
        $this->calculated = true;
        return [$this->dispatchX, $this->dispatchY];
    }

    function smallerOrBigger($one, $two)
    {
        if ($one < $two) {
            return abs($one - $two) * -1;
        } else {
            return abs($one - $two);
        }
    }

    public function move($maxWidth, $maxHeight)
    {
        if (!$this->calculated) {
            throw new \Exception("Need calculate() before move!");
        }
        //Проходимся по всем линкам,
        $this->x += $this->dispatchX;
        $this->y += $this->dispatchY;

        if ($this->x + $this->width / 2 >= $maxWidth) {
            $this->x = $maxWidth - $this->width / 2;
        }
        if ($this->x < 0) {
            $this->x = 10;
        }
        if ($this->y + $this->height / 2 >= $maxHeight) {
            $this->y = $maxHeight - $this->height / 2;
        }
        if ($this->y < 0) {
            $this->y = 10;
        }

        $this->dispatchX = 0;
        $this->dispatchY = 0;

        $this->calculated = false;
    }


    public function createedge(Vertex $targetVertex, $attractive)
    {
        if ($targetVertex == $this) {
            throw new \Exception("edge Creation error: Target Vertex matches source Vertex!");
        }
        $this->edges[] = new Edge($this, $targetVertex, $attractive, $this->perfectDistance);
    }


    public function getX($centered = false): float
    {
        return round($this->x + ($centered ? $this->width / 2 : 0), 0);
    }

    public function getY($centered = false): float
    {
        return round($this->y + ($centered ? $this->height / 2 : 0), 0);
    }

    public function getXY($centered = false): array
    {
        return [$this->getX($centered), $this->getY($centered)];
    }

    public function getName()
    {
        return $this->name ?? 'name is missing';
    }

    /**
     * @return Edge[]
     */
    public function getedges(): array
    {
        return $this->edges ?? [];
    }

    /**
     * @return float
     */
    public function getDispatchX(): float
    {
        return round($this->dispatchX, 1);
    }

    /**
     * @return float
     */
    public function getDispatchY(): float
    {
        return round($this->dispatchY, 1);
    }

    /**
     * @return string
     */
    public function getDesc(): string
    {
        return $this->desc;
    }

    /**
     * @param string $desc
     */
    public function setDesc(string $desc): void
    {
        $this->desc = $desc;
    }

}