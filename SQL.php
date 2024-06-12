<?php
function openSQLConnexion() {
    $host = 'mysql:host=127.0.0.1;port=3306;dbname=jobsScraper';
    $user = 'root';    
    $password = '';
    $option = array(PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8", PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION);
    try {
        $connection = new PDO($host, $user, $password, $option);
        $connection->query('SET lc_time_names="fr_FR"');        
    } catch (PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
    return $connection;
}

function closeSQLConnexion() {    
    unset($GLOBALS['connection']);

}

function insertUpdate($connection, $query, $tabVars, $count = false) {
    if (!$count) {
        try {
            $resultat = $connection->prepare($query);
            foreach ($tabVars as $vars) {                
                $resultat->execute($vars);
            }
            return 1;
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
    } else {
        try {
            $resultat = $connection->prepare($query);
            $i=0;
            $count = array();
            foreach ($tabVars as $vars) {                
                if ($resultat->execute($vars)){
                    $count[$i]=$resultat->rowCount();
                }
                else {
                    $count[$i]=false;
                }
                $i++;
            }
        } catch (PDOException $e) {
            echo 'ERROR: ' . $e->getMessage();
        }
        return $count;
    }

}

function select($connection, $query, $vars = null) {    
    try {
        $resultat = $connection->prepare($query);
        $resultat->execute($vars);
        return $resultat->fetchAll();
    } catch (PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
}

function getLastId(&$connection) {    
    try {        
        return $connection->lastInsertId();
    } catch (PDOException $e) {
        echo 'ERROR: ' . $e->getMessage();
    }
}
?>