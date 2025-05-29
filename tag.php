<?php

include 'SQL.php';

$connection = openSQLConnexion();


$listJ = select($connection, "SELECT J.name
	FROM JOBS AS J 
	LEFT JOIN JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id WHERE JhF.id IS NULL
	");

//LEFT JOIN JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id WHERE JhF.id IS NULL

$tagsSQL = select($connection, "SELECT value FROM FILTERS WHERE 1 ORDER BY value");

function cb1($a)
{
    return $a['value'];
}

$tags = array_map('cb1', $tagsSQL);

$words = [];

$filteringArray = [
    'la',
    'dans',
    'chez',
    'du',
    'de',
    'of',
    'h/f',
    'f/h',
    '-',
    '/',
    'with',
    'des',
    'h/f/d',
    'f/h/x',
    'h/f/x',
    'and',
    'une',
    'f/m',
    'f/h/nb',
    'm/f/x',
    'f-h',
    '@',
    'et',
    '&',
    'en',
    'à',
    'as',
    '|',
    ' ',
    'in',
    'un',
    '',
    'a'
];

$toReplaceA = ["\n", ',', '.', '(', ')', '[', ']', ':', '·', '°', 'd\''];
$replacedByA = [' ', '_', '_', '', '', '', '', '', '', '', ''];

function stripAccents($str)
{
    return strtr(utf8_decode($str), utf8_decode('àáâãäçèéêëìíîïñòóôõöùúûüýÿÀÁÂÃÄÇÈÉÊËÌÍÎÏÑÒÓÔÕÖÙÚÛÜÝ'), 'aaaaaceeeeiiiinooooouuuuyyAAAAACEEEEIIIINOOOOOUUUUY');
}

if (isset($_GET['p']) && $_GET['p'] == '2') {
    foreach ($listJ as $j) {
        $name = stripAccents(str_replace($toReplaceA, $replacedByA, strtolower($j['name'])));

        $jArray = mb_split(' ', $name);
        if (is_array($jArray)) {
            foreach ($jArray as $i => $word) {
                if ($i == 0) {
                    continue;
                }
                $lword =  $jArray[$i - 1];
                $tword = $lword . ' ' . $word;

                if (in_array($tword, $tags) || in_array($lword, $filteringArray) || in_array($word, $filteringArray)) {
                    continue;
                }

                if (!isset($words[$tword])) {
                    $words[$tword] = 1;
                } else {
                    $words[$tword]++;
                }
            }
        }
    }
} else {
    foreach ($listJ as $j) {
        $name = stripAccents(str_replace($toReplaceA, $replacedByA, strtolower($j['name'])));
        $jArray = mb_split(' ', $name);
        if (is_array($jArray)) {
            foreach ($jArray as $word) {
                if (in_array($word, $tags) || in_array($word, $filteringArray)) {
                    continue;
                }
                if (!isset($words[$word])) {
                    $words[$word] = 1;
                } else {
                    $words[$word]++;
                }
            }
        }
    }
}

arsort($words);


?>

<!DOCTYPE html>
<html>

<head>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title></title>
    <link rel="stylesheet" type="text/css" href="/css/pure-min.css">
    <link rel="stylesheet" href="/css/grids-responsive-min.css">
    <link href="/css/clusterize.css" rel="stylesheet">
    <link href="/css/main.css" rel="stylesheet">
    <script type="text/javascript" src="/js/jquery.min.js"></script>
    <script type="text/javascript" src="/js/jquery.mousewheel.min.js"></script>
    <script type="text/javascript" src="/js/tattica.umd.js"></script>
    <script src="/js/clusterize.min.js"></script>
    <script src="https://d3js.org/d3.v3.js"></script>
    <script type="text/javascript" src="/js/js.js"></script>
</head>

<body id="tag">
    <div class="pure-g">
        <div class="pure-u-1 list">
            <button id="reRunFilters" class="pure-button pure-button-primary">reRun filters</button>
            <div id="scrollArea" class="clusterize-scroll">
                <table class="pure-table pure-table-striped">
                    <thead>
                        <tr>
                            <th>word</th>
                            <th>count</th>
                            <th>ban</th>
                            <th>safe</th>
                            <th>search</th>
                            <th>google</th>

                        </tr>
                    </thead>
                    <tbody id="contentArea" class="clusterize-content">
                        <?php
                        foreach ($words as $word => $count) {
                            if ($count > 1 && $count < 999999) {
                                echo '<tr>
                                <td>' . $word . '</td>
                                <td>' . $count . '</td>
                                <td><button class="banWord pure-button button-warning" data-word="' . urlencode($word) . '">ban</button></td>
                                <td><button class="safeWord pure-button button-warning" data-word="' . urlencode($word) . '">safe</button></td>
                                <td><a target="_blank" href="index.php?w=' . urlencode($word) . '" class="pure-menu-link">ici</a></td>
                                <td><a target="_blank" href="https://www.google.com/search?q=' . urlencode($word) . '" class="pure-menu-link">ici</a></td>
                                </tr>';
                            }
                        }
                        ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</body>

</html>