<?php

$databaseConnection = new PDO(
    // Replaced :12061 with ;port=12061 and changed dbname to defaultdb
    'mysql:host=mysql-3ff31b32-valenciabusiness86-035d.i.aivencloud.com;port=12061;dbname=defaultdb;charset=utf8mb4', 
    'avnadmin', 
    'AVNS_Kh62UywQararjZObsgv', 
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        
        // Required for Aiven SSL connections. 
        // Download the CA certificate from your dashboard, place it in your project, and point to its path here:
        PDO::MYSQL_ATTR_SSL_CA => __DIR__ . '/ca.pem'
    ]
);