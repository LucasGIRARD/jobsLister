$(document).ready(function () {
	/*
	var clusterize = new Clusterize({
		scrollId: 'scrollArea',
		contentId: 'contentArea'
	});
	*/

	var column_names = [];
	var clicks = {};
	var data = [];

	var table = d3.select("table");

	var headers = d3.select("table thead").selectAll("th");

	headers.each(function (d, i) {
		var cell = d3.select(this);
		column_names[i] = cell.text();
		clicks[column_names[i]] = 0;
	});

	headers.data(column_names);


	var rows = table.select("tbody").selectAll("tr");

	rows.each(function () {
		var row = d3.select(this);
		var rowData = {};
		row.selectAll("td").each(function (d, i) {
			var cell = d3.select(this);
			rowData[column_names[i]] = cell.text();
		});
		data.push(rowData);
	});

	/*  data bind */
	rows.data(data);

	/**  search functionality **//* 
	d3.select("#newFilter").on("keyup", function () {
		var searched_data = data,
			text = this.value.trim();
		if (text == "") {
			d3.select("tr.hideRow").classed("hideRow", false);
		} else {
			var searchResults = searched_data.map(function (r) {
				value = r.name;

				var regex = new RegExp("^" + text + ".*", "i");
				if (!regex.test(value)) {
					d3.select("#row-" + r.id).classed("hideRow", true);
				} else {
					d3.select("#row-" + r.id).classed("hideRow", false);
				}


			})
		}
	}) */


	// 	/**  sort functionality **/
	function sortAlphabetically(rows, key, ascending) {
		rows.sort(function (a, b) {
			if (a[key].toUpperCase() < b[key].toUpperCase()) {
				return ascending ? -1 : 1;
			} else if (a[key].toUpperCase() > b[key].toUpperCase()) {
				return ascending ? 1 : -1;
			} else {
				return 0;
			}
		});
	}

	function sortNumerically(rows, key, ascending) {
		rows.sort(function (a, b) {
			if (+a[key] < +b[key]) {
				return ascending ? -1 : 1;
			} else if (+a[key] > +b[key]) {
				return ascending ? 1 : -1;
			} else {
				return 0;
			}
		});
	}

	function sortByDate(rows, key, ascending) {
		rows.sort(function (a, b) {
			var dateA = new Date(a[key]);
			var dateB = new Date(b[key]);
			if (dateA < dateB) {
				return ascending ? -1 : 1;
			} else if (dateA > dateB) {
				return ascending ? 1 : -1;
			} else {
				return 0;
			}
		});
	}

	headers.on("click", function (d) {
		clicks[d]++;
		if (d == "number" || d == "Min" || d == "Max") {
			sortNumerically(rows, d, clicks[d] % 2 == 0);
		} else if (d == "date") {
			sortByDate(rows, d, clicks[d] % 2 == 0);
		} else {
			sortAlphabetically(rows, d, clicks[d] % 2 == 0);
		}
	});


	var chksHide = document.querySelectorAll('#hideCols input[type="checkbox"]');
	if (chksHide.length > 0) {
		chksHide.forEach(function (element) {
			element.addEventListener('click', function () {
				var col = element.dataset.col;
				var table = document.querySelectorAll('table th:nth-child(' + col + '),table td:nth-child(' + col + ')');
				if (element.checked) {
					table.forEach(function (i) {
						i.style.display = "none";
					});
				} else {
					table.forEach(function (i) {
						i.style.display = "table-cell";
					});
				}
			}, false);
		});
	}

	var btnNewFilter = document.getElementById('createNewFilter');
	if (btnNewFilter != null) {
		btnNewFilter.addEventListener('click', function () {
			var filter = document.getElementById("newFilter").value;
			var httpRequest = new XMLHttpRequest();
			httpRequest.onreadystatechange = function (data) {
				if (httpRequest.readyState == '4' && httpRequest.status == '200') {
					document.getElementById("newFilter").value = "";
				}
			};
			httpRequest.open("GET", "ajax.php?action=insertFilter&filter=" + filter);
			httpRequest.send();
		}, false);
	}
	var btnReRunFilters = document.getElementById('reRunFilters');
	if (btnReRunFilters != null) {
		btnReRunFilters.addEventListener('click', function () {
			var httpRequest = new XMLHttpRequest();
			httpRequest.onreadystatechange = function (data) {
				if (httpRequest.readyState == '4' && httpRequest.status == '200') {
					alert('ReRunFilters fini');
				}
			};
			httpRequest.open("GET", "ajax.php?action=ReRunFilters");
			httpRequest.send();
		}, false);
	}

	var btnsBanWord = document.querySelectorAll('.banWord');
	if (btnsBanWord.length > 0) {
		btnsBanWord.forEach(function (element) {
			element.addEventListener('click', function () {
				var filter = element.dataset.word;
				var httpRequest = new XMLHttpRequest();
				httpRequest.onreadystatechange = function (data) {
					if (httpRequest.readyState == '4' && httpRequest.status == '200') {
						element.parentElement.parentElement.remove();
					}
				};
				httpRequest.open("GET", "ajax.php?action=insertFilter&filter=" + filter);
				httpRequest.send();
			}, false);
		});
	}

	var btnsJob = document.querySelectorAll('.banJob, .banJob2, .selectJob, .candidatedJob, .refusedJob');
	if (btnsJob.length > 0) {
		btnsJob.forEach(function (element) {
			element.addEventListener('click', function () {
				var jobId = element.dataset.jobid;
				var httpRequest = new XMLHttpRequest();
				var candidated = false;
				var action = "";
				if (element.classList.contains('candidatedJob')) {
					candidated = true;
					action = "candidated";
				} else if (element.classList.contains('refusedJob')) {
					action = "refused";
				} else if (element.classList.contains('banJob')) {
					action = "filterJob";
				} else if (element.classList.contains('banJob2')) {
					action = "filterJob2";
				} else if (element.classList.contains('selectJob')) {
					action = "selectJob";
				} else {
					return false;
				}
				httpRequest.onreadystatechange = function (data) {
					if (httpRequest.readyState == '4' && httpRequest.status == '200') {
						if (candidated) {
							element.remove();
						} else {
							element.parentElement.parentElement.remove();
						}

					}
				};
				httpRequest.open("GET", "ajax.php?action=" + action + "&jobId=" + jobId);
				httpRequest.send();
			}, false);
		});
	}

	var btnsJob = document.querySelectorAll('.expiredJob');
	if (btnsJob.length > 0) {
		btnsJob.forEach(function (element) {
			element.addEventListener('click', function () {
				var ShJId = element.dataset.shjid;
				var httpRequest = new XMLHttpRequest();
				var candidated = false;
				var action = "";
				if (element.classList.contains('expiredJob')) {
					candidated = false;
					action = "expired";
				} else {
					return false;
				}
				httpRequest.onreadystatechange = function (data) {
					if (httpRequest.readyState == '4' && httpRequest.status == '200') {
						if (candidated) {
							element.remove();
						} else {
							element.parentElement.parentElement.remove();
						}

					}
				};
				httpRequest.open("GET", "ajax.php?action=" + action + "&ShJId=" + ShJId);
				httpRequest.send();
			}, false);
		});
	}

	var btnsBanWord = document.querySelectorAll('.safeWord');
	if (btnsBanWord.length > 0) {
		btnsBanWord.forEach(function (element) {
			element.addEventListener('click', function () {
				var filter = element.dataset.word;
				var httpRequest = new XMLHttpRequest();
				httpRequest.onreadystatechange = function (data) {
					if (httpRequest.readyState == '4' && httpRequest.status == '200') {
						element.parentElement.parentElement.remove();
					}
				};
				httpRequest.open("GET", "ajax.php?action=insertSafeFilter&filter=" + filter);
				httpRequest.send();
			}, false);
		});
	}
	var btnsBanWord = document.querySelectorAll('.esnRadio');
	if (btnsBanWord.length > 0) {
		btnsBanWord.forEach(function (element) {
			element.addEventListener('change', function () {
				var id = element.id.split('-')[2];
				var esn = element.value;
				var httpRequest = new XMLHttpRequest();
				httpRequest.onreadystatechange = function (data) {
					if (httpRequest.readyState == '4' && httpRequest.status == '200') {

					}
				};
				httpRequest.open("GET", "ajax.php?action=esnSociete&id=" + id + "&esn=" + esn);
				httpRequest.send();
			}, false);
		});
	}

	if (document.querySelector('#scrollArea') != null && document.querySelector("body").id != "s") {
		var actualHash = location.hash;
		var bottomMargin = document.querySelector("#scrollArea").offsetHeight - 36 - 150;
		var options = {
			root: document.querySelector("#scrollArea"),
			rootMargin: "0px 0px -" + bottomMargin + "px 0px", //(top, right, bottom, left)
			threshold: 1.0,
		};

		var callback = function (entries, observer) {
			entries.forEach((entry) => {
				if (entry.isIntersecting) {
					actualHash = entry.target.id;
				}
			});
		};

		var observer = new IntersectionObserver(callback, options);

		document.querySelectorAll("#scrollArea tbody tr").forEach((i) => {
			if (i) {
				observer.observe(i);
			}
		});

		setHashTimeout = '';

		document.querySelector('#scrollArea').addEventListener('scrollend', function () {
			setHashTimeout = setTimeout(function () {
				location.hash = actualHash;
			}, 1000);
		});
	}


	if (document.querySelector('#typeJ') != null) {
		document.querySelector('#typeJ').addEventListener('change', function () {
			location.href = location.origin + location.pathname + '?type=' + this.value;
		}, false);
	}


	var selectsCompanyType = document.querySelectorAll('.setCompanyType');
	if (selectsCompanyType != null) {
		selectsCompanyType.forEach(function (element) {
			element.addEventListener('change', function () {
				var that = this;
				var id = element.id.split('-')[1];
				var httpRequest = new XMLHttpRequest();
				httpRequest.onreadystatechange = function (data) {
					if (httpRequest.readyState == '4' && httpRequest.status == '200') {
						document.getElementById("R-Ajax-" + id).innerHTML = "OK";
					}
				};
				httpRequest.open("GET", "ajax.php?action=updateCompanyType&type=" + element.value + "&id=" + id);
				httpRequest.send();
			}, false);
		});
	}
	if (document.querySelectorAll('#hideCols input[type="checkbox"]').length > 0) {
		document.querySelectorAll('#hideCols input[type="checkbox"]')[0].click();
		//document.querySelectorAll('#hideCols input[type="checkbox"]')[1].click();
		document.querySelectorAll('#hideCols input[type="checkbox"]')[4].click();
		document.querySelectorAll('#hideCols input[type="checkbox"]')[5].click();
		document.querySelectorAll('#hideCols input[type="checkbox"]')[6].click();
		document.querySelectorAll('#hideCols input[type="checkbox"]')[9].click();
		if (document.querySelector("body").id != "s" && document.querySelector("body").id != "p") {
			document.querySelectorAll('#hideCols input[type="checkbox"]')[11].click();
			document.querySelectorAll('#hideCols input[type="checkbox"]')[12].click();
		}
		document.querySelectorAll('#hideCols input[type="checkbox"]')[13].click();
		//document.querySelector('table tr th:nth-child(9)').click();
		document.querySelector('table tr th:nth-child(2)').click();
		document.querySelector('table tr th:nth-child(2)').click();
	}
});