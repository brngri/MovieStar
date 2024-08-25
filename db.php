<?php 

    $host = "localhost";
    $dbname = "moviestar";
    $user = "root";
    $password = "";

    $conn = new PDO("mysql:dbname=$dbname;host=$host", $user, $password);

    // Habilitar erros PDO

    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->setAttribute(PDO::ATTR_EMULATE_PREPARES,false);