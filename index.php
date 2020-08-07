<!DOCTYPE html>
<html lang="en">

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
                <form action="post" enctype="multipart/form-data" class="form">
                    <table class="table">
                        <tbody>
                            <tr>
                                <th class="table_title">おなまえ</th>
                                <td class="table_data" colspan="3"><input type="text" name="name" size="28" class="input"></td>
                            </tr>
                            <tr>
                                <th class="table_title">Eメール</th>
                                <td class="table_data" colspan="3"><input type="email" name="email" size="28" class="input"></td>
                            </tr>
                            <tr>
                                <th class="table_title">題名</th>
                                <td class="table_data"><input type="text" name="sub" size="35" class="input"></td>
                                <td class="table_data"><input type="submint" value="送信する" class="btn"></td>
                                <td class="table_data"><input type="reset" value="リセット" class="btn"></td>
                            </tr>
                            <tr>
                                <th class="table_title">コメント</th>
                                <td colspan="3" class="table_data"><textarea name="com" cols="50" rows="4" wrap="soft" class="textarea"></textarea></td>
                            </tr>
                            <tr>
                                <th class="table_title">U　R　L</th>
                                <td colspan="3" class="table_data"><input type="url" name="url" size="63" value="http://" class="input"></td>
                            </tr>
                            <tr>
                                <th class="table_title">添付File</th>
                                <td colspan="3" class="table_data"><input type="file" name="upfile" size="35" value="http://"></td>
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
            <div class="block block-right block-border">
                <div class="block block-spaceS">
                    <form action="post" class="form form-delete">
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