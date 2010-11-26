<?php

/**
	Примеры использования GNUPlot на низком уровене
	Другие примеры: http://php-gnuplot.sourceforge.net/
*/

require_once('config.php');
mkpath(config('cache_dir'));

require_once(config('php_gnuplot.include'));

$p = new GNUPlot();

$p->set('terminal pngcairo enhanced font "Tahoma,8" size 1024,768');
$p->set('output "test.png"');

$p->setRange('x', 0, 8);
$p->setRange('y', 0, 6);

$p->setTitle("2D Test"); 

$p->draw2DLine( 0,0, 1,1);

$data = new PGData('test Data');
$data->addDataEntry( array(1, 2) );
$data->addDataEntry( array(2, 3) );
$data->addDataEntry( array(3, 4) );
$data->addDataEntry( array(4, 4) );
$data->addDataEntry( array(5, 3) ); 

$p->plotData( $data, 'lines', '1:($2)' );
$p->set2DLabel("2D Label", 1,1 ); 
//$p->plotData( $data2, 'linespoints', '($1/20):($2*2)' ); 
//$p->setSize( 0.6, 0.6 );
//$p->export('test2D.png');
$p->close();
