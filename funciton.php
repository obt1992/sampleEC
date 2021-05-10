<?php
function xss_header(){
  header('Cache-Control: private,no-store,must-revalidate');
  //Webサーバから返されてくるコンテンツをキャッシュさせないようにさせる
  header('X-Content-Type-Options:nosniff');
  //スクリプトが混じっていた場合に勝手にHTMLとして解釈されることを防ぐ
  header('X-Frame-Options:DENY');
  //クリックジャッキング攻撃対策。外部のサイトからiframeを使って呼ばれる事を防ぐ。
  //X-Frame-Options:DENYだと、すべてのページから呼べない。
  header('X-Frame-Options:SAMEORIGIN');
  //X-Frame-Options:SAMEORIGINだと、同じサイト内からであれば呼べる。
  header('X-Frame-Options:ALLOW-FROM uri');
  //X-Frame-Options:ALLOW-FROMだと指定された生成元に限り、ページをフレーム内に表示できる。
  header('X-XSS-Protection:1; mode=block');
  //XSSフィルタの誤作動を防ぐため
  header('Access-Control-Allow-Origin: *');
  //特定のサイトからしかAjaxで呼び出しできないように設定する。
}

// トークンの生成
function get_csrf_token(){
  // get_random_string()はユーザー定義関数。
  $token = get_random_string(30);
  // set_session()はユーザー定義関数。
  set_session('csrf_token', $token);
  return $token;
}

// トークンのチェック
function is_valid_csrf_token($token){
  if($token === '') {
    return false;
  }
  // get_session()はユーザー定義関数
  return $token === get_session('csrf_token');
}
?>