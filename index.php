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
    //エラーメッセージ
	$err = [];
	//フラッシュメッセージ
	$flash = '';
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


    //POST送信(register)があった場合
    if(!empty($_POST['regist'])) {
        //POSTの中身をそれぞれ変数に
        $name = $_POST['name'];
        $email = $_POST['email'];
        $sub = $_POST['sub'];
        $com = $_POST['com'];
        $url = $_POST['url'];
        $upfile = $_FILES['upfile'];
        $pwd = $_POST['pwd'];

        //バリデーションチェック
        //名前の入力があるか
        if($name === '' || ctype_space($name)) {
            $err['name'] = '名前が書きこまれていません';
        }

        //本文の入力があるか
        if($com === '' || ctype_space($com)) {
            $err['com'] = '本文が書き込まれていません';
        } else if(mb_strlen($com) > 1000) { //1000文字を超えていないか
            $err['com'] = '本文が長すぎますっ！';
        }

        //画像のアップロードがある場合
        if($upfile['size'] !== 0) {

            //画像のマイムタイプをチェック
            if(exif_imagetype($upfile['tmp_name']) !== IMAGETYPE_PNG && exif_imagetype($upfile['tmp_name']) !== IMAGETYPE_JPEG &&
            exif_imagetype($upfile['tmp_name']) !== IMAGETYPE_GIF) {
                $err['upfile'] = '画像はGIF,JPG,PNGのいずれかにしてください';
            } else if($upfile['size'] > 100000 ) { //画像の容量をチェック
                $err['upfile'] = '画像サイズが100KBを超えています';
            }
        }

        //削除キーの入力があるか
        if($pwd === '' || ctype_space($pwd)) {
            $err['pwd'] = '削除キーの入力がありません';
        } else if(!preg_match("/^[0-9]+$/", $pwd )) { //半角数字になっているか
            $err['pwd'] = '半角数字で入力してください';
        }

        //バリデーションチェックをクリアした場合
        if(empty($err)) {

            //タイトルが未入力の場合は「(無題)」にする
            if($sub === '' || ctype_space($sub)) {
                $sub = '(無題)';
            }

            //コメントの改行コードを置換
            if(!empty($com)) {
                $com = str_replace(array("\r\n", "\n", "\r"), "<br>", $com);
            }

            //URLの入力がなかったら「none」にする
            if($url === 'http://' || $url === '' || ctype_space($url)) {
                $url = 'none';
            }

            //画像のアップロードがあった場合はimagesディレクトリへ保存、ログにはファイルパスを保存
            if($upfile['size'] !== 0) {
                $file_path = './images/'.$upfile['name'];
                move_uploaded_file($upfile['tmp_name'], $file_path);
            } else {
                //画像アップロードがなかった場合ログファイルには空欄で保存
                $file_path = '';
            }

            //パスワードをハッシュ化
            if(!empty($pwd)) {
                $pwd = password_hash($pwd, PASSWORD_DEFAULT);
            }

            //各入力項目を配列に格納
            $arr = array(
                "name" => $name,
                "email" => $email,
                "sub" => $sub,
                "com" => $com,
                "url" => $url,
                "upfile" => $file_path,
                "pwd" => $pwd,
                "created_at" => date("Y-m-d H:i:s")
            );

            //logファイルを開いてcsv形式で書き込む
            $fp = fopen(LOGFILE, 'a');
            fputcsv($fp, $arr);
            fclose($fp);

			flashMessage('投稿しました');
            header('Location:'.$_SERVER['PHP_SELF']);
		}


	//POST送信(delete)があった場合
    } else if(!empty($_POST['delete'])) {
		//POSTの中身をそれぞれ変数に格納
        $no = $_POST['no'];
        $pwd = $_POST['pwd'];

		//各バリデーションチェック
        if($no === '' || ctype_space($no)) {
            $err['no'] = '記事Noの入力がありません';
		} else if(!preg_match("/^[0-9]+$/", $no )) {
            $err['no'] = '記事Noは半角数字で入力してください';
		}

		if($pwd === '' || ctype_space($pwd)) {
            $err['pwd-delete'] = '削除キーの入力がありません';
		} else if(!preg_match("/^[0-9]+$/", $pwd )) {
            $err['pwd-delete'] = '削除キーは半角数字で入力してください';
		}

		//バリデーションチェックをクリアした場合
		if(empty($err)) {

			if(!empty($lines)) {

				//ログファイルの中身を一行ずつ変数へ
				foreach($lines as $index => $line) {
					list(, , , , , $upfile, $pwd_csv, ,) = explode(',', $line);

					//POSTされた記事Noと削除キーがそれぞれ一致したらログファイルから該当のデータを削除
					if($no-1 == $index && password_verify($pwd, $pwd_csv)) {
						$file = file(LOGFILE);
						unset($file[$no-1]);
						file_put_contents(LOGFILE, $file);

						//画像データの投稿があれば画像も削除
						if(!empty($upfile)) {
							unlink($upfile);
						}

						flashMessage('削除しました');
						header('Location:'.$_SERVER['PHP_SELF']);

					} else { //POSTされた記事Noと削除キーが不一致だった場合はエラーメッセージを表示
						$err['delete'] = '該当記事が見つからないかパスワードが間違っています';
					}
				}
			}

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
			<div class="block block-content">
				<form method="post" enctype="multipart/form-data" action="#" class="form" novalidate="novalidate">
					<table class="table">
						<tbody>
							<tr>
								<th class="table_title">おなまえ</th>
								<td class="table_data" colspan="3">
									<input type="text" name="name" size="28" value="<?php if(!empty($err)) echo $name; ?>" class="input <?php if(!empty($err['name'])) echo 'active'; ?>">
									<span class="errText"><?php if(!empty($err['name'])) { echo $err['name'];} ?></span>
								</td>
							</tr>
							<tr>
								<th class="table_title">Eメール</th>
								<td class="table_data" colspan="3"><input type="email" name="email" size="28" value="<?php if(!empty($err)) echo $email; ?>" class="input"></td>
							</tr>
							<tr>
								<th class="table_title">題名</th>
								<td class="table_data"><input type="text" name="sub" size="35" value="<?php if(!empty($err)) echo $sub; ?>" class="input"></td>
								<td class="table_data"><input type="submit" name="register" value="送信する" class="btn"></td>
								<td class="table_data"><input type="reset" value="リセット" class="btn"></td>
							</tr>
							<tr>
								<th class="table_title">コメント</th>
								<td colspan="3" class="table_data">
									<textarea name="com" cols="50" rows="4" wrap="soft" class="textarea <?php if(!empty($err['com'])) echo 'active'; ?>"><?php if(!empty($err)) echo $com; ?></textarea>
									<span class="errText"><?php if(!empty($err['com'])) { echo $err['com'];} ?></span>
								</td>
							</tr>
							<tr>
								<th class="table_title">U　R　L</th>
								<td colspan="3" class="table_data"><input type="url" name="url" size="63" value="http://<?php if(!empty($err)) echo str_replace('http://', '', $url); ?>" class="input"></td>
							</tr>
							<tr>
								<th class="table_title">添付File</th>
								<td colspan="3" class="table_data">
									<input type="file" name="upfile" size="35">
									<span class="errText"><?php if(!empty($err['upfile'])) { echo $err['upfile'];} ?></span>
								</td>
							</tr>
							<tr>
								<th class="table_title">削除キー</th>
								<td colspan="2" class="table_data">
									<div class="table_data_flexEnd">
										<input type="password" name="pwd" size="8" maxlength="8" class="input">
										<div class="textBox">
											<p class="textBox_text textBox_text-sizeS">(記事の削除用。英数字で8文字以内)</p>
										</div>
									</div>
									<span class="errText"><?php if(!empty($err['pwd'])) { echo $err['pwd'];} ?></span>
								</td>
							</tr>
						</tbody>
					</table>
					<ul class="list list-left">
						<li class="list_item list_item-style">
							<div class="textBox textBox-list">
								<p class="textBox_text textBox_text-sizeS">添付可能ファイル：GIF,JPG,PNG</p>
							</div>
						</li>
						<li class="list_item list_item-style">
							<div class="textBox textBox-list">
								<p class="textBox_text textBox_text-sizeS">ブラウザによっては正常に添付できないことがあります。</p>
							</div>
						</li>
						<li class="list_item list_item-style">
							<div class="textBox textBox-list">
								<p class="textBox_text textBox_text-sizeS">最大投稿データ量は 100 KB までです。</p>
							</div>
						</li>
						<li class="list_item list_item-style">
							<div class="textBox textBox-list">
								<p class="textBox_text textBox_text-sizeS">画像は横 250ピクセル、縦 250ピクセルを超えると縮小表示されます。</p>
							</div>
						</li>
					</ul>
					<input type="hidden" name="regist" value="regist">
				</form>
			</div>
			<div class="block block-spaceL">
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
						<a href="<?php echo $upfile?>" target="_blank"><img src="<?php echo $upfile ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" alt=""></a>
					</div>
					<?php } ?>
					<div class="textBox textBox-spaceM">
						<p class="textBox_text textBox_text-wrap"><?php echo $com ?></p>
					</div>
				</div>
				<?php }} ?>
				<?php if(!empty($lines)) { ?>
				<div class="pagination">
					<div class="pagination_list">
						<?php if($now == 1) {?>
							<a class="pagination_list_item">&lt;</a>
						<?php } else { ?>
							<a href="/?page=<?php echo $page-1; ?>" class="pagination_list_item">&lt;</a>
						<?php } ?>
					</div>
					<ol class="pagination_list">
						<?php for($i = 1; $i <= $max_page; $i++) { if($i == $now) { ?>
							<li class="pagination_list_item"><a><?php echo $i; ?></a></li>
						<?php } else { ?>
							<li class="pagination_list_item"><a href="/?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
						<?php }} ?>
					</ol>
					<div class="pagination_list">
						<?php if($now == $max_page) {?>
							<a class="pagination_list_item">&gt;</a>
						<?php } else { ?>
							<a href="/?page=<?php echo $page+1; ?>" class="pagination_list_item pagination_list_item-last">&gt;</a>
						<?php } ?>
					</div>
				</div>
				<?php } ?>
			</div>
			<div class="block block-right block-border block-spaceL">
				<div class="block block-spaceS">
					<form method="post" action="#" class="form form-delete" name="delete" novalidate="novalidate">
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
								<?php if(!empty($err['no'])) { ?>
									<tr>
										<td colspan="5" class="table_data table_data-center"><span class="errText"><?php echo $err['no']; ?></span></td>
									</tr>
								<?php } ?>
								<?php if(!empty($err['pwd-delete'])) { ?>
									<tr>
										<td colspan="5" class="table_data table_data-center"><span class="errText"><?php echo $err['pwd-delete']; ?></span></td>
									</tr>
								<?php } ?>
								<?php if(!empty($err['delete'])) { ?>
									<tr>
										<td colspan="5" class="table_data table_data-center"><span class="errText"><?php echo $err['delete']; ?></span></td>
									</tr>
								<?php } ?>
							</tbody>
						</table>
						<input type="hidden" name="delete" value="delete">
					</form>
				</div>
			</div>
			<footer>
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