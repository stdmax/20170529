<!DOCTYPE html>
<html lang="ru">
<head>
	<title><?php echo htmlspecialchars($title) ?></title>
	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<script src="<?= $url ?>main.js"></script>
	<style>
		body {
			background: #fff;
			padding: 20px 50px;
		}
		thead td {
			cursor: pointer;
		}
		thead td.active {
			color: #ff0000;
		}
	</style>
</head>
<body>

	<h1><?php echo htmlspecialchars($title) ?></h1>
	<?php echo $contents ?>

</body>
</html>