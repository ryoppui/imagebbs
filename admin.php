<?php

require "set.php";

//フラッシュメッセージ初期化
$flash = new Flash();
$flash->reset();

$vali = new Validation();

//POST送信(login)があった場合
if (!empty($_POST['login'])) {

    $pass = $_POST['pass'];

    //バリデーション
    $vali->passCheck($pass, 'pass');
    $vali->halfNumber($pass, 'pass', '半角数字で入力してください');
    $vali->required($pass, 'pass', 'パスワードが入力されていません');

    if (empty($vali->getErr())) {
        $_SESSION['admin'] = true;
    }
}

//POST送信(logout)があった場合
if (!empty($_POST['admin']) && $_POST['logout']) {
    session_destroy();
    header('Location:' . $_SERVER['PHP_SELF']);

    //POST送信(delete)があった場合
} elseif (!empty($_POST['admin']) && $_POST['delete']) {
    $delete_no = $_POST['delete_radio'];

    $vali->radioCheck($delete_no, 'delete');

    if (isset($delete_no)) {

        $file = new File();

        foreach ($file->findAll() as $index => $line) {
            list(,,,,, $upfile,,,) = explode(',', $line);

            //POSTされた記事Noとインデックスがそれぞれ一致したらログファイルから該当のデータを削除
            if ($delete_no == $index) {

                $file = new File();

                $file->delete($delete_no + 1, $upfile);
            }
        }

        $flash->setFlash('削除しました');
        header('Location:' . $_SERVER['PHP_SELF']);
    }
}


//ページ表示用にインスタンス化
$file = new File();
$page = new Pagination();
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
        <?php if (!empty($flash->getFlash())) { ?>
            <div class="flashMessage js-flash">
                <p class="flashMessage_text"><?php echo $flash->getFlash(); ?></p>
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
            <?php if ($_SESSION['admin']) { ?>
                <form method="post" action="#" class="form form-admin" name="delete" novalidate="novalidate">
                    <div class="block block-spaceL">
                        <div class="title title-borderNone">
                            <h2 class="title_text title_text-sizeL">管理者モード</h2>
                        </div>
                        <?php if (!empty($vali->errShow('delete'))) { ?>
                            <span class="errText"><?php echo $vali->errShow('delete'); ?></span>
                        <?php } ?>
                        <?php if (!empty($file->findAll())) {
                            foreach ($file->showData() as $index => $line) {
                                list($name, $email, $sub, $com, $url, $upfile, $pwd, $created_at) = explode(',', $line); ?>
                                <div class="block block-article">
                                    <div class="block_body block_body-flexAlignCenter">
                                        <div class="textBox textBox-num">
                                            <p class="textBox_text textBox_text-left">NO.<?php echo $index + 1 ?></p>
                                        </div>
                                        <div class="title title-borderNone">
                                            <h2 class="title_text title_text-color title_text-sizeL"><?php echo htmlspecialchars($sub); ?></h2>
                                        </div>
                                    </div>
                                    <div class="block_body block_body-flexAlignCenter">
                                        <dl class="textBox textBox-flexAlignCenter">
                                            <dt class="textBox_text textBox_text-spaceRightS">Name</dt>
                                            <dd class="textBox_text">
                                                <a <?php if (!empty($email)) { ?>href="mailto:<?php echo $email; ?>" <?php } ?> class="textBox_text textBox_text-color2"><?php echo htmlspecialchars($name); ?></a>
                                            </dd>
                                        </dl>
                                        <dl class="textBox textBox-flexAlignCenter">
                                            <dt class="textBox_text textBox_text-spaceRightS">Date</dt>
                                            <dd class="textBox_text"><?php echo $created_at ?></dd>
                                        </dl>
                                        <dl class="textBox textBox-flexAlignCenter">
                                            <dt class="textBox_text textBox_text-spaceRightS">URL</dt>
                                            <dd class="textBox_text">
                                                <?php if ($url === 'none') { ?>
                                                    <a><?php echo htmlspecialchars($url); ?></a>
                                                <?php } else { ?>
                                                    <a href="<?php echo $url; ?>"><?php echo htmlspecialchars($url); ?></a>
                                                <?php } ?>
                                            </dd>
                                        </dl>
                                    </div>
                                    <?php if (!empty($upfile)) {
                                        list($width, $height) = imageCustom($upfile) ?>
                                        <div class="block_body block_body-spaceS">
                                            <a href="<?php echo $upfile ?>" target="_blank"><img src="<?php echo $upfile ?>" style="width: <?php echo $width; ?>px; height: <?php echo $height; ?>px;" alt=""></a>
                                        </div>
                                    <?php } ?>
                                    <div class="textBox textBox-spaceM">
                                        <p class="textBox_text textBox_text-wrap"><?php echo htmlspecialchars($com); ?></p>
                                    </div>
                                    <div class="block_body block_body-right">
                                        <input type="radio" name="delete_radio" value="<?php echo $index; ?>">
                                    </div>
                                </div>
                        <?php }
                        } ?>
                        <?php if (!empty($file->findAll())) { ?>
                            <div class="pagination">
                                <div class="pagination_list">
                                    <?php if ($page->now() == 1) { ?>
                                        <a class="pagination_list_item">&lt;</a>
                                    <?php } else { ?>
                                        <a href="/?page=<?php echo $page->getPage() - 1; ?>" class="pagination_list_item">&lt;</a>
                                    <?php } ?>
                                </div>
                                <ol class="pagination_list">
                                    <?php for ($i = 1; $i <= $page->maxPage(); $i++) {
                                        if ($i == $page->now()) { ?>
                                            <li class="pagination_list_item"><a><?php echo $i; ?></a></li>
                                        <?php } else { ?>
                                            <li class="pagination_list_item"><a href="/?page=<?php echo $i; ?>"><?php echo $i; ?></a></li>
                                    <?php }
                                    } ?>
                                </ol>
                                <div class="pagination_list">
                                    <?php if ($page->now() == $page->maxPage()) { ?>
                                        <a class="pagination_list_item">&gt;</a>
                                    <?php } else { ?>
                                        <a href="/?page=<?php echo $page->getPage() + 1; ?>" class="pagination_list_item pagination_list_item-last">&gt;</a>
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
                        <?php if (!empty($vali->errShow('pass'))) { ?>
                            <span class="errText"><?php echo $vali->errShow('pass'); ?></span>
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