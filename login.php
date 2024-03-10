<?php

if (isset($_POST['login'])) {
	if (!_POST['username'] || !_POST['password']) {

	} else {
		$stmt = $db->prepare("SELECT * FROM users WHERE name = ? and password = ?");
		$password = md5($_POST['password']);
		$stmt->bind_param("ss", $_POST['username'], $password);
		$stmt->execute();
		$res = $stmt->get_result();
		$user = $res->fetch_assoc();
		if ($user) {
			$_SESSION['user'] = $user;
			header("Location: /");
		} else {

		}
	}
}

if (isset($_POST['register'])) {
	if (!_POST['username'] || !_POST['password'] || $_POST['password'] != $_POST['password_confirm']) {

	} else {
		$stmt = $db->prepare("INSERT INTO users (`name`, `password`) values (?, ?)");
		$new_password = md5($_POST['password']);
		$stmt->bind_param("ss", $_POST['username'], $new_password);
		$stmt->execute();

		header("Location: /");
	}
	
}
?>
<h1>Нужен логин</h1>
<form method="POST">
	<div><input type="text" name="username"></div>
	<div><input type="password" name="password"></div>
	<input type="hidden" name="login">
	<div>
		<button type="submit">ok</button>
	</div>
</form>
<h2>Регистрация</h2>
<form method="POST">
	<div><input type="text" name="username"></div>
	<div><input type="password" name="password"></div>
	<div><input type="password" name="password_confirm"></div>
	<input type="hidden" name="register">
	<div>
		<button type="submit">ok</button>
	</div>
</form>
