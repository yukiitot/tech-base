<?php
//例外処理
try {
	//データベース接続
    $dsn = '*';
    $user = '*';
    $password = '*';
    $pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

/*	
//table作成
    $sql = "CREATE TABLE IF NOT EXISTS mission5_1"
    . "("
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment VARCHAR(100),"
    . "date CHAR(100),"
    . "password INT(100)"
    . ")";
    $stmt = $pdo->query($sql);
    */
// POSTデータ処理開始
	if ($_POST) {
	//新規投稿機能
		$submit = filter_input(INPUT_POST, 'submit');
		if ($submit === '投稿') {
			$postname = filter_input(INPUT_POST, 'name');
			$comment = filter_input(INPUT_POST, 'comment');
			$password = filter_input(INPUT_POST, 'password');
			//?に$postname,$comment,$passwordの値を入れていく
			$stmt = $pdo->prepare('insert into mission5_1 (name, comment, password) values (?, ?, ?)');
			$stmt->execute([$postname, $comment, $password]);
			//insertされた行数を返し、動作確認
			$msg = $stmt->rowCount() ? '追加しました' : '追加できませんでした';
		}
		//削除機能
		if ($submit === '削除') {
			$dele = filter_input(INPUT_POST, 'dele');
			$dpass = filter_input(INPUT_POST, 'dpass');
			//?に$dele,$dpassの値を入れていく
			$stmt = $pdo->prepare('delete from mission5_1 where id=? and password=?');
			$stmt->execute([$dele, $dpass]);
			//insertされた行数を返し、動作確認
			$msg = $stmt->rowCount() ? '削除しました' : 'パスワードが違います';
		}
		//編集機能
		if ($submit === '編集') {
			$edit = filter_input(INPUT_POST, 'edit');
			$epass = filter_input(INPUT_POST, 'epass');
			//?に$edit,$epassの値を入れていく
			$stmt = $pdo->prepare('select * from mission5_1 where id=? and password=?');
			$stmt->execute([$edit, $epass]);
			//fetchでデータベースからデータを取り出す
			if ($row = $stmt->fetch()) {
				$postname = $row['name'];
				$comment = $row['comment'];
			//fetchでデータを取り出せなかったら$editの中身を削除して文章表示
			} else {
				unset($edit);
				$msg = '番号またはパスワードが誤っています';
			}
		}
		//更新実行
		if ($submit === '更新') {
			$upd = filter_input(INPUT_POST, 'upd');
			$postname = filter_input(INPUT_POST, 'name');
			$comment = filter_input(INPUT_POST, 'comment');
			//?に$postname,$comment,$updの値を入れていく
			$stmt = $pdo->prepare('update mission5_1 set name=?, comment=? where id=?');
			$stmt->execute([$postname, $comment, $upd]);
			//updateされた行数を返し、動作確認
			$msg = $stmt->rowCount() ? '更新しました' : '更新できませんでした';
			//$postname,$commentの中身を削除
			unset($postname);
			unset($comment);
		}
	// POSTデータ処理終了
	}

	// 一覧表示の準備
	$bbs = [];
	foreach ($pdo->query('select * from mission5_1 order by id asc') as $row) 
		$bbs[] = sprintf('<div>No.%d（%s）<br>名前：%s<p>%s</p></div><hr>', $row['id'], $row['date'], hesc($row['name']), hesc($row['comment']));

} catch (PDOException $e) {
    xdie($e->getMessage());
}
?>
<!DOCTYPE html>
<html lang="ja">
  <head>
    <meta charset="UTF-8">
    <title>mission_5-1</title>
    <style>
      form {
        margin-bottom: 20px;
      }
    </style>
  </head>
<body>
<?= isset($msg) ? '<div style="color:red">' . $msg  . '</div>' : '' ?>
<form action="" method="post">
<?php
if (isset($edit)) {
	echo '<h3>更新</h3>';
	printf('<input type="hidden" name="upd" value="%d">', $edit);
} else {
	echo '<h3>新規投稿</h3>';
}
?>
<input type="text" name="name" placeholder="名前" value="<?= !empty($postname) ? $postname : '' ?>"><br>
<input type="text" name="comment" placeholder="コメント" value="<?= !empty($comment)  ? $comment : '' ?>"><br>
<input type="<?= isset($edit) ? 'hidden' : 'text' ?>" name="password" placeholder="パスワード" value="<?= !empty($password) ? $password : '' ?>"><br>
<input type="submit" name="submit" value="<?= isset($edit) ? '更新' : '投稿' ?>">
</form>
<hr>
<form action="" method="post">
<input type="number" name="dele" placeholder="削除対象番号"><br>
<input type="text" name="dpass" placeholder="パスワード"><br>
<input type="submit" name="submit" value="削除"><br>
</form>
<hr>
<form action="" method="post">
<input type="number" name="edit" placeholder="編集対象番号"><br>
<input type="text" name="epass" placeholder="パスワード"><br>
<input type="submit" name="submit" value="編集">
<hr>
</form>
<?= implode(PHP_EOL, $bbs) ?>
</body>
</html>

<?php
// functions
function hesc($str) {
	return htmlspecialchars($str, ENT_QUOTES);
}

function xdie($msg) {
	if (headers_sent()) $msg = '<div style="color:red; font-weight: bold;">' . htmlspecialchars($msg, ENT_QUOTES) . '<div>';
		else header('content-type: text/plain; charset=utf-8');
	die($msg);
}
?>