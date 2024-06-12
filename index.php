<?php

include 'SQL.php';

$connection = openSQLConnexion();

$listJ = select($connection, "SELECT 
	C.name as companie, J.name, localisation, salary, candidats, filtered, postulated, refused, searched, 
	link, S.name AS source
	FROM JOBS AS J 
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id 
	LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id 
	LEFT JOIN SOURCES AS S ON S.id = ShJ.SOURCES_id 
	WHERE 1 
	ORDER BY C.name, J.name, S.name");
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
	<script src="http://d3js.org/d3.v3.js"></script>
	<script type="text/javascript" src="/js/js.js"></script>
</head>
<body>

	<div class="pure-g">
		<div class="pure-u-1 list">
			<div>
				<input type="text" name="newFilter" id="newFilter">
				<button id="createNewFilter">create</button>
				<select name="typeJ" id="typeJ">
					<option></option>
					<?php
					foreach ($typeJ as $key => $value) {
						echo '<option value="'.$key.'">'.$value.'</option>';
					}
					?>
				</select>
				<span id="responseAjax"></span>
				
			<!--
				<div style="text-align: right;display: inline-block;width: calc(100% - 855px);height: 30px">
					<input type="checkbox" name="viewed_all" id="viewed_all" style="top: 9px;right: 9px;position: relative;">
				</div>
			-->
		</div>
		<div id="scrollArea" class="clusterize-scroll">
			<table class="pure-table pure-table-striped">
				<thead>
				<tr>
					<th>companie</th>
					<th>name</th>
					<th>source</th>
					<th>localisation</th>
					<th>salary</th>
					<th>candidats</th>
					<th>filtered</th>
					<th>postulated</th>
					<th>refused</th>
					<th>searched</th>
					<th>link</th>
				</tr>
				</thead>
				<tbody id="contentArea" class="clusterize-content">
				<?php

				foreach ($listJ as $j) {
					echo '<tr>
					<td>'.$j['companie'].'</td>
					<td>'.$j['name'].'</td>
					<td>'.$j['source'].'</td>
					<td>'.$j['localisation'].'</td>
					<td>'.$j['salary'].'</td>
					<td>'.$j['candidats'].'</td>
					<td>'.$j['filtered'].'</td>
					<td>'.$j['postulated'].'</td>
					<td>'.$j['refused'].'</td>
					<td>'.$j['searched'].'</td>
					<td><a href="'.$j['link'].'">lien</a></td>
					</tr>';

					/*
					$html = '<tr><td hidden>'.$value['idYT'].'</td>';
					$html .= '<td><span title="'.$value['dateCreation'].'">'.mb_substr($value['dateCreation'],0,4).'</span></td>';
					if (!isset($_GET['chaine'])) {
						$html .= '<td id="'.$value['CHAINE_id'].'">'.$chaines[$value['CHAINE_id']].'</td>';
					}

					$html .= '<td class="title">'.$value['nom'].'</td>';
					$html .= '<td><input type="checkbox" name="viewed_'.$value['id'].'"></td>';
					$html .= '</tr>';
					*/
				}
				?>
			</tbody>
			</table>
		</div>
	</div>
</div>
</body>
</html>