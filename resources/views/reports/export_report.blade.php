<?php

$report->run()
    ->export('ExportReport')
    ->settings([
        // "useLocalTempFolder" => true,
    ])
    ->pdf(array(
        "format" => "A4",
        "orientation" => "portrait",
        //"zoom"=>2
    ))
    ->toBrowser("sakila_rental.pdf");
?>
