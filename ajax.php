<?php
include 'SQL.php';
$connection = openSQLConnexion();

switch ($_GET['action']) {
	case 'updateCompanyType':
		$type = (empty($_GET['type']) ? NULL : $_GET['type']);
		$queryOK = insertUpdate($connection, 'UPDATE COMPANIES SET TYPES_id=? WHERE id=?', array(array($type, $_GET['id'])));
		break;
	case 'insertFilter':
		$filter = (empty($_GET['filter']) ? NULL : $_GET['filter']);
		$insertFilter = insertUpdate($connection, 'INSERT INTO FILTERS (value) VALUES (?)', array(array($filter)));

		$filterId = getLastId($connection);

		$before = array('% ', '%-', '%\'', '%/', '%,', '%(', '');
		$after = array(' %', '-%', '%\'', '/%', ',%', ')%', '');
		$filterSearch = array();
		foreach ($before as $key => $value) {
			foreach ($after as $key2 => $value2) {
				$filterSearch[] = $value . $filter . $value2;
			}
		}

		$sqlWhere = 'name like ?';
		for ($i = 1; $i < count($filterSearch); $i++) {
			$sqlWhere .= 'or name like ?';
		}


		$jobsMatching = select($connection, "SELECT id FROM JOBS WHERE $sqlWhere", $filterSearch);

		$insertJobshasFilters = array();
		foreach ($jobsMatching as $jobMatching) {
			$insertJobshasFilters[] = array($jobMatching['id'], $filterId);
		}

		$insertJobshasFilters = insertUpdate($connection, 'INSERT INTO JOBS_has_FILTERS (JOBS_id, FILTERS_id) VALUES (?,?)', $insertJobshasFilters);

		break;
	case 'insertSafeFilter':
		$filter = (empty($_GET['filter']) ? NULL : $_GET['filter']);
		$insertFilter = insertUpdate($connection, 'INSERT INTO FILTERS (value,safe) VALUES (?,1)', array(array($filter)));
		break;
	case 'ReRunFilters':
		if (!isset($_GET['i']) || empty($_GET['i'])) {
			$i = 0;
			select($connection, 'DELETE FROM JOBS_has_FILTERS WHERE id <> ""');
		} else {
			$i = $_GET['i'];
		}
		$typesJ = select($connection, "SELECT id, value FROM FILTERS WHERE safe = 0 ORDER BY value");
		$max = $i + 10;
		for ($i = $i; $i < $max; $i++) {
			if (!isset($typesJ[$i])) {
				break;
			}

			$typeJ = $typesJ[$i];
			$filter = $typeJ['value'];

			$before = array('% ', '%-', '%\'', '%/', '%,', '%(', '');
			$after = array(' %', '-%', '%\'', '/%', ',%', ')%', '');
			$filterSearch = array();
			foreach ($before as $key => $value) {
				foreach ($after as $key2 => $value2) {
					$filterSearch[] = $value . $filter . $value2;
				}
			}


			$sqlWhere = 'name like ?';
			for ($y = 1; $y < count($filterSearch); $y++) {
				$sqlWhere .= 'or name like ?';
			}

			$jobsMatching = select($connection, "SELECT id FROM JOBS WHERE $sqlWhere", $filterSearch);

			$insertJobshasFilters = array();
			foreach ($jobsMatching as $jobMatching) {
				$insertJobshasFilters[] = array($jobMatching['id'], $typeJ['id']);
			}

			$insertJobshasFilters = insertUpdate($connection, 'INSERT INTO JOBS_has_FILTERS (JOBS_id, FILTERS_id) VALUES (?,?)', $insertJobshasFilters);
		}

		//$i++;
		if (isset($typesJ[$i])) {
			echo '<meta http-equiv="Refresh" content="1; URL=/ajax.php?action=ReRunFilters&i=' . $i . '" />';
		} else {
			echo 'finished';
		}
		break;
	case 'ReRunFiltersNEW':
		if (!isset($_GET['i']) || empty($_GET['i'])) {
			$i = 0;
		} else {
			$i = $_GET['i'];
		}
		if (isset($_GET['d'])) {
			$d = $_GET['d'];
		} else {
			exit('missing d');
		}
		$typesJ = select($connection, "SELECT id, value FROM FILTERS WHERE safe = 0 ORDER BY value");
		$max = $i + 10;
		for ($i = $i; $i < $max; $i++) {
			if (!isset($typesJ[$i])) {
				break;
			}

			$typeJ = $typesJ[$i];
			$filter = $typeJ['value'];

			$before = array('% ', '%-', '%\'', '%/', '%,', '%(', '');
			$after = array(' %', '-%', '%\'', '/%', ',%', ')%', '');
			$filterSearch = array();
			foreach ($before as $key => $value) {
				foreach ($after as $key2 => $value2) {
					$filterSearch[] = $value . $filter . $value2;
				}
			}


			$sqlWhere = '(name like ?';
			for ($y = 1; $y < count($filterSearch); $y++) {
				$sqlWhere .= 'or name like ?';
			}

			$sqlWhere .= ') and imported > "' . $d . '"';

			$jobsMatching = select($connection, "SELECT id FROM JOBS WHERE $sqlWhere", $filterSearch);

			$insertJobshasFilters = array();
			foreach ($jobsMatching as $jobMatching) {
				$insertJobshasFilters[] = array($jobMatching['id'], $typeJ['id']);
			}

			$insertJobshasFilters = insertUpdate($connection, 'INSERT INTO JOBS_has_FILTERS (JOBS_id, FILTERS_id) VALUES (?,?)', $insertJobshasFilters);
		}

		//$i++;
		if (isset($typesJ[$i])) {
			echo '<meta http-equiv="Refresh" content="1; URL=/ajax.php?action=ReRunFiltersNEW&i=' . $i . '&d='.$d.'" />';
		} else {
			echo 'finished';
		}
		break;
	case 'dedoubleSociete':
		$societe = select($connection, "SELECT name, count(name) as c, GROUP_CONCAT(id) FROM COMPANIES group by name having c > 1 ORDER BY c desc");

		foreach ($societe as $value) {
			$ids = explode(',', $value['GROUP_CONCAT(id)']);
			arsort($ids);
			$first = array_shift($ids);
			foreach ($ids as $id) {
				if ($id == $first) continue;
				insertUpdate($connection, 'UPDATE JOBS SET COMPANIES_id=? WHERE COMPANIES_id=?', array(array($first, $id)));
				insertUpdate($connection, 'DELETE FROM COMPANIES WHERE id=?', array(array($id)));
			}
		}
		break;
	case 'esnSociete':
		$id = (empty($_GET['id']) ? NULL : $_GET['id']);
		$esn = (!isset($_GET['esn']) ? NULL : $_GET['esn']);

		insertUpdate($connection, 'UPDATE COMPANIES SET esn=? WHERE id=?', array(array($esn, $id)));
		break;
	case 'correctLink':
		select($connection, 'DELETE FROM SOURCES_has_JOBS WHERE link like "https://fr.indeed.com/pagead/%"');
		select($connection, 'DELETE J FROM JOBS AS J LEFT JOIN SOURCES_has_JOBS AS SJ ON J.id = SJ.JOBS_id WHERE SJ.id IS NULL AND J.id <> 0');
		$list = select($connection, "SELECT id,SOURCES_id,link FROM SOURCES_has_JOBS WHERE SOURCES_id IN (7,11,12)");

		foreach ($list as $value) {
			$link = $value['link'];
			$source = $value['SOURCES_id'];
			$id = $value['id'];

			switch ($source) {
				case 7:
				case 12:
					$correctLink = substr($link, 0, strpos($link, '?'));
					break;
				case 11:
					$correctLink = substr($link, 0, strpos($link, '&')) . '&vjs=3';
					break;
			}

			insertUpdate($connection, 'UPDATE SOURCES_has_JOBS SET link=? WHERE id=?', array(array($correctLink, $id)));
		}
		break;
	case 'dedoubleLink':
		$list = select($connection, "SELECT SJ.id as doNotDel, SJ2.id as toDel FROM SOURCES_has_JOBS AS SJ
			LEFT JOIN SOURCES_has_JOBS AS SJ2 ON SJ.link = SJ2.link and SJ.id <> SJ2.id
			WHERE SJ2.id IS NOT NULL
			ORDER BY SJ.id");

		$idsToNotDel = [];
		$idsToDel = [];
		foreach ($list as $value) {
			$idsToNotDel[] = $value['doNotDel'];
			if (!in_array($value['toDel'], $idsToNotDel)) {
				$idsToDel[] = [$value['toDel']];
			}
		}

		insertUpdate($connection, 'DELETE FROM SOURCES_has_JOBS WHERE id=?', $idsToDel);
		select($connection, 'DELETE J FROM JOBS AS J LEFT JOIN SOURCES_has_JOBS AS SJ ON J.id = SJ.JOBS_id WHERE SJ.id IS NULL AND J.id <> 0');
		break;
	case 'mergeJob':
		$jobs = select($connection, "SELECT 
		J.id as id, J.localisation as loc, J.salary as sal, J.searched as search, SJ.link as link, 
		J2.id as id2, J2.localisation as loc2, J2.salary as sal2, J2.searched as search2, SJ2.id as SJ2id, SJ2.link as link2
		FROM JOBS AS J 
		LEFT JOIN SOURCES_has_JOBS AS SJ ON SJ.JOBS_id = J.id
		LEFT JOIN JOBS as J2 ON J.COMPANIES_id = J2.COMPANIES_id AND J.name = J2.name AND J.id <> J2.id 
		LEFT JOIN SOURCES_has_JOBS AS SJ2 ON SJ2.JOBS_id = J2.id
		WHERE J2.id is not null order by J.id");

		$made = [];
		foreach ($jobs as $j) {
			if (!in_array($j['id'], $made)) {
				$made[] = $j['id'];
				$made[] = $j['id2'];

				$loc = $j['loc'];
				$sal = $j['sal'];
				$search = $j['search'];
				if (!empty($j['loc2']) && $j['loc'] != $j['loc2']) {
					$loc .= ' \n ' . $j['loc2'];
				}

				if (!empty($j['sal2']) && $j['sal'] != $j['sal2']) {
					$sal = ' \n ' . $j['sal2'];
				}

				if (!empty($j['search2']) && $j['search'] != $j['search2']) {
					$search = ' \n ' . $j['search2'];
				}

				insertUpdate($connection, 'UPDATE JOBS SET localisation=?, salary=?, searched=?, expired=NULL WHERE id=?', array(array($loc, $sal, $search, $j['id'])));
				insertUpdate($connection, 'UPDATE SOURCES_has_JOBS SET JOBS_id=? WHERE id=?', array(array($j['id'], $j['SJ2id'])));
				insertUpdate($connection, 'DELETE FROM JOBS WHERE id=?', array(array($j['id2'])));
			}
		}
		break;
	case 'filterJob':
		$jobId = (empty($_GET['jobId']) ? NULL : $_GET['jobId']);
		$deleteJob = insertUpdate($connection, 'UPDATE JOBS SET filtered=NOW() WHERE id=?', array(array($jobId)));
		break;
	case 'filterJob2':
		$jobId = (empty($_GET['jobId']) ? NULL : $_GET['jobId']);
		$deleteJob = insertUpdate($connection, 'UPDATE JOBS SET filtered2=NOW() WHERE id=?', array(array($jobId)));
		break;
	case 'selectJob':
		$jobId = (empty($_GET['jobId']) ? NULL : $_GET['jobId']);
		$deleteJob = insertUpdate($connection, 'UPDATE JOBS SET selected=NOW() WHERE id=?', array(array($jobId)));
		break;
		break;
	case 'candidated':
		$jobId = (empty($_GET['jobId']) ? NULL : $_GET['jobId']);
		$deleteJob = insertUpdate($connection, 'UPDATE JOBS SET postulated=NOW() WHERE id=?', array(array($jobId)));
		break;
	case 'refused':
		$jobId = (empty($_GET['jobId']) ? NULL : $_GET['jobId']);
		$deleteJob = insertUpdate($connection, 'UPDATE JOBS SET refused=NOW() WHERE id=?', array(array($jobId)));
		break;
	case 'expired':
		$ShJId = (empty($_GET['ShJId']) ? NULL : $_GET['ShJId']);
		$deleteJob = insertUpdate($connection, 'UPDATE SOURCES_has_JOBS SET expired=NOW() WHERE id=?', array(array($ShJId)));
		break;
	case 'salaryFix':
		$listJ = select($connection, "SELECT 
			J.id, salary
			FROM JOBS AS J 
			LEFT JOIN  JOBS_has_FILTERS AS JhF ON JhF.JOBS_id = J.id
			LEFT JOIN SOURCES_has_JOBS AS ShJ ON ShJ.JOBS_id = J.id
			WHERE JhF.id IS NULL and expired IS NULL and filtered is null and 
			salary is not null and salary != '' and salaryMax is null
			GROUP BY J.id
			ORDER BY J.name
		");

		foreach ($listJ as $value) {
			$salary = $value['salary'];
			$salary = str_replace('(Estimation de l\'employeur)', '', $salary);
			$salary = str_replace('A négocier', '', $salary);
			$salary = str_replace('\n', '', $salary);
			$salary = str_replace('/ an', '', $salary);
			$salary = str_replace('par an', '', $salary);
			$salary = str_replace('De', '', $salary);
			$salary = str_replace('brut annuel', '', $salary);
			$salary = str_replace('Jusqu’', '', $salary);
			$salary = str_replace('par mois', '', $salary);
			$salary = str_replace('€', '', $salary);
			$salary = str_replace('K', '000', $salary);
			$salary = str_replace('k', '000', $salary);
			$salary = str_replace(' ', '', $salary);
			$salary = str_replace(' ', '', $salary);
			$salary = str_replace(' ', '', $salary);
			$salary = str_replace('&nbsp;', '', strtolower($salary));
			$salary = str_replace('à', '-', $salary);
			
			if (strpos($salary, '-') !== false) {
				$salaryA = explode('-', $salary);
				$salaryMin = intval($salaryA[0]);
				$salaryMax = intval($salaryA[1]);
				$insertJobshasFilters = insertUpdate($connection, 'UPDATE JOBS SET salaryMin=?, salaryMax=? WHERE id=?', array(array($salaryMin, $salaryMax, $value['id'])));
			} else if (!empty($salary)) {
				$salary = intval($salary);
				$insertJobshasFilters = insertUpdate($connection, 'UPDATE JOBS SET salaryMax=? WHERE id=?', array(array($salary, $value['id'])));
			}
		}
		break;
	default:
		break;
}

if (isset($return) && !empty($return)) {
	echo json_encode($return);
} else {
	echo ' ';
}


closeSQLConnexion($connection);
