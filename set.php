<?php

session_start();

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



//クラス
//バリデーションクラス
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
    public static function halfNumber($post, $errPoint, $errMessage)
    {
        global $err;

        if (!preg_match("/^[0-9]+$/", $post)) {
            $err[$errPoint] = $errMessage;
        }
    }

    //パスワードチェック
    public static function passCheck($pass, $errPoint)
    {
        global $err;

        if ($pass !== ADMIN_PASS) {
            $err[$errPoint] = 'パスワードが違います';
        }
    }

    //管理者削除チェック
    public static function radioCheck($post, $errPoint)
    {
        global $err;

        if (!isset($post)) {
            $err[$errPoint] = '削除する記事を選択してください';
        }
    }
}


//ログファイルクラス
class File
{
    private $all;

    function __construct()
    {
        $this->all = array_reverse(file(LOGFILE), true);
    }

    //全件取得
    public function findAll()
    {
        return  $this->all;
    }

    //データ表示
    public function showData()
    {
        //ゲットパラメータのページ
        $page = $_GET['page'];

        //GETパラメータから表示するページを取得
        if (!isset($page)) {
            $now = 1;
        } else {
            $now = $page;
        }

        //何件目から表示させるか
        $start_no = ($now - 1) * PAGE_DEF;
        //1ページ分のデータを取得
        $show_data = array_slice($this->all, $start_no, PAGE_DEF, true);

        return $show_data;
    }

    //ファイル更新
    public function update($arr)
    {
        $fp = fopen(LOGFILE, 'a');
        fputcsv($fp, $arr);
        fclose($fp);
    }

    //ファイルデータ削除
    public function delete($no, $upfile)
    {
        $log_file = file(LOGFILE);
        unset($log_file[$no - 1]);
        file_put_contents(LOGFILE, $log_file);

        //画像データの投稿があれば画像も削除
        if (!empty($upfile)) {
            unlink($upfile);
        }
    }
}

//ページネーションクラス
class Pagination
{
    private $all;
    private $page;

    function __construct()
    {
        $this->all = array_reverse(file(LOGFILE), true);
        $this->page = $_GET['page'];
    }

    //総ページ数
    public function maxPage()
    {
        $lines_num = count($this->all);
        $max_page = ceil($lines_num / PAGE_DEF);

        return $max_page;
    }

    //Getパラメーターのゲッター
    public function getPage()
    {
        return $this->page;
    }

    // 現在ページ
    public function now()
    {
        if (!isset($this->page)) {
            $now = 1;
        } else {
            $now = $this->page;
        }
        return $now;
    }
}

//フラッシュメッセージクラス
class Flash
{
    private $flash;

    //初期化
    public function reset()
    {
        $this->flash = isset($_SESSION['flash']) ? $_SESSION['flash'] : array();
        unset($_SESSION['flash']);
    }

    //メッセージセット
    public function setFlash($msg)
    {
        $_SESSION['flash'] = $msg;
        $this->flash = $_SESSION['flash'];
    }

    //ゲットメッセージ
    public function getFlash()
    {
        return $this->flash;
    }
}
