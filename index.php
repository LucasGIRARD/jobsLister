<?php
$bodyClass = "";

include 'SQL.php';

$connection = openSQLConnexion();
$type = NULL;
if (isset($_GET['type'])) {
	$type = $_GET['type'];

	$listJ = select($connection, "SELECT 
	J.id, C.name as companie, J.name, localisation, salary, candidats, filtered, postulated, refused, searched, 
	link, S.name AS source
	FROM JOBS_has_FILTERS AS JhF
	LEFT JOIN JOBS AS J ON J.id = JhF.JOBS_id
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id 
	LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id 
	LEFT JOIN SOURCES AS S ON S.id = ShJ.SOURCES_id 
	WHERE JhF.FILTERS_id = ?
	ORDER BY C.name, J.name, S.name
	", array($type));
} else if (isset($_GET['w'])) {
	$before = array('% ', '%-', '%\'', '%/', '%,', '%(', '');
	$after = array(' %', '-%', '%\'', '/%', ',%', ')%', '');
	$filterSearch = array();
	foreach ($before as $key => $value) {
		foreach ($after as $key2 => $value2) {
			$filterSearch[] = $value . $_GET['w'] . $value2;
		}
	}

	$sqlWhere = 'J.name like ?';
	for ($i = 1; $i < count($filterSearch); $i++) {
		$sqlWhere .= 'or J.name like ?';
	}

	$listJ = select($connection, "SELECT 
	J.id, C.name as companie, J.name, localisation, salary, salaryMin, salaryMax, candidats, filtered, postulated, refused, searched, 
	link, S.name AS source
	FROM JOBS AS J 
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id 
	LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id 
	LEFT JOIN SOURCES AS S ON S.id = ShJ.SOURCES_id 
	WHERE $sqlWhere
	ORDER BY C.name, J.name, S.name
	", $filterSearch);
} else if (isset($_GET['s'])) {
	$bodyClass = "s";
	$listJ = select($connection, "SELECT 
	J.id, C.name as companie, J.name, localisation, salary, salaryMin, salaryMax, candidats, filtered, postulated, refused, searched, 
	group_concat(link SEPARATOR '|') AS link,group_concat(S.name SEPARATOR '<br>') AS source
	FROM JOBS AS J 
	LEFT JOIN  JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id 
	LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id 
	LEFT JOIN SOURCES AS S ON S.id = ShJ.SOURCES_id 
	WHERE JhF.id IS NULL and expired IS NULL and filtered is null and filtered2 is null and selected is not null
	GROUP BY J.id
	ORDER BY C.name, J.name
	");
} else if (isset($_GET['m'])) {
	$listJ = select($connection, "SELECT 
	J.id, C.name as companie, J.name, J.localisation, J.salary, J.candidats, J.filtered, J.postulated, J.refused, J.searched, 
	SJ.link, S.name AS source, 
	J2.id as id2, J2.localisation as localisation2, J2.salary as salary2, J2.searched as searched2, SJ2.link as link2
	FROM JOBS AS J 
	LEFT JOIN  JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id
	LEFT JOIN SOURCES_has_JOBS AS SJ ON SJ.JOBS_id = J.id
	LEFT JOIN SOURCES AS S ON S.id = SJ.SOURCES_id
	LEFT JOIN JOBS as J2 ON J.COMPANIES_id = J2.COMPANIES_id AND J.name = J2.name AND J.id <> J2.id 
	LEFT JOIN SOURCES_has_JOBS AS SJ2 ON SJ2.JOBS_id = J2.id
	WHERE J2.id is not null
	ORDER BY C.name, J.name, S.name
	");

	//JhF.id IS NULL

} else if (isset($_GET['min'])) {
	$listJ = select($connection, "SELECT 
	J.id, C.name as companie, J.name, localisation, salary, salaryMin, salaryMax, candidats, filtered, postulated, refused, searched, 
	group_concat(link SEPARATOR '|') AS link,group_concat(S.name SEPARATOR '<br>') AS source
	FROM JOBS AS J 
	LEFT JOIN  JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id 
	LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id 
	LEFT JOIN SOURCES AS S ON S.id = ShJ.SOURCES_id 
	WHERE JhF.id IS NULL and expired IS NULL and filtered is null and filtered2 is null and selected is null and salaryMin > ".$_GET['min']."000 OR salaryMax > ".$_GET['min']."000
	GROUP BY J.id
	ORDER BY C.name, J.name
	");
} else {
	$listJ = select($connection, "SELECT 
	J.id, C.name as companie, J.name, localisation, salary, salaryMin, salaryMax, candidats, filtered, postulated, refused, searched, 
	group_concat(link SEPARATOR '|') AS link,group_concat(S.name SEPARATOR '<br>') AS source
	FROM JOBS AS J 
	LEFT JOIN  JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id
	LEFT JOIN COMPANIES AS C ON C.id = J.COMPANIES_id 
	LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id 
	LEFT JOIN SOURCES AS S ON S.id = ShJ.SOURCES_id 
	WHERE JhF.id IS NULL and expired IS NULL and filtered is null and filtered2 is null and selected is null
	GROUP BY J.id
	ORDER BY C.name, J.name
	");
}

$typeJ = select($connection, "SELECT id, value FROM FILTERS WHERE safe=0 ORDER BY value");
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

<body id="<?php echo $bodyClass; ?>">
	<div class="pure-g">
		<div class="pure-u-1 list">
			<nav class="pure-menu pure-form pure-menu-horizontal">
				<ul class="pure-menu-list">
					<li class="pure-menu-item pure-menu-has-children pure-menu-allow-hover">
						<a href="#" class="pure-menu-link">Menu</a>
						<ul class="pure-menu-children">
							<li class="pure-menu-item">
								<a href="/" class="pure-menu-link">list</a>
							</li>
							<li class="pure-menu-item">
								<a href="/?s=1" class="pure-menu-link">selected list</a>
							</li>
							<li class="pure-menu-item">
								<a href="/?f2=1" class="pure-menu-link">filtered2 list</a>
							</li>
							<li class="pure-menu-item">
								<a href="/tag.php" class="pure-menu-link">tags</a>
							</li>
							<li class="pure-menu-item">
								<a href="/tag.php?p=2" class="pure-menu-link">tags 2</a>
							</li>
							<li class="pure-menu-item">
								<a href="/societe.php" class="pure-menu-link">societe</a>
							</li>
							<li class="pure-menu-item pure-menu-has-children vertical pure-menu-allow-hover">
								<a href="#" class="pure-menu-link">ajax</a>
								<ul class="pure-menu-children">
									<li class="pure-menu-item">
										<a target="_blank" href="/ajax.php?action=ReRunFilters" class="pure-menu-link">ReRunFilters</a>
									</li>
									<li class="pure-menu-item">
										<a target="_blank" href="/ajax.php?action=dedoubleSociete" class="pure-menu-link">dedoubleSociete</a>
									</li>
									<li class="pure-menu-item">
										<a target="_blank" href="/ajax.php?action=correctLink" class="pure-menu-link">correctLink</a>
									</li>
									<li class="pure-menu-item">
										<a target="_blank" href="/ajax.php?action=dedoubleLink" class="pure-menu-link">dedoubleLink</a>
									</li>
								</ul>
							</li>
						</ul>
					</li>
				</ul>
				
				<select name="typeJ" id="typeJ">
					<option></option>
					<?php
					if (isset($typeJ)) {
						foreach ($typeJ as $value) {
							echo '<option value="' . $value['id'] . '" ' . ($value['id'] == $type ? 'selected' : '') . '>' . $value['value'] . '</option>';
						}
					}
					?>
				</select>
				<label for="newFilter">Add filter</label>	
				<input type="text" name="newFilter" id="newFilter" class="">
				<button id="createNewFilter" class="pure-button pure-button-primary">create</button>

				nbr offres : <?php echo count($listJ); ?>



				<ul id="hideCols" class="pure-menu-list pure-form">
					<li class="pure-menu-item pure-menu-has-children pure-menu-allow-hover">
						<a href="#" class="pure-menu-link">Hide Cols</a>
						<ul class="pure-menu-children">
							<?php
							$cols = array('id', 'companie', 'name', 'source', 'localisation', 'salary', 'salaryMin', 'salaryMax', 'candidats', 'filtered', 'postulated', 'refused', 'searched', 'link');
							$i = 1;
							foreach ($cols as $col) {
								echo '<li class="pure-menu-item"><input type="checkbox" name="hide-' . $col . '" id="hide-' . $col . '" data-col="' . $i . '" value="1"><label for="hide-' . $col . '">' . $col . '</label></li>';
								$i++;
							}
							?>
						</ul>
					</li>
				</ul>
				<span id="responseAjax"></span>
				<!--
				<div style="text-align: right;display: inline-block;width: calc(100% - 855px);height: 30px">
				<input type="checkbox" name="viewed_all" id="viewed_all" style="top: 9px;right: 9px;position: relative;">
				</div>
				-->
			</nav>
			<div id="scrollArea" class="clusterize-scroll">
				<table class="pure-table pure-table-striped">
					<thead>
						<tr>
							<th>id</th>
							<th>companie</th>
							<th>name</th>
							<th>source</th>
							<th>localisation</th>
							<th>salary</th>
							<th>Min</th>
							<th>Max</th>
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
						$listed = array();
						foreach ($listJ as $j) {
							$sArray = explode('<br>', $j['source']);
							if (isset($_GET['m']) && in_array($j['id'], $listed)) {
								continue;
							}
							$linkE = explode('|', $j['link']);
							if (count($linkE) > 1) {
								if (count($linkE) > 2) {
									$j['link3'] = $linkE[2];
								}
								$j['link2'] = $linkE[1];
								$j['link'] = $linkE[0];
							}
							echo '<tr id="row-' . $j['id'] . '">
								<td>' . $j['id'] . '</td>
								<td>' . $j['companie'] . '</td>
								<td>' . (isset($_GET['w']) ? str_ireplace($_GET['w'], '<span style="background-color:gold;">' . $_GET['w'] . '</span>', $j['name']) : $j['name']) . '</td>
								<td>' . $j['source'] . '</td>
								<td>' . $j['localisation'] . (isset($_GET['m']) && !empty($j['localisation2']) && $j['localisation'] != $j['localisation2'] ? '<br>' . $j['localisation2'] : '') . '</td>
								<td>' . $j['salary'] . (isset($_GET['m']) && !empty($j['salary2']) && $j['salary'] != $j['salary2'] ? '<br>' . $j['salary2'] : '') . '</td>
								<td>' . $j['salaryMin'] . '</td>
								<td>' . $j['salaryMax'] . '</td>
								<td>' . $j['candidats'] . '</td>
								<td>' . (!empty($j['filtered']) ? $j['filtered'] : '<button class="banJob pure-button button-error" data-jobid="' . $j['id'] . '">filter</button>').' <button class="banJob2 pure-button button-warning" data-jobid="' . $j['id'] . '">filter2</button> <button class="selectJob pure-button button-success" data-jobid="' . $j['id'] . '">select</button></td>
								<td>' . $j['postulated'] . (empty( $j['postulated'])?'<button class="candidatedJob pure-button button-success" data-jobid="' . $j['id'] . '">candidated</button>':'') . '</td>
								<td>' . $j['refused'] . '</td>
								<td>' . $j['searched'] . (isset($_GET['m']) && !empty($j['searched2']) && $j['searched'] != $j['searched2'] ? '<br>' . $j['searched2'] : '') . '</td>
								<td><a target="_blank" href="' . $j['link'] . '" class="pure-menu-link">'.$sArray[0].'</a>
								' . (isset($j['link2']) && !empty($j['link2']) && $j['link'] != $j['link2'] ? '<br><a target="_blank" href="' . $j['link2'] . '" class="pure-menu-link">'.$sArray[1].'</a>' : '') . '
								' . (isset($j['link3']) && !empty($j['link3']) && $j['link'] != $j['link3'] ? '<br><a target="_blank" href="' . $j['link3'] . '" class="pure-menu-link">'.$sArray[2].'</a>' : '') . '</td>
								</tr>';
							if (isset($_GET['m'])) {
								$listed[] = $j['id2'];
							}


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