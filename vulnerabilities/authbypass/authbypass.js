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

		// Build each row through the DOM API rather than concatenating a
		// string of HTML, so a stored value can never be parsed as markup
		// here regardless of what escaping (if any) happened server side.
		function makeInput (type, id, name, value) {
			var input = document.createElement('input');
			input.type = type;
			input.id = id;
			input.name = name;
			input.value = value;
			return input;
		}

		function updateTable (user) {
			var id = String(user['user_id']);

			var row = table_body.insertRow(0);
			var cell0 = row.insertCell(-1);
			cell0.appendChild(document.createTextNode(id));
			cell0.appendChild(makeInput('hidden', 'user_id_' + id, 'user_id', id));
			var cell1 = row.insertCell(1);
			cell1.appendChild(makeInput('text', 'first_name_' + id, 'first_name', user['first_name']));
			var cell2 = row.insertCell(2);
			cell2.appendChild(makeInput('text', 'surname_' + id, 'surname', user['surname']));
			var cell3 = row.insertCell(3);
			var button = document.createElement('input');
			button.type = 'button';
			button.value = 'Update';
			button.addEventListener('click', function () { submit_change(id); });
			cell3.appendChild(button);
		}
	};
	xhr.send();
}
