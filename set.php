<?php

//定数
const LOGFILE = './imglog.csv';
const PAGE_DEF = '7';
const ADMIN_PASS = '0123';

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


//関数
//画像のサイズが規定を超えていたら縮小する
function imageCustom($upfile)
{
    if (!empty($upfile)) {
        $image_info = getimagesize($upfile);
        $width = $image_info[0];
        $height = $image_info[1];

        if ($width > 250 || $height > 250) {
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
function flashMessage($msg)
{
    global $flash;
    $_SESSION['flash'] = $msg;
    $flash = $_SESSION['flash'];
}


//クラス
//バリデーション
class Validation
{

    //入力チェック
    public static function required($post, $errPoint, $errMessage)
    {
        global $err;

        if ($post === '' || ctype_space($post)) {
            $err[$errPoint] = $errMessage;
        }
    }

    //最大文字数チェック
    public static function maxLength($post, $errPoint, $errMessage)
    {

        global $err;

        if (mb_strlen($post) > 1000) {
            $err[$errPoint] = $errMessage;
        }
    }

    //画像チェック
    public static function imgType($size, $tmp_name)
    {

        global $err;

        if ($size !== 0) {

            //マイムタイプ
            if (
                exif_imagetype($tmp_name) !== IMAGETYPE_PNG && exif_imagetype($tmp_name) !== IMAGETYPE_JPEG &&
                exif_imagetype($tmp_name) !== IMAGETYPE_GIF
            ) {
                $err['upfile'] = '画像はGIF,JPG,PNGのいずれかにしてください';

                //サイズチェック
            } elseif ($size > 100000) {
                $err['upfile'] = '画像サイズが100KBを超えています';
            }
        }
    }

    //半角数字チェック
    public static function halfNumber($post, $errPoint)
    {

        global $err;

        if (!preg_match("/^[0-9]+$/", $post)) {
            $err[$errPoint] = '半角数字で入力してください';
        }
    }
}
