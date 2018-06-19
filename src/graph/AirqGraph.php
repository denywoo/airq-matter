<?php

namespace app\graph;


use Amenadiel\JpGraph\Graph\Graph;
use Amenadiel\JpGraph\Plot\LinePlot;

class AirqGraph
{
    public $dataX;
    public $dataY;
    public $title = '';
    public $xAxisTitle = '';
    public $yAxisTitle = '';
    public $width;
    public $height;
    public $imgFormat = 'png';
    
    public function show() {
        $graph = $this->prepareGraph();
        $graph->Stroke();
    }

    public function save(string $path) {
        $graph = $this->prepareGraph();
        $graph->Stroke($path);
    }

    private function prepareGraph() {
        $graph = new Graph($this->width,$this->height);
        $graph->SetScale('textlin');
        $graph->SetShadow(true);
        $graph->yaxis->title->Set($this->yAxisTitle);

        $graph->xaxis->SetTickLabels($this->dataX);
        $graph->xaxis->SetTextLabelInterval(1);
        $graph->yaxis->title->Set($this->yAxisTitle);



        $linePlot = new LinePlot($this->dataY);
        $linePlot -> SetColor ( 'blue' );
        $linePlot -> SetWeight ( 2 );

        $graph->Add($linePlot);

        $graph->img->SetImgFormat($this->imgFormat);
        
        return $graph;
    }
}