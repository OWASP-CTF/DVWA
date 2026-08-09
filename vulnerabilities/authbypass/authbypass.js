function show_save_result (data) {
	if (data.result == 'ok') {
		document.getElementById('save_result').innerText = 'Save Successful';
	} else {
		document.getElementById('save_result').innerText = 'Save Failed';
	}
}
	
function submit_change(id) {
	first_name = document.getElementById('first_name_' + id).value
	surname = document.getElementById('surname_' + id).value

	fetch('change_user_details.php', {
		method: 'POST',
		headers: {
			'Accept': 'application/json',
			'Content-Type': 'application/json'
		},
		body: JSON.stringify({ 'id': id, 'first_name': first_name, 'surname': surname })
	}
	)
	.then((response) => response.json())
	.then((data) => show_save_result(data));
}

function populate_form() {
	var xhr= new XMLHttpRequest();
	xhr.open('GET', 'get_user_data.php', true);
	xhr.onreadystatechange= function() {
		if (this.readyState!==4) {
			return;
		}
		if (this.status!==200) {
			return;
		}
		const users = JSON.parse (this.responseText);
		table_body = document.getElementById('user_table').getElementsByTagName('tbody')[0];
		users.forEach(updateTable);

		function buildField (type, idAttr, nameAttr, valueAttr) {
			var field = document.createElement('input');
			field.setAttribute('type', type);
			field.setAttribute('id', idAttr);
			field.setAttribute('name', nameAttr);
			field.setAttribute('value', valueAttr);
			return field;
		}

		function updateTable (user) {
			var uid = String(user['user_id']);

			var row = table_body.insertRow(0);

			var cell0 = row.insertCell(-1);
			cell0.appendChild(document.createTextNode(uid));
			cell0.appendChild(buildField('hidden', 'user_id_' + uid, 'user_id', uid));

			var cell1 = row.insertCell(1);
			cell1.appendChild(buildField('text', 'first_name_' + uid, 'first_name', user['first_name']));

			var cell2 = row.insertCell(2);
			cell2.appendChild(buildField('text', 'surname_' + uid, 'surname', user['surname']));

			var cell3 = row.insertCell(3);
			var updateBtn = document.createElement('input');
			updateBtn.setAttribute('type', 'button');
			updateBtn.setAttribute('value', 'Update');
			updateBtn.addEventListener('click', function () {
				submit_change(uid);
			});
			cell3.appendChild(updateBtn);
		}
	};
	xhr.send();
}
