<?php
	session_start();

    //定数
    const LOGFILE = './imglog.csv';
    const PATH = './img/';
    const MAX_KB = '100';
    const MAX_W = '250';
    const MAX_H = '250';

    const PAGE_DEF = '7';
    const LOG_MAX = '200';

    const ADMIN_PASS ='0123';
    const CHECK = 1;
    const SOON_ICON = 'soon.jpg';

    const BUNRI = 0;

    //変数
    //POST内容
    $name = '';
    $email = '';
    $sub = '';
    $com = '';
    $url = '';
    $upfile = '';
	$pwd = '';
	$pass = '';
    //エラーメッセージ
    $err = [];
    //ログファイル全件
	$lines = file(LOGFILE);
	//データの総件数
	$lines_num = count($lines);
	//トータルページ数
	$max_page = ceil($lines_num / PAGE_DEF);
	//ゲットパラメータのページ
	$page = $_GET['page'];


	//ページ表示の処理
	//GETパラメータから表示するページを取得
	if(!isset($page)) {
		$now = 1;
	} else {
		$now = $page;
	}
	//何件目から表示させるか
	$start_no = ($now - 1) * PAGE_DEF;
	//1ページ分のデータを取得
	$show_data = array_slice($lines, $start_no, PAGE_DEF, true);


	//POST送信(login)があった場合
	if(!empty($_POST['login'])) {

		$pass = $_POST['pass'];

		//バリデーション
		if($pass === '' || ctype_space($pass)) {
			$err['pass'] = 'パスワードが入力されていません';
		} else if(!preg_match("/^[0-9]+$/", $pass )) {
			$err['pass'] = '半角数字で入力してください';
		} else if($pass !== ADMIN_PASS) {
			$err['pass'] = 'パスワードが違います';
		} else {
			$_SESSION['admin'] = true;
		}
	}

	//POST送信(admin)があった場合
	if(!empty($_POST['admin'])) {
		session_destroy();
		header('Location:'.$_SERVER['PHP_SELF']);
	}

?>

<!DOCTYPE html>
<html lang="ja">

<head>
	<meta charset="utf-8">
	<meta name="viewport" content="width=1000">
	<meta name="format-detection" content="telephone=no">
	<meta name="apple-mobile-web-app-title" content="画像BBS">
	<meta name="keywords" content="画像BBS">
	<meta name="description" content="画像BBS">
	<title>画像BBS</title>
	<link rel="stylesheet" href="css/ress.css">
	<link rel="stylesheet" href="css/style.css">
</head>

<body class="l-body">
	<div class="l-contents">
		<div class="contents">
			<div class="block block-right">
				<ul class="list list-inlineFlex">
					<li class="list_item">
						<p>[<a href="http://php.s3.to/">ホーム</a>]</p>
					</li>
					<li class="list_item">
						<p>[<a href="./admin.php">管理用</a>]</p>
					</li>
				</ul>
			</div>
			<div class="title">
				<h1 class="title_text"><a class="title_text_anchor" href="/">画像BBS</a></h1>
			</div>
			<?php if($_SESSION['admin']) { ?>
			<div class="block block-spaceL">
				<div class="title title-borderNone">
					<h2 class="title_text title_text-sizeL">管理者モード</h2>
				</div>
				<?php if(!empty($lines)) { foreach($show_data as $index => $line) {
					list($name, $email, $sub, $com, $url, $upfile, $pwd, $created_at) = explode(',', $line); ?>
				<div class="block block-article">
					<div class="block_body block_body-flexAlignCenter">
						<div class="textBox textBox-num">
							<p class="textBox_text textBox_text-left">NO.<?php echo $index+1 ?></p>
						</div>
						<div class="title title-borderNone">
							<h2 class="title_text title_text-color title_text-sizeL"><?php echo $sub?></h2>
						</div>
					</div>
					<div class="block_body block_body-flexAlignCenter">
						<dl class="textBox textBox-flexAlignCenter">
							<dt class="textBox_text textBox_text-spaceRightS">Name</dt>
							<dd class="textBox_text">
								<a <?php if(!empty($email)){ ?>href="mailto:<?php echo $email; ?>" <?php } ?> class="textBox_text textBox_text-color2"><?php echo $name?></a>
							</dd>
						</dl>
						<dl class="textBox textBox-flexAlignCenter">
							<dt class="textBox_text textBox_text-spaceRightS">Date</dt>
							<dd class="textBox_text"><?php echo $created_at ?></dd>
						</dl>
						<dl class="textBox textBox-flexAlignCenter">
							<dt class="textBox_text textBox_text-spaceRightS">URL</dt>
							<dd class="textBox_text">
								<?php if($url === 'none'){ ?>
								<a><?php echo $url; ?></a>
								<?php }else{ ?>
								<a href="<?php echo $url; ?>"><?php echo $url; ?></a>
								<?php } ?>
							</dd>
						</dl>
					</div>
					<?php if(!empty($upfile)) { list($width, $height) = imageCustom($upfile) ?>
					<div class="block_body block_body-spaceS">
						<img src="<?php echo $upfile ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" alt="">
					</div>
					<?php } ?>
					<div class="textBox">
						<p class="textBox_text textBox_text-wrap"><?php echo $com ?></p>
					</div>
				</div>
				<?php }} ?>
				<div class="pagination">
					<div class="pagination_list">
						<?php if($now == 1) {?>
						<a class="pagination_list_item">&lt;</a>
						<?php } else { ?>
						<a href="/admin.php?page=<?php echo $page-1; ?>" class="pagination_list_item">&lt;</a>
						<?php } ?>
					</div>
					<ol class="pagination_list">
						<?php for($i = 1; $i <= $max_page; $i++) { if($i == $now) { ?>
						<li class="pagination_list_item"><a><?php echo $i; ?></a></li>
						<?php } else { ?>
						<li class="pagination_list_item"><a href="/admin.php?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
						<?php }} ?>
					</ol>
					<div class="pagination_list">
						<?php if($now == $max_page) {?>
						<a class="pagination_list_item">&gt;</a>
						<?php } else { ?>
						<a href="/admin.php?page=<?php echo $page+1; ?>" class="pagination_list_item pagination_list_item-last">&gt;</a>
						<?php } ?>
					</div>
				</div>
			</div>
			<div class="block block-right block-border block-spaceL">
				<div class="block block-spaceS">
					<form method="post" action="#" class="form form-admin" name="delete" novalidate="novalidate">
						<table class="table table-delete">
							<thead>
								<tr>
									<th colspan="5" class="table_title table_title-delete">【記事削除】</th>
								</tr>
							</thead>
							<tbody>
								<tr>
									<th class="table_title table_title-delete">記事No</th>
									<td class="table_data">
										<input type="text" name="no" size="3" class="input input-delete <?php if(!empty($err['no'])) echo 'active'; ?>" value="<?php if(!empty($err)) echo $no; ?>">
									</td>
									<th class="table_title table_title-delete">削除キー</th>
									<td class="table_data"><input type="password" name="pwd" size="8" maxlength="8" class="input input-delete <?php if(!empty($err['pwd-delete'])) echo 'active'; ?>"></td>
									<td class="table_data"><input type="submit" value="削除" name="delete" class="btn btn-delete"></td>
								</tr>
								<tr>
									<td  colspan="5"><input type="submit" value="ログアウト" class="btn"></td>
								</tr>
							</tbody>
						</table>
						<input type="hidden" name="admin" value="admin">
					</form>
				</div>
			</div>

			<?php } else { ?>
			<div class="block block-content">
				<div class="tiiv  title-borderNone">
					<h2 class="title_text title_text-sizeM">管理者モード</h2>
				</div>
				<form action="#" method="post" class="form form-login">
					<input type="hidden" name="login" value="login">
					<input type="password" class="input input-width100" name="pass" maxlength="8" placeholder="パスワード">
					<?php if(!empty($err['pass'])) {?>
					<span class="errText"><?php echo $err['pass']; ?></span>
					<?php } ?>
					<input type="submit" class="btn btn-spaceS">
				</form>
			</div>
			<?php } ?>

			<footer class="l-footer">
				<div class="textBox textBox-inlineBlock">
					<p class="textBox_text">-<a href="http://php.s3.to">GazouBBS </a>-</p>
				</div>
			</footer>
		</div>
	</div>
</body>

</html>