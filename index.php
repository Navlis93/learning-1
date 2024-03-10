<?php
session_start();
require_once __DIR__.'/dbconfig.php';
// require_once __DIR__.'/Controller.php';
// require_once __DIR__.'/ItemsRepository.php';
if (!isset($_SESSION['user'])) {
	require('login.php');
	die();
}
if (isset($_POST['logout'])) {
	unlink($_SESSION['user']);
	session_destroy();
	header("Location: /");
}
$errors = [];
if ($_SERVER["CONTENT_TYPE"] ==  'application/json') {

	$postData = file_get_contents('php://input');
	$data = json_decode($postData, true);
	if ($data[action] == 'complete') {
		$stmt = $db->prepare("SELECT * from items WHERE id = ? and user_id = ?");
		$stmt->bind_param("ii", $data['id'], $_SESSION['user']['id']);
		$stmt->execute();
		$res = $stmt->get_result();
		$row = $res->fetch_assoc();

		$status = $row['status'] == 0 ? 1 : 0;
		$stmt = $db->prepare("UPDATE items SET status = ? WHERE id = ? and user_id = ?");
		$stmt->bind_param("iii", $status, $data['id'], $_SESSION['user']['id']);
		$stmt->execute();
		echo json_encode(['result' => true]);
		die();
	}
	if ($data[action] == 'remove') {
		$stmt = $db->prepare("DELETE FROM items WHERE id = ? and user_id = ?");
		$stmt->bind_param("ii", $data['id'], $_SESSION['user']['id']);
		$stmt->execute();
		echo json_encode(['result' => true]);
		die();
	}
}

if (isset($_POST['new-element'])) {
	if ($_POST['new-element'] == '') {
		$errors['new-element'] = true;
	} else {
		$stmt = $db->prepare("INSERT INTO items (`text`, `status`, `user_id`) values (?, 0, ?)");
		$stmt->bind_param("si", $_POST['new-element'], $_SESSION['user']['id']);
		$stmt->execute();
		header("Location: /");
	}
}

?>
<!DOCTYPE html>
<html>
<head>
	<title>Demo app</title>
	<meta charset="utf-8">
	<style type="text/css">
		.text-through {
			text-decoration: line-through;
		}
		.errored {
			border-color: red;
		}
		.remove-item {
			border: 1px solid;
			border-radius: 3px;
			background: #ccc;
			color: #fff;
			padding: 3px;
			cursor: pointer;
		}
		.complete-item {
			border: 1px solid;
			border-radius: 3px;
			background: #ccc;
			color: #fff;
			padding: 3px;
			cursor: pointer;
			margin: 0 4px;
		}
	</style>
</head>
<body>
	<form method="post"><button type="submit" name="logout">logout</button></form>
	<h1>Todo-лист</h1>
	<form method="post">
		<input type="text" name="new-element" <?= $errors['new-element'] ? 'class="errored"' : '' ?>>
		<button type="submit">Добавить</button>
	</form>
	<ul>
		<?php
		
		$stmt = $db->prepare("SELECT * FROM items WHERE user_id = ?");
		$stmt->bind_param("i", $_SESSION['user']['id']);
		$stmt->execute();
		$result = $stmt->get_result();

		while($row = $result->fetch_assoc()) {
			echo '<li '. ($row['status'] == 1 ? 'class="text-through"' : '') .'>'.$row['text'].'<span class="complete-item" data-id="'.$row['id'].'">✓</span> <span class="remove-item" data-id="'.$row['id'].'">X</span></li>';
		}
		?>
	</ul>

	<script>
		document.querySelectorAll('.remove-item').forEach(function(elem) {
			elem.addEventListener('click', async function () {
				let response = await fetch('/', {
				  method: 'POST',
				  headers: {
				    'Content-Type': 'application/json'
				  },
				  body: JSON.stringify({"action": "remove", "id": event.target.closest('.remove-item').getAttribute('data-id')})
				});
				let result = await response.json();
				if (result.result) {
					window.location.reload();
				}
				
			})
		});

		document.addEventListener('click', async function(event){
			if (event.target.closest('.complete-item')) {
				let response = await fetch('/', {
				  method: 'POST',
				  headers: {
				    'Content-Type': 'application/json'
				  },
				  body: JSON.stringify({"action": "complete", "id": event.target.closest('.complete-item').getAttribute('data-id')})
				});
				let result = await response.json();
				if (result.result) {
					window.location.reload();
				}
			}
		});

	</script>
</body>
</html>

