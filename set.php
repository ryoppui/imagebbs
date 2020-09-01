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
    private $err = [];

    //入力チェック
    public function required($post, $errPoint, $errMessage)
    {

        if ($post === '' || ctype_space($post)) {
            $this->err[$errPoint] = $errMessage;
        }
    }

    //最大文字数チェック
    public function maxLength($post, $errPoint, $errMessage)
    {


        if (mb_strlen($post) > 1000) {
            $this->err[$errPoint] = $errMessage;
        }
    }

    //画像チェック
    public function imgType($size, $tmp_name)
    {


        if ($size !== 0) {

            //マイムタイプ
            if (
                exif_imagetype($tmp_name) !== IMAGETYPE_PNG && exif_imagetype($tmp_name) !== IMAGETYPE_JPEG &&
                exif_imagetype($tmp_name) !== IMAGETYPE_GIF
            ) {
                $this->err['upfile'] = '画像はGIF,JPG,PNGのいずれかにしてください';

            //サイズチェック
            } elseif ($size > 100000) {
                $this->err['upfile'] = '画像サイズが100KBを超えています';
            }
        }
    }

    //半角数字チェック
    public function halfNumber($post, $errPoint, $errMessage)
    {

        if (!preg_match("/^[0-9]+$/", $post)) {
            $this->err[$errPoint] = $errMessage;
        }
    }

    //パスワードチェック
    public function passCheck($pass, $errPoint)
    {

        if ($pass !== ADMIN_PASS) {
            $this->err[$errPoint] = 'パスワードが違います';
        }
    }

    //管理者削除チェック
    public function radioCheck($post, $errPoint)
    {

        if (!isset($post)) {
            $this->err[$errPoint] = '削除する記事を選択してください';
        }
    }

    // エラーセット
    public function setErr($errPoint, $errMessage)
    {
        $this->err[$errPoint] = $errMessage;
    }

    //エラーゲッター
    public function getErr() {
        return $this->err;
    }

    // エラーメッセージ表示
    public function errShow($errPoint)
    {
        if(!empty($this->err[$errPoint])) {
            return $this->err[$errPoint];
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
