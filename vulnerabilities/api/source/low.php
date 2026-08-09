<?php

/*
This level is about API versioning: an old version of an endpoint kept alive
alongside the current one. The fix for that lives in the API itself - the user
endpoints no longer expose password hashes at any version - so this file only
has to render the level's own page again.

Two sinks from the original page are deliberately not reproduced:

  * the request URI was interpolated straight into the inline script, which is a
    reflected XSS sink. The endpoint sits directly under this page, so a plain
    relative URL is used instead and nothing from the request is reflected.
  * table headers and cells were written with innerHTML, so anything the API
    returned was parsed as markup. They are written with textContent instead.
*/

$html .= "
<p>
	Versioning is important in APIs, running multiple versions of an API can allow for backward compatibility and can allow new services to be added without affecting existing users. The downside to keeping old versions alive is when those older versions contain vulnerabilities.
</p>
";

$html .= "
<script>
	function loadTableData(items) {
		if (!Array.isArray(items) || items.length === 0) {
			return;
		}

		const row = document.getElementById('tableHead');

		Object.keys(items[0]).forEach(function (k) {
			const cell = document.createElement('th');
			cell.textContent = k;
			row.appendChild(cell);
			if (k == 'password') {
				document.getElementById('message').style.display = 'block';
			}
		});

		const tableBody = document.getElementById('tableBody');

		items.forEach(function (item) {
			const bodyRow = tableBody.insertRow();
			Object.keys(item).forEach(function (key) {
				const cell = bodyRow.insertCell(-1);
				cell.textContent = item[key];
			});
		});
	}

	function get_users() {
		fetch('v2/user/', { method: 'GET' })
			.then(function (response) {
				if (!response.ok) {
					throw new Error('Network response was not ok');
				}
				return response.json();
			})
			.then(function (data) {
				loadTableData(data);
			})
			.catch(function (error) {
				console.error('There was a problem with your fetch operation:', error);
			});
	}
</script>
";

$html .= "
<table id='table' class=''>
  <thead>
    <tr id='tableHead'>
    </tr>
  </thead>
  <tbody id='tableBody'></tbody>
</table>


		<p>
			Look at the call used to create this table and see if you can exploit it to return some additional information.
		</p>
		<div class='success' style='display:none' id='message'>Well done, you found the password hashes.</div>
		<script>
			get_users();
		</script>
";

?>
