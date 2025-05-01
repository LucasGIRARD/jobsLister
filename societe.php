<?php

include 'SQL.php';

$connection = openSQLConnexion();

if (isset($_GET['type']) && !empty($_GET['type'])) {
	$listC = select($connection, "SELECT id, name as companie, esn, TYPES_id AS type
	FROM COMPANIES
	WHERE TYPES_id = ".$_GET['type']." 
	ORDER BY name");
} else {
	$listC = select($connection, "SELECT id, name as companie, esn, TYPES_id AS type
	FROM COMPANIES
	WHERE 1 
	ORDER BY name");	
}


$listT = select($connection, "SELECT  id, name
	FROM TYPES
	WHERE 1 
	ORDER BY name");
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
<body>

	<div class="pure-g">
		<div class="pure-u-1 list">
			<div>
				<input type="text" name="newFilterCompanie" id="newFilterCompanie">
				<button id="createNewFilterCompanie">create</button>
				<select name="selectType" id="selectType">
					<option></option>
					<?php
					foreach ($listT as $value) {
						echo '<option value="'.$value['id'].'">'.$value['name'].'</option>';
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
					<th>esn</th>
					<th>link</th>
				</tr>
				</thead>
				<tbody id="contentArea" class="clusterize-content">
				<?php

/* <td>
<select id="T-'.$j['id'].'" class="setCompanyType">
<option></option>';
foreach ($listT as $value) {
	echo '<option value="'.$value['id'].'" '.($j['type'] == $value['id']?'selected':'').'>'.$value['name'].'</option>';
}
echo '</select><span id="R-Ajax-'.$j['id'].'"></span>
</td> */

				foreach ($listC as $j) {
					echo '<tr>
					<td>'.$j['companie'].'</td>					

					<td>
						<input type="radio" name="esn-' . $j['id'] . '" id="esn-1-' . $j['id'] . '" class="esnRadio pure-radio" value="1"' . ($j['esn']==1?' checked':'') . ' />
						<label for="esn-1-' . $j['id'] . '" class="pure-radio">Oui</label>
						&nbsp;&nbsp;&nbsp;
						<input type="radio" name="esn-' . $j['id'] . '" id="esn-0-' . $j['id'] . '" class="esnRadio pure-radio" value="0"' . ($j['esn']===0?' checked':'') . ' />
						<label for="esn-0-' . $j['id'] . '" class="pure-radio">Non</label>
					</td>
					
					<td><a tabindex="-1" target="_blank" href="'.$j['companie'].'">lien</a></td>
					</tr>';
				}
				?>
			</tbody>
			</table>
		</div>
	</div>
</div>
</body>
</html>