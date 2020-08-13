<?php

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
    //エラーメッセージ
    $err = [];
    //ログファイルから一行ごとに配列に
    $lines = file(LOGFILE);


    //POST送信があった場合
    if(!empty($_POST)) {
        //POSTの中身をそれぞれ変数に
        $name = $_POST['name'];
        $email = $_POST['email'];
        $sub = $_POST['sub'];
        $com = $_POST['com'];
        $url = $_POST['url'];
        $upfile = $_FILES['upfile'];
        $pwd = $_POST['pwd'];

        //バリデーションチェック
        if($name === '' || ctype_space($name)) {
            $err['name'] = '名前が書きこまれていません';
        }

        if($com === '' || ctype_space($com)) {
            $err['com'] = '本文が書き込まれていません';
        }

        if(mb_strlen($com) > 1000) {
            $err['com'] = '本文が長すぎますっ！';
        }

        if($upfile['size'] !== 0) {

            if(exif_imagetype($upfile['tmp_name']) !== IMAGETYPE_PNG && exif_imagetype($upfile['tmp_name']) !== IMAGETYPE_JPEG &&
            exif_imagetype($upfile['tmp_name']) !== IMAGETYPE_GIF) {
                $err['upfile'] = '画像はGIF,JPG,PNGのいずれかにしてください';
            } else if($upfile['size'] > 100000 ) {
                $err['upfile'] = '画像サイズが100KBを超えています';
            }
        }

        //バリデーションチェックをクリアした場合
        if(empty($err)) {

            //タイトルが未入力の場合は「(無題)」にする
            if($sub === '' || ctype_space($sub)) {
                $sub = '(無題)';
            }

            //画像のアップロードがあった場合はimagesディレクトリへ保存、ログにはファイルパスを保存
            if($upfile['size'] !== 0) {
                $file_path = './images/'.$upfile['name'];
                move_uploaded_file($upfile['tmp_name'], $file_path);
            } else {
                //画像アップロードがなかった場合ログファイルには空欄で保存
                $file_path = '';
            }

            //各入力項目を配列に格納
            $arr = array(
                "name" => $name,
                "email" => $email,
                "sub" => $sub,
                "com" => $com,
                "url" => $url,
                "upfile" => $file_path,
                "created_at" => date("Y-m-d H:i:s")
            );

            //logファイルを開いてcsv形式で書き込む
            $fp = fopen(LOGFILE, 'a');
            fputcsv($fp, $arr);
            fclose($fp);

        }


    }


?>

<!DOCTYPE html>
<html lang="ja">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=width=1000">
    <meta name="format-detection" content="telephone=no">
    <meta name="apple-mobile-web-app-title" content="画像BBS">
    <meta name="keywords" content="画像BBS">
    <meta name="description" content="画像BBS">
    <title>画像BBS</title>
    <link rel="stylesheet" href="css/ress.css">
    <link rel="stylesheet" href="css/style.css">
</head>

<body>
    <div class="l-contents">
        <div class="contents">
            <div class="block block-right">
                <ul class="list list-inlineFlex">
                    <li class="list_item">
                        <p>[<a href="http://php.s3.to/">ホーム</a>]</p>
                    </li>
                    <li class="list_item">
                        <p>[<a href="http://php.s3.to/">管理用</a>]</p>
                    </li>
                </ul>
            </div>
            <div class="title">
                <h1 class="title_text">画像BBS</h1>
            </div>
            <div class="block block-content">
                <form method="post" enctype="multipart/form-data" action="#" class="form" novalidate="novalidate">
                    <!-- <input type="hidden" name="MAX_FILE_SIZE" value="100000"> -->
                    <table class="table">
                        <tbody>
                            <tr>
                                <th class="table_title">おなまえ</th>
                                <td class="table_data" colspan="3">
                                    <input type="text" name="name" size="28" value="<?php if(!empty($err)) echo $name; ?>" class="input <?php if(!empty($err_name)) echo 'active'; ?>">
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
                                <td class="table_data"><input type="submit" value="送信する" class="btn"></td>
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
                                <td colspan="3" class="table_data table_data-flexEnd">
                                    <input type="password" name="pwd" size="8" maxlength="8" class="input">
                                    <div class="textBox">
                                        <p class="textBox_text textBox_text-sizeS">(記事の削除用。英数字で8文字以内)</p>
                                    </div>
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
                </form>
            </div>
            <div class="block block-spaceL">
                <?php if(!empty($lines)) { foreach($lines as $index => $line) {
                list($name, $email, $sub, $com, $url, $upfile, $created_at) = explode(',', $line); ?>
                    <div class="block block-article">
                        <div class="block_body block_body-flexAlignCenter">
                            <div class="textBox textBox-num">
                                <p class="textBox_text textBox_text-left">NO.<?php echo $index ?></p>
                            </div>
                            <div class="title title-borderNone">
                                <h2 class="title_text title_text-color title_text-sizeL"><?php echo $sub?></h2>
                            </div>
                        </div>
                        <div class="block_body block_body-flexAlignCenter">
                            <dl class="textBox textBox-flexAlignCenter">
                                <dt class="textBox_text textBox_text-spaceRightS">Name</dt>
                                <dd class="textBox_text"><a href="#" class="textBox_text textBox_text-color2"><?php echo $name?></a></dd>
                            </dl>
                            <dl class="textBox textBox-flexAlignCenter">
                                <dt class="textBox_text textBox_text-spaceRightS">Date</dt>
                                <dd class="textBox_text"><?php echo $created_at?></dd>
                            </dl>
                            <dl class="textBox textBox-flexAlignCenter">
                                <dt class="textBox_text textBox_text-spaceRightS">URL</dt>
                                <dd class="textBox_text"><?php echo $url ?></dd>
                            </dl>
                        </div>
                        <div class="textBox">
                            <p class="textBox_text textBox_text-wrap"><?php echo $com ?></p>
                        </div>
                    </div>
                <?php }} ?>
            </div>
            <div class="block block-right block-border">
                <div class="block block-spaceS">
                    <form method="post" class="form form-delete" novalidate="novalidate">
                        <table class="table table-delete">
                            <thead>
                                <tr>
                                    <th colspan="5" class="table_title table_title-delete">【記事削除】</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <th class="table_title table_title-delete">記事No</th>
                                    <td class="table_data"><input type="text" name="no" size="3" class="input input-delete"></td>
                                    <th class="table_title table_title-delete">削除キー</th>
                                    <td class="table_data"><input type="password" name="pwd" size="8" maxlength="8" class="input input-delete"></td>
                                    <td class="table_data"><input type="submint" value="削除" class="btn btn-delete"></td>
                                </tr>
                            </tbody>
                        </table>
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
</body>

</html>