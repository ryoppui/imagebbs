<?php
/*************************************
  * 画像BBS             by ToR
  *
  * http://php.s3.to/
  *
  * 画像アップロード掲示板です。
  *
  * 保存用ディレクトリimgを作成して777にします。
  * 空のログファイルimglog.logを用意して666にします。
  * サーバーによってはアプロードできません
  *
  * 2001/09/27 v2.4 画像保存名をローカル→時間名、ページング
  * 2001/10/31 v3.0 作り直し。管理者用投稿ページ作成。ﾌｫｰﾑも分離可
  **************************************/
//----設定--------
define(LOGFILE, 'imglog2.log');		//ログファイル名
define(PATH, './img/');			//画像保存ディレクトリ./???/

define(TITLE, '画像BBS');		//タイトル（<title>とTOP）
define(HOME,  'http://php.s3.to');	//「ホーム」へのリンク

define(MAX_KB, '100');			//投稿容量制限 KB（phpの設定により2Mまで
define(MAX_W,  '250');			//投稿サイズ幅（これ以上はwidthを縮小
define(MAX_H,  '250');			//投稿サイズ高さ

define(PAGE_DEF, '7');			//一ページに表示する記事
define(LOG_MAX,  '200');		//ログ最大行数

define(ADMIN_PASS, '0123');		//管理者パス
define(CHECK, 1);			//管理者がチェックしてから画像表示？yes=1
define(SOON_ICON, 'soon.jpg');		//チェック中の時の代替画像

define(BUNRI, 0);			//投稿フォームを分離する？

define(PHP_SELF, $PHP_SELF);		//このスクリプト名

/* ヘッダ */
function head(&$dat){
  $dat.='
<meta charset="utf-8">
<meta name="viewport" content="width=width=1000">
<meta name="format-detection" content="telephone=no">
<meta name="apple-mobile-web-app-title" content="'.TITLE.'">
<meta name="keywords" content="'.TITLE.'">
<meta name="description" content="'.TITLE.'">
<STYLE TYPE="text/css">
<!--
body,tr,td,th { font-size:10pt }
a:hover { color:#DD0000; }
span { font-size:20pt }
small { font-size:8pt }
-->
</STYLE>
<title>'.TITLE.'</title></head>
<body bgcolor="#FFFFEE" text="#800000" link="#0000EE" vlink="#0000EE">
<p align=right>
[<a href="'.HOME.'" target="_top">ホーム</a>]
[<a href="'.PHP_SELF.'?mode=admin">管理用</a>]
<p align=center>
<font color="#800000" face="ＭＳ Ｐゴシック" size=5>
<b><SPAN>'.TITLE.'</SPAN></b></font>
<hr width="90%" size=1>
';
}
/* 投稿フォーム */
function form(&$dat,$admin=""){
  $maxbyte = MAX_KB * 1024;
  if($admin){
    $hidden = "<input type=hidden name=admin value=\"".ADMIN_PASS."\">";
    $msg = "<h4>タグがつかえます</h4>";
  }
  $dat.='
<center>'.$msg.'
<form action="'.PHP_SELF.'" method="POST" enctype="multipart/form-data">
<input type=hidden name=mode value="regist">
'.$hidden.'
<input type=hidden name="MAX_FILE_SIZE" value="'.$maxbyte.'">
<table cellpadding=1 cellspacing=1>
<tr>
  <td bgcolor=#eeaa88><b>おなまえ</b></td>
  <td><input type=text name=name size="28"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>Ｅメール</b></td>
  <td><input type=text name=email size="28"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>題　　名</b></td>
  <td>
    <input type=text name=sub size="35">
    <input type=submit value="送信する"><input type=reset value="リセット">
  </td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>コメント</b></td>
  <td><textarea name=com cols="48" rows="4" wrap=soft></textarea>
  </td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>ＵＲＬ</b></td>
  <td><input type=text name=url size="63" value="http://"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>添付File</b></td>
  <td><input type=file name=upfile size="35"></td>
</tr>
<tr>
  <td bgcolor=#eeaa88><b>削除キー</b></td>
  <td>
    <input type=password name=pwd size=8 maxlength=8 value="">
    <small>(記事の削除用。英数字で8文字以内)</small>
  </td>
</tr>
<tr><td colspan=2>
<small>
<LI>添付可能ファイル ： GIF, JPG, PNG<br>
<LI>ブラウザによっては正常に添付できないことがあります。<br>
<LI>最大投稿データ量は '.MAX_KB.' KB までです。<br>
<LI>画像は横 '.MAX_W.'ピクセル、縦 '.MAX_H.'ピクセルを超えると縮小表示されます。
</small>
</td></tr></table></form></center>
<hr>
  ';
}
/* 記事部分 */
function main(&$dat, $page){
  $line = file(LOGFILE);
  $st = ($page) ? $page : 0;

  for($i = $st; $i < $st+PAGE_DEF; $i++){
    if($line[$i]=="") continue;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pwd,$ext,$w,$h,$time,$chk) = explode(",", $line[$i]);
    // URLとメールにリンク
    if($url)   $url = "<a href=\"http://$url\" target=_blank>Link</a>";
    if($email) $name = "<a href=\"mailto:$email\">$name</a>";
    $com = eregi_replace("(^|/>)(&gt;[^<]*)", "\\1<font color=789922>\\2</font>", $com);
    // 画像ファイル名
    $img = PATH.$time.$ext;
/* 自由に変更してください["]=[\"]に */
    // <imgタグ作成
    $imgsrc = "";
    if($ext && is_file($img)){
      $size = ceil(filesize($img) / 1024);//altにサイズ表示
      if(CHECK && $chk != 1){//未チェック
        $imgsrc = "<img src=".SOON_ICON.">";
      }elseif($w && $h){//サイズがある時
        $imgsrc = "<a href=\"".$img."\" target=_blank><img src=".$img."
			border=0 align=left width=$w height=$h hspace=20 alt=\"".$size." KB\"></a>";
      }else{//それ以外
        $imgsrc = "<a href=\"".$img."\" target=_blank><img src=".$img."
			border=0 align=left hspace=20 alt=\"".$size." KB\"></a>";
      }
    }
    // メイン作成
    $dat.="No.$no <font color=#cc1105 size=+1><b>$sub</b></font><br> ";
    $dat.="Name <font color=#117743><b>$name</b></font> Date $now &nbsp; $url";
    $dat.="<p><blockquote>$imgsrc $com</blockquote><br clear=left><hr>\n";

    $p++;
    clearstatcache();//ファイルのstatをクリア
  }
  $prev = $st - PAGE_DEF;
  $next = $st + PAGE_DEF;
  // 改ページ処理
  $dat.="<table align=left><tr>\n";
  if($prev > 0){
    $dat.="<td><form action=\"".PHP_SELF."\" method=POST>";
    $dat.="<input type=hidden name=page value=$prev>";
    $dat.="<input type=submit value=\"前のページ\" name=submit>\n";
    $dat.="</form></td>\n";
  }
  if($p >= PAGE_DEF && count($line) > PAGE_DEF){
    $dat.="<td><form action=\"".PHP_SELF."\" method=POST>";
    $dat.="<input type=hidden name=page value=$next>";
    $dat.=" <input type=submit value=\"次のページ\" name=submit>\n";
    $dat.="</form></td>\n";
  }
  $dat.="</td>\n</tr></table>\n";
}
/* フッタ */
function foot(&$dat){
  $dat.='
<table align=right><tr>
<td nowrap align=center><form action="'.PHP_SELF.'" method=POST>
<input type=hidden name=mode value=usrdel>
【記事削除】<br>
記事No<input type=text name=no size=3>
削除キー<input type=password name=pwd size=4 maxlength=8>
<input type=submit value="削除">
</form></td>
</tr></table><br clear=all>
<center><P><small><!-- GazouBBS v3.0 -->
- <a href="http://php.s3.to" target=_top>GazouBBS</a> -
</small></center>
</body></html>
  ';
}
/* 記事書き込み */
function regist($name,$email,$sub,$com,$url,$pwd,$upfile,$upfile_name){
  global $REQUEST_METHOD;

  if($REQUEST_METHOD != "POST") error("不正な投稿をしないで下さい");
  // フォーム内容をチェック
  if(!$name||ereg("^[ |　|]*$",$name)) error("名前が書き込まれていません");
  if(!$com||ereg("^[ |　|\t]*$",$com)) error("本文が書き込まれていません");
  if(!$sub||ereg("^[ |　|]*$",$sub))   $sub="（無題）";
  if(strlen($com) > 1000) error("本文が長すぎますっ！");

  $line = file(LOGFILE);
  // 時間とホスト取得
  $tim = time();
  $host = gethostbyaddr(getenv("REMOTE_ADDR"));
  // 連続投稿チェック
  list($lastno,,$lname,,,$lcom,,$lhost,,,,,$ltime,) = explode(",", $line[0]);
  if(RENZOKU && $host == $lhost && $tim - $ltime < RENZOKU)
    error("連続投稿はもうしばらく時間を置いてからお願い致します");
  // No.とパスと時間とURLフォーマット
  $no = $lastno + 1;
  $pass = ($pwd) ? substr(md5($pwd),2,8) : "*";
  $now = gmdate("Y/m/d(D) H:i",$tim+9*60*60);
  $url = ereg_replace("^http://", "", $url);
  //テキスト整形
  $name = CleanStr($name);
  $email= CleanStr($email);
  $sub  = CleanStr($sub);
  $url  = CleanStr($url);
  $com  = CleanStr($com);
  // 改行文字の統一。
  $com = str_replace( "\r\n",  "\n", $com);
  $com = str_replace( "\r",  "\n", $com);
  // 連続する空行を一行
  $com = ereg_replace("\n((　| )*\n){3,}","\n",$com);
  $com = nl2br($com);										//改行文字の前に<br>を代入する
  $com = str_replace("\n",  "", $com);	//\nを文字列から消す。
  // 二重投稿チェック
  if($name == $lname && $com == $lcom)
    error("二重投稿は禁止です<br><br><a href=$PHP_SELF>リロード</a>");
  // ログ行数オーバー
  if(count($line) >= LOG_MAX){
    for($d = count($line)-1; $d >= LOG_MAX-1; $d--){
      list($dno,,,,,,,,,$ext,,,$dtime,) = explode(",", $line[$d]);
      if(is_file(PATH.$dtime.$ext)) unlink(PATH.$dtime.$ext);
      $line[$d] = "";
    }
  }
  // アップロード処理
  if($upfile != "none"){
    $dest = PATH.$upfile_name;
    copy($upfile, $dest);
    if(!is_file($dest)) error("アップロードに失敗しました。サーバがサポートしていない可能性があります");
    $size = getimagesize($dest);
    $W = $size[0];
    $H = $size[1];
    rename($dest,$tim.$size[2]);
    // 画像表示縮小
    if($W > Max_W || $H > Max_H){
      $W2 = Max_W / $W;
      $H2 = Max_H / $H;

      ($W2 < $H2) ? $key = $W2 : $key = $H2;

      $W = $W * $key;
      $H = $H * $key;
    }
    $mes = "画像 $upfile_name のアップロードが成功しました<br><br>";
  }
  $chk = (CHECK) ? 0 : 1;//未チェックは0

  $newline = "$no,$now,$name,$email,$sub,$com,$url,$host,$pass,.$size[2],$W,$H,$tim,$chk,\n";

  $fp = fopen(LOGFILE, "w");
  flock($fp, 2);
  fputs($fp, $newline);
  fputs($fp, implode('', $line));
  fclose($fp);

  echo "$msg 画面を切り替えます";
  echo "<META HTTP-EQUIV=\"refresh\" content=\"1;URL=".PHP_SELF."?\">";
}
/* テキスト整形 */
function CleanStr($str){
  global $admin;

  $str = trim($str);//先頭と末尾の空白除去
  if (get_magic_quotes_gpc()) {//￥を削除
    $str = stripslashes($str);
  }
  if($admin!=ADMIN_PASS){//管理者はタグ可能
    $str = htmlspecialchars($str, ENT_QUOTES, 'Shift_JIS');//タグっ禁止
    $str = str_replace("&amp;", "&", $str);//特殊文字
  }
  return str_replace(",", "&#44;", $str);//カンマを変換
}
/* ユーザー削除 */
function usrdel($no,$pwd){
  if($no == "" || $pwd == "") error("削除Noまたはパスワードが入力漏れです");

  $line = file(LOGFILE);
  $flag = FALSE;

  for($i = 0; $i<count($line); $i++){
    list($dno,,,,,,,,$pass,$dext,,,$dtim,) = explode(",", $line[$i]);
    if($no == $dno && substr(md5($pwd,2,8)) == $pass){
      $flag = TRUE;
      $line[$i] = "";			//パスワードがマッチした行は空に
      $delfile = PATH.$dtim.$dext;	//削除ファイル
      break;
    }
  }
  if(!$flag) error("該当記事が見つからないかパスワードが間違っています");
  // ログ更新
  $fp = fopen(LOGFILE, "w");
  flock($fp, 2);
  fputs($fp, implode('', $line));
  fclose($fp);

  if(is_file($delfile)) unlink($delfile);//削除
}
/* パス認証 */
function valid($pass){
  if($pass && $pass != ADMIN_PASS) error("パスワードが違います");

  head($dat);
  echo $dat;
  echo "[<a href=\"".PHP_SELF."\">掲示板に戻る</a>]\n";
  echo "<table width='100%'><tr><th bgcolor=#E08000>\n";
  echo "<font color=#FFFFFF>管理モード</font>\n";
  echo "</th></tr></table>\n";
  echo "<p><form action=\"".PHP_SELF."\" method=POST>\n";
  // ログインフォーム
  if(!$pass){
    echo "<center><input type=radio name=admin value=del checked>記事削除 ";
    echo "<input type=radio name=admin value=post>管理人投稿<p>";
    echo "<input type=hidden name=mode value=admin>\n";
    echo "<input type=password name=pass size=8>";
    echo "<input type=submit value=\" 認証 \"></form></center>\n";
    die("</body></html>");
  }
}
/* 管理者削除 */
function admindel($delno,$chkno){
  global $pass;

  if($chkno || $delno){
    $line = file(LOGFILE);
    $find = FALSE;
    for($i = 0; $i < count($line); $i++){
      list($no,$now,$name,$email,$sub,$com,$url,
           $host,$pw,$ext,$w,$h,$tim,$chk) = explode(",",$line[$i]);
      if($chkno == $no){//画像チェック$chk=1に
        $find = TRUE;
        $line[$i] = "$no,$now,$name,$email,$sub,$com,$url,$host,$pw,$ext,$w,$h,$tim,1,\n";
        break;
      }
      if($delno == $no){//削除の時は空に
        $find = TRUE;
        $line[$i] = "";
        break;
      }
    }
    if($find){//ログ更新
      $fp = fopen(LOGFILE, "w");
      flock($fp, 2);
      fputs($fp, implode('', $line));
      fclose($fp);
    }
  }
  // 削除画面を表示
  echo "<input type=hidden name=mode value=admin>\n";
  echo "<input type=hidden name=admin value=del>\n";
  echo "<input type=hidden name=pass value=\"$pass\">\n";
  echo "<center><P>削除したい記事のチェックボックスにチェックを入れ、削除ボタンを押して下さい。\n";
  echo "<P><table border=1 cellspacing=0>\n";
  echo "<tr bgcolor=6080f6><th>削除</th><th>記事No</th><th>投稿日</th><th>題名</th>";
  echo "<th>投稿者</th><th>コメント</th><th>ホスト名</th><th>添付<br>(Bytes)</th>";
  if(CHECK) echo "<th>画像<br>許可</th>";
  echo "</tr>\n";

  $line = file(LOGFILE);

  for($j = 0; $j < count($line); $j++){
    $img_flag = FALSE;
    list($no,$now,$name,$email,$sub,$com,$url,
         $host,$pw,$ext,$w,$h,$time,$chk) = explode(",",$line[$j]);
    // フォーマット
    list($now,$dmy) = split("\(", $now);
    if($email) $name="<a href=\"mailto:$email\">$name</a>";
    $com = str_replace("<br />"," ",$com);
    $com = htmlspecialchars($com, ENT_QUOTES, 'Shift_JIS');
    if(strlen($com) > 40) $com = substr($com,0,38) . " ...";
    // 画像があるときはリンク
    if($ext && is_file(PATH.$time.$ext)){
      $img_flag = TRUE;
      $clip = "<a href=\"".PATH.$time.$ext."\" target=_blank>".$time.$ext."</a>";
      $size = filesize(PATH.$time.$ext);
      $all += $size;			//合計計算
    }else{
      $clip = "";
      $size = 0;
    }
    $bg = ($j % 2) ? "d6d6f6" : "f6f6f6";//背景色

    echo "<tr bgcolor=$bg><th><input type=checkbox name=del value=\"$no\"></th>";
    echo "<th>$no</th><td><small>$now</small></td><td>$sub</td>";
    echo "<td><b>$name</b></td><td><small>$com</small></td>";
    echo "<td>$host</td><td align=center>$clip<br>($size)</td>\n";

    if(CHECK){//画像チェック
      if($img_flag && $chk == 1){
        echo "<th><font color=red>OK</font></th>";
      }elseif($img_flag && $chk != 1) {
        echo "<th><input type=checkbox name=chk value=$no></th>";
      }else{
        echo "<td><br></td>";
      }
    }
    echo "</tr>\n";
  }
  if(CHECK) $msg = "or許可する";

  echo "</table><p><input type=submit value=\"削除する$msg\">";
  echo "<input type=reset value=\"リセット\"></form>";

  $all = (int)($all / 1024);
  echo "【 画像データ合計 : <b>$all</b> KB 】";
  die("</center></body></html>");
}
/* エラー画面 */
function error($mes){
  global $upfile_name;

  if(is_file(PATH.$upfile_name)) unlink(PATH.$upfile_name);

  head($dat);
  echo $dat;
  echo "<br><br><hr size=1><br><br>
        <center><font color=red size=5><b>$mes</b></font></center>
        <br><br><hr size=1>";
  die("</body></html>");
}
/*-----------Main-------------*/
switch($mode){
  case 'regist':
    regist($name,$email,$sub,$com,$url,$pwd,$upfile,$upfile_name);
    break;
  case 'admin':
    valid($pass);
    if($admin=="del") admindel($del,$chk);
    if($admin=="post"){
      echo "</form>";
      form($post,1);
      echo $post;
      die("</body></html>");
    }
    break;
  case 'usrdel':
    usrdel($no,$pwd);
    break;
  default:
    head($buf);
    if(!BUNRI) form($buf);
    main($buf,$page);
    foot($buf);
    echo $buf;
}
?>