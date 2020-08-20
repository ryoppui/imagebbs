<?php

    //定数
    const LOGFILE = './imglog.csv';
    const PAGE_DEF = '7';
    const ADMIN_PASS ='0123';

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
	$lines = array_reverse(file(LOGFILE), true);
	//データの総件数
	$lines_num = count($lines);
	//トータルページ数
	$max_page = ceil($lines_num / PAGE_DEF);
	//ゲットパラメータのページ
	$page = $_GET['page'];


	//セッション
	session_start();
	//フラッシュメッセージ初期化
	$flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : array();
	unset($_SESSION['flash']);


    //関数
    //画像のサイズが規定を超えていたら縮小する
    function imageCustom($upfile) {
        if(!empty($upfile)) {
            $image_info = getimagesize($upfile);
            $width = $image_info[0];
            $height = $image_info[1];

            if($width > 250 || $height > 250) {
                $width2 = 250 / $width;
                $height2 = 250 / $height;

                ($width2 < $height2) ? $key = $width2 : $key = $height2;

                $width = $width * $key;
                $height = $height * $key;
            }
            return [$width, $height];
        }
	}

	//フラッシュメッセージ
	function flashMessage($msg) {
		global $flash;
		$_SESSION['flash'] = $msg;
		$flash = $_SESSION['flash'];
	}


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

	//POST送信(logout)があった場合
	if(!empty($_POST['admin']) && $_POST['logout']) {
		session_destroy();
		header('Location:'.$_SERVER['PHP_SELF']);

	} elseif(!empty($_POST['admin']) && $_POST['delete']) { //POST送信(delete)があった場合
		$delete_no = $_POST['delete_radio'];

		if(!isset($delete_no)) {

			$err['delete'] = '削除する記事を選択してください';

		} else {
			foreach($lines as $index => $line) {
				list(, , , , , $upfile, , ,) = explode(',', $line);

				//POSTされた記事Noと削除キーがそれぞれ一致したらログファイルから該当のデータを削除
				if($delete_no == $index) {
					$file = file(LOGFILE);
					unset($file[$index]);
					file_put_contents(LOGFILE, $file);

					//画像データの投稿があれば画像も削除
					if(!empty($upfile)) {
						unlink($upfile);
					}

				}
			}

			flashMessage('削除しました');
			header('Location:'.$_SERVER['PHP_SELF']);
		}

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
		<?php if(!empty($flash)) {?>
		<div class="flashMessage js-flash">
			<p class="flashMessage_text"><?php echo $flash; ?></p>
		</div>
		<?php } ?>
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
				<form method="post" action="#" class="form form-admin" name="delete" novalidate="novalidate">
					<div class="block block-spaceL">
						<div class="title title-borderNone">
							<h2 class="title_text title_text-sizeL">管理者モード</h2>
						</div>
						<?php if(!empty($err['delete'])) { ?>
							<span class="errText"><?php echo $err['delete']; ?></span>
						<?php }?>
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
								<div class="block_body block_body-right">
									<input type="radio" name="delete_radio" value="<?php echo $index; ?>">
								</div>
							</div>
						<?php }} ?>
						<?php if(!empty($lines)) { ?>
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
						<?php } ?>
					</div>
					<div class="block block-right block-border block-spaceL">
						<div class="block block-spaceS">
							<table class="table table-admin">
								<tbody>
									<tr>
										<td>削除したい記事のラジオボタンにチェックを入れ、<br>削除ボタンを押して下さい</td>
									</tr>
									<tr>
										<td class="table_data"><input type="submit" value="削除" name="delete" class="btn btn-admin"></td>
									</tr>
									<tr>
										<td class="table_data"><input type="submit" value="ログアウト" name="logout" class="btn btn-admin"></td>
									</tr>
								</tbody>
							</table>
							<input type="hidden" name="admin" value="admin">
						</div>
					</div>
				</form>

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
	<script src="https://code.jquery.com/jquery-3.5.1.min.js"></script>
	<script src="./js/script.js"></script>
</body>

</html>