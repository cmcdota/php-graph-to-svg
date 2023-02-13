<?php

namespace Cmcdota\PhpGraphToSvg;


use Exception;

class Vertex
{

    private string $name;
    private string $desc;
    public float $x;
    public float $y;

    public array $edges;
    public array $data;
    private int $height;
    private int $width;
    public string $fill;
    private bool $calculated = false;
    private float $displaceX;
    private float $displaceY;
    private float $perfectDistance;

    /**
     * @param float $x
     * @param float $y
     * @param string $name
     * @param float $width
     * @param float $height
     * @param float $perfectDistance
     * @param mixed $fill
     * @param array $data
     *
     * @throws Exception
     */
    public function __construct(float $x, float $y, string $name, float $width, float $height, float $perfectDistance, $fill , array $data)
    {
        $this->name = $name;
        $this->width = $width;
        $this->height = $height;
        $this->data = $data;
        $this->x = $x;
        $this->y = $y;
        $this->perfectDistance = $perfectDistance;
        $this->desc = "Description for future";
        if (isset($fill)) {
            if (is_numeric($fill)) {
                $this->fill = base_convert($fill, 10, 16);
            } else {
                $this->fill = $fill;
            }
        } else {
            $this->fill = base_convert(random_int(10000, 16777215), 10, 16);
        }
        return $this;
    }

    /**
     * @return $this
     */
    public function calculateMoving(): Vertex
    {
        //Проходимся по всем линкам, считаем насколько их надо сдвинуть
        $this->displaceX = 0;
        $this->displaceY = 0;
        foreach ($this->getedges() as $edge) {

            $distance = $edge->calculateDistanceBetweenVertexes();
            //Calculating moving:
            //Считаем, насколько нужно сдвинуться в сторону вектора
            list($Repulse, $attraction) = $edge->calculateMovingEdgeVertexes();

            list($sourceX, $sourceY, $targetX, $targetY) = $edge->getX1Y1X2Y2();

            //Difference between Source and Target
            $edge->setRepulseX($this->deltaUnit($sourceX, $targetX, $distance) * $Repulse);
            $edge->setRepulseY($this->deltaUnit($sourceY, $targetY, $distance) * $Repulse);

            $this->displaceX += $edge->getRepulseX();
            $this->displaceY += $edge->getRepulseY();

            //
            $edge->setAttractionX($this->deltaUnit($sourceX, $targetX, $distance) * $attraction);
            $edge->setAttractionY($this->deltaUnit($sourceY, $targetY, $distance) * $attraction);

            $this->displaceX -= $edge->getAttractionX();
            $this->displaceY -= $edge->getAttractionY();
        }

        $this->calculated = true;
        return $this;
    }

    /**
     * Main calculation.
     * Returns value from 0.000 to 1.000, meaning which part this X or Y in distance.
     *
     *
     * @param float $source
     * @param float $target
     * @param float $distance
     * @return float
     */
    function deltaUnit(float $source, float $target, float $distance): float
    {
        if ($distance == 0) {
            return 0;
        }
        return ($source - $target) / ($distance);
    }

    public function move($maxWidth, $maxHeight, float $temperature): Vertex
    {
        if (!$this->calculated) {
            throw new Exception("Need calculate() before move!");
        }

        $displaceLen = sqrt($this->displaceX * $this->displaceX + $this->displaceY * $this->displaceY);
        $displaceScale = min($displaceLen, $temperature);

        $this->x += $this->displaceX / $displaceLen * $displaceScale;
        $this->y += $this->displaceY / $displaceLen * $displaceScale;

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

        $this->displaceX = 0;
        $this->displaceY = 0;

        $this->calculated = false;
        return $this;
    }


    public function createEdge(Vertex $targetVertex, $attractive, bool $isMain = false): Vertex
    {
        if ($targetVertex === $this) {
            throw new Exception("Edge Creation error: Target Vertex matches source Vertex!");
        }
        $this->edges[] = new Edge($this, $targetVertex, $attractive, $this->perfectDistance, $isMain);
        return $this;
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

    public function getName(): string
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
    public function getDisplaceX(): float
    {
        return round($this->displaceX, 1);
    }

    /**
     * @return float
     */
    public function getDisplaceY(): float
    {
        return round($this->displaceY, 1);
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


    public function getFill(): string
    {
        return $this->fill;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     */
    public function setData(array $data): void
    {
        $this->data = $data;
    }


}