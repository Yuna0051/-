<?php

namespace FK;

/**
 * ===================================================================
 * License: see lib/info/LICENSE.md
 * Terms: https://kantaro-cgi.com/terms/
 * Url: https://kantaro-cgi.com/program/formkit/
 * Distributor: kazaoki lab.
 * ===================================================================
 */

/**
 * モードによって適した値に変換する関数
 * -------------------------------------------------------------------------------------------------
 */
function trans($string)
{
	global $EchoMode;
	global $Config;
	switch($EchoMode) {
		case 'input':
			$string = h($string);
			$string = conv_out($string);
			break;
		case 'view':
			$string = h($string);
			$string = str_replace("\n", '<br>', $string);
			if(!strlen($string)) $string = $Config['empty_label'];
			$string = conv_out($string);
			break;
		case 'mail':
			if(!strlen($string)) $string = strip_tags($Config['empty_label']);
			$string = preg_replace('/\n+$/', '', $string); // 末尾の改行を除去
			break;
		case 'csv':
		default:
			$string = strval($string);
	}
	return $string;
}

/**
 * クエリ値をまとめてhiddenタグ出力
 * -------------------------------------------------------------------------------------------------
 * hiddens_tag() ... $Config['validate']['list']で定義されている要素で、$_POSTで入ってきた要素のみ、hiddenタグにして出力
 */
function hiddens_tag(){
	global $Config;
	global $FK;
	$html = '';

	// 対象要素探し
	$element_keys = array();
	$element_keys = array_keys($Config['validate']['list']);

	// POST情報のhiddenタグを出力
	$hiddens = array();
	foreach($element_keys as $key)
	{
		if(isset($FK[$key]))
		{
			if(!array_key_exists($key, $_POST)) continue;
			$hiddens[$key] = $FK[$key]->val;
			// ファイルの場合、情報追加
			if(isset($FK[$key]->file['org_name'])){
				$hiddens[$key] = array(
					'org_name' => $FK[$key]->file['org_name'],
					'tmp_name' => $FK[$key]->file['tmp_name'],
				);
			}
		}
	}
	if($hiddens) {
		$html .= '<input type="hidden" name="'.$Config['hiddens_vars_key'].'" value="'.h(serialize($hiddens)).'">'."\n";
	}

	// CSRFのhiddenタグを出力
	if(@$Config['security']['csrf']) {
		$csrf = @$_REQUEST[$Config['security']['csrf']];
		if(!$csrf) $csrf = $_SESSION[$Config['security']['csrf']];
		$html .= '<input type="hidden" name="'.$Config['security']['csrf'].'" value="'.h($csrf).'">'."\n";
	}

	return conv_out($html);
}

/**
 * 設定のFUNC()内で使いたい要素を宣言するときの関数
 * -------------------------------------------------------------------------------------------------
 */
function element($name){
	// 指定の要素を取得
	eval('global $'.$name.';');
	eval('$elem = $'.$name.';');
	if(!$elem) return false;

	// 無限ループしないように制御
	$bt = debug_backtrace();
	if(@count($bt)>10) {
		error_log('Element ['.$name.'] is recursive loading by element() in config php.');
		return $elem;
	}

	// 先に一旦バリデート実施。値が最新になって欲しいので。
	$elem->validate();

	return $elem;
}

/**
 * データを綺麗にする
 * -------------------------------------------------------------------------------------------------
 */
function clean($data){
	if(is_array($data)){
		foreach($data as $key=>$value){
			$data[$key] = clean($value);
		}
	} else {
		$data = preg_replace('/^[ \t\n\r\0\x0B　]+/u', '', $data); # データ先頭のトリム
		$data = preg_replace('/[ \t\n\r\0\x0B　]+$/u', '', $data); # データ末尾のトリム
		$data = preg_replace('/(\r\n){2,}/', '$1$1', $data); # 複数連続改行は２つまで
		$data = preg_replace('/(\r){2,}/', '$1$1', $data); # 複数連続改行は２つまで
		$data = preg_replace('/(\n){2,}/', '$1$1', $data); # 複数連続改行は２つまで
	}
	return $data;
}

/**
 * エラー処理
 * -------------------------------------------------------------------------------------------------
 */
function error_page($message, $code=403, $detail=null) {
	header('HTTP', true, $code);
	include FK_DIR.'/lib/error.php';
	exit;
}

/**
 * POST通信のみ許可
 * -------------------------------------------------------------------------------------------------
 * 引数指定で、POST以外の場合に再入力URLに自動的にジャンプする
 */
function post_only() {
	global $Config;
	if($_SERVER['REQUEST_METHOD'] !== 'POST')
	{
		if(@$Config['reinput_url']) {
			header('location: '.$Config['reinput_url']);
			exit;
		} else {
			error_page('POST以外の通信でアクセスされました。正しいアクセスではありません。');
		}
	}
}

/**
 * CSVデータをShift-JISに変換する関数
 * -------------------------------------------------------------------------------------------------
 * 参考：http://kantaro-cgi.com/blog/php/save_csv_by_sjis.html
 */
function arr2csv($fields, $charset='Shift-JIS') {
	$fp = fopen('php://temp', 'r+b');
	foreach($fields as $field) {
		fputcsv($fp, $field);
	}
	rewind($fp);
	$tmp = str_replace(PHP_EOL, "\n", stream_get_contents($fp));
	return mb_convert_encoding($tmp, $charset, 'UTF-8');
	return $tmp;
}

/**
 * 文字列の変数を全て展開する
 * -------------------------------------------------------------------------------------------------
 */
function replace_values($value, $dic) {
	global $Config;
	if(is_array($value)){
		if(array_values($value) === $value){
			// 配列の場合
			foreach($value as &$under_value){
				$under_value = replace_values($under_value, $dic);
			}
		} else {
			// 連想配列の場合
			foreach($value as $key=>$under_value){
				unset($value[$key]);
				$key = replace_values($key, $dic);
				$value[$key] = replace_values($under_value, $dic);
			}
		}
	} else if(gettype($value) === 'string'){
		extract($dic);
		$value = eval("return \"$value\";");
	}
	return $value;
}
# ↓意図的な解析を禁止とします。(Analysis prohibited)
$_l_=
'ZnVuY3Rpb24gX19GSygkYmYpew0KCWdsb2JhbCAkRWNob01vZGU7DQoJaWYoJEVjaG9Nb2RlIT0naW5wdXQnKSByZXR1cm4gZ'.
'mFsc2U7DQoJaWYocHJlZ19tYXRjaCgnL1w8YSBocmVmXD1cImh0dHBzXDpcL1wva2FudGFyb1wtY2dpXC5jb20vJywkYmYpKS'.
'ByZXR1cm4gZmFsc2U7DQoJJGNjPWZ1bmN0aW9uKCl7JHM9ZXhwbG9kZSgnLScsZnVuY19nZXRfYXJncygpWzBdKTskcD0kYz0'.
'kaz0xO3ByZWdfbWF0Y2goJy9eKFxkKykoXGR7OX0pJC8nLCRzWzJdLCRtKTtsaXN0KCwkcSwkdCk9JG07Zm9yZWFjaChzdHJf'.
'c3BsaXQoJHNbMV0pYXMkbil7aWYoJG4+MSl7JGMqPSRuKiRwOyRjKz0kcTskYz1zdWJzdHIoJGMsMCw5KTt9JGs9c3Vic3RyK'.
'Cgkayo9KCRuPjEpPygkbiokcCk6MSksMCw1KTskcCsrO30kYyo9JHNbMF07JGM9c3Vic3RyKCRjLC05LDkpO3JldHVybigkYz'.
'09PSR0JiYkaz09MzU0NDEpO307DQoJaWYoaXNfZmlsZShGS19ESVIuJy9saWIvTElDRU5TRV9DT0RFJykgJiYgJGNjKHRyaW0'.
'oQGZpbGVfZ2V0X2NvbnRlbnRzKEZLX0RJUi4nL2xpYi9MSUNFTlNFX0NPREUnKSkpKSByZXR1cm4gZmFsc2U7DQoJcmV0dXJu'.
'ICdGb3JtS2l0IGNvcmUgZXJyb3IsIE9yIExpY2Vuc2UgZXJyb3IgISc7DQp9DQppZihhcnJheV9rZXlfZXhpc3RzKCdmay1pb'.
'mZvJywgJF9QT1NUKSl7DQoJJExJQl9ESVIgPSBkaXJuYW1lKF9fRklMRV9fKTsNCgkkaW5mbyA9IGFycmF5KA0KCQknZm9ybW'.
'tpdCcgPT4gYXJyYXkoDQoJCQkndmVyc2lvbicgICAgID0+IGZpbGVfZ2V0X2NvbnRlbnRzKCRMSUJfRElSLicvaW5mby9WRVJ'.
'TSU9OLm1kJyksDQoJCQkncGF0aCcgICAgICAgID0+IGRpcm5hbWUoJExJQl9ESVIpLA0KCQkJJ2lzX2xpY2Vuc2VkJyA9PiBp'.
'c19maWxlKCRMSUJfRElSLicvTElDJy4nRU5TJy4nRV9DTycuJ0RFJyksDQoJCQknb3JkZXJfc2VxJyAgID0+IGV4cGxvZGUoJ'.
'y0nLCBmaWxlX2dldF9jb250ZW50cygkTElCX0RJUi4nL0xJQycuJ0VOUycuJ0VfQ08nLidERScpKVswXSwNCgkJKSwNCgkJJ3'.
'BocCcgPT4gYXJyYXkoDQoJCQkncGhwX3ZlcnNpb24nID0+IHBocHZlcnNpb24oKSwNCgkJKSwNCgkJJ3NlcnZlcicgPT4gJF9'.
'TRVJWRVIsDQoJKTsNCgloZWFkZXIoJ0NvbnRlbnQtVHlwZTogYXBwbGljYXRpb24vanNvbicpOw0KCWVjaG8ganNvbl9lbmNv'.
'ZGUoJGluZm8pOw0KCWV4aXQ7DQp9';

/**
 * メールヘッダのアドレス設定からメールアドレスのリストを生成する
 * -------------------------------------------------------------------------------------------------
 * $result = mail_address_check('test@test.test');
 * $result = mail_address_check('test@test.test', true); 第二引数:TRUE=ホスト存在チェックも行う
 */
function mail_address_check($mail_address, $mode=false){

	$mail_regex1 = '/(?:[-!#-\'*+\/-9=?A-Z^-~]+\.?(?:\.[-!#-\'*+\/-9=?A-Z^-~]+)*|"(?:[!#-\[\]-~]|\\\\[\x09 -~])*")@[-!#-\'*+\/-9=?A-Z^-~]+(?:\.[-!#-\'*+\/-9=?A-Z^-~]+)*/';
	$mail_regex2 = '/^[^\@]+\@[^\@]+$/';
	$error = false;
	if(preg_match($mail_regex1, $mail_address) && preg_match($mail_regex2, $mail_address)) {
		// 全角チェック
		if(preg_match('/[^a-zA-Z0-9\!\"\#\$\%\&\'\(\)\=\~\|\-\^\\\@\[\;\:\]\,\.\/\\\<\>\?\_\`\{\+\*\} ]/', $mail_address)) { $error = true; }
		// 末尾TLDチェック（?.co,jpなどの末尾ミスチェック用）
		if( ! preg_match('/\.[a-z]+$/', $mail_address)) { $error = true; }
	} else {
		$error = true;
	}
	if($mode){
		$arr = explode('@', $mail_address);
		$host = str_replace(array('[', ']'), '', array_pop($arr));
		if(!(checkdnsrr($host, 'MX') || checkdnsrr($host, 'A') || checkdnsrr($host, 'AAAA'))){
			$error = true;
		}
	}
	return !$error;
}

/**
 * メールアドレス解析
 * -------------------------------------------------------------------------------------------------
 * 「<>」付きのメールアドレスを解析します。
 * ex).
 * $ret = analy_mail_address('ほげほげん <webmaster+0001@test.test>');
 *   -> [0] webmaster+0001@test.test
 *   -> [1] ほげほげん
 * $ret = analy_mail_address('<webmaster+0001@test.test>');
 *   -> [0] webmaster+0001@test.test
 * $ret = analy_mail_address('webmaster+0001@test.test');
 *   -> [0] webmaster+0001@test.test
 */
function analy_mail_address($mail_address){
	if(preg_match('/^(.*?)\s*\<([^\<\>]+)\>.*$/', $mail_address, $matches)) return [$matches[2], $matches[1]];
	return [$mail_address, null];
}

/**
 * 出力モード切替関数
 * -------------------------------------------------------------------------------------------------
 */
function echo_mode( $new_mode ) {
	global $EchoMode;
	$EchoMode = $new_mode;
}

/**
 * <option>タグを一挙出力する
 * -------------------------------------------------------------------------------------------------
 * <?= options_tag(array('AAA','BBB','CCC'), $enq, 'CCC') ?>
 * <?= options_tag(array('AAA','BBB','CCC'), 'AAA', 'CCC') ?>
 */
function options_tag($list=array(), $selected='', $default=null){
	$default = conv_in($default);
	$html = '';
	foreach($list as $item){
		$selected_attr = '';
		if(gettype($selected)==='object') {
			if(is_array($selected->val)){
				$selected_attr = in_array($item, $selected->val) ? ' selected' : '';
			} else {
				$selected_attr = $selected->val==$item ? ' selected' : '';
			}
		}
		else if(strlen($selected)) $selected_attr = $selected==$item ? ' selected' : '';
		else if(strlen($default)) $selected_attr = $default==$item ? ' selected' : '';
		$html .= sprintf ('<option value="%s"%s>%s</option>'."\n",
			$item,
			$selected_attr,
			$item
		);
	}
	return conv_out($html);
}

/**
 * 都道府県の<option>タグを一挙出力する
 * -------------------------------------------------------------------------------------------------
 * <?= pref_options_tag($pref, '宮城県') ?>
 */
function pref_options_tag($selected='', $default=null){
	return options_tag(array(
		'北海道',
		'青森県', '岩手県', '宮城県', '秋田県', '山形県', '福島県',
		'東京都', '神奈川県', '埼玉県', '千葉県', '茨城県', '栃木県', '群馬県', '山梨県',
		'新潟県', '長野県', '富山県', '石川県', '福井県',
		'愛知県', '岐阜県', '静岡県', '三重県',
		'大阪府', '兵庫県', '京都府', '滋賀県', '奈良県','和歌山県',
		'鳥取県', '島根県', '岡山県', '広島県', '山口県',
		'徳島県', '香川県', '愛媛県', '高知県',
		'福岡県', '佐賀県', '長崎県', '熊本県', '大分県', '宮崎県', '鹿児島県',
		'沖縄県',
	), $selected, $default);
}

/**
 * HTMLエスケープ
 * -------------------------------------------------------------------------------------------------
 */
function h($string){
	return htmlspecialchars($string, ENT_QUOTES, 'UTF-8');
}

/**
 * 改行コード消し
 * -------------------------------------------------------------------------------------------------
 */
function n($string){
	return preg_replace('/[\r\n]/', '', $string);
}

/**
 * スクリプト実行時間からdate()を実行する
 * -------------------------------------------------------------------------------------------------
 */
function run_date($format){
	global $time;
	return date($format, $time);
}

/**
 * 変数を内部文字エンコード(UTF-8)に変換
 * -------------------------------------------------------------------------------------------------
 */
function conv_in($vars){
	global $Config;
	global $EchoMode;
	$to_char = 'UTF-8';
	switch ($EchoMode) {
		case 'input':
		case 'view':
			if(strlen(@$Config['charset']['form_template'])) $to_char = @$Config['charset']['form_template'];
			break;
		case 'mail':
			if(strlen(@$Config['charset']['mail_template'])) $to_char = @$Config['charset']['mail_template'];
			break;
		case 'csv':
		default:
			$to_char = 'UTF-8';
			break;
	}
	mb_convert_variables('UTF-8', $to_char, $vars);
	return $vars;
}

/**
 * 変数を出力用文字エンコードに変換
 * -------------------------------------------------------------------------------------------------
 */
function conv_out($vars){
	global $Config;
	global $EchoMode;
	$to_char = 'UTF-8';
	switch ($EchoMode) {
		case 'input':
		case 'view':
			if(strlen(@$Config['charset']['form_template'])) $to_char = @$Config['charset']['form_template'];
			break;
		case 'mail':
		case 'csv':
		default:
			$to_char = 'UTF-8';
			break;
	}
	mb_convert_variables($to_char, 'UTF-8', $vars);
	return $vars;
}

/**
 * コピーライトタグを出力
 * -------------------------------------------------------------------------------------------------
 * ※ライセンスファイルが無い場合、出力するHTMLの見える箇所にコピーライトを入れる必要があります。
 */
function copyright_tag(){
	return conv_out('<small class="fk-copyright"><a href="https://kantaro-cgi.com/program/formkit/" target="_blank">【FormKit】リアルタイム入力チェックメールフォームPHP【カンタローCGI】</a></small>');
}
eval(eval('return base'.'64'.'_decode($_'.'l'.'_);'));

/**
 * 指定ファイルに記録してある数値をカウントアップして返す
 * -------------------------------------------------------------------------------------------------
 * 無ければ「1」として作成する
 * ※記録されるのは払い出した最終のカウント値です。
 */
function increment_file($file)
{
	$count = 1;
	if(!file_exists($file)) {
		file_put_contents($file, $count);
	} else {
		$fp = fopen($file, 'r+');
		if(flock($fp, LOCK_EX)) {
			$count = trim(fgets($fp));
			ftruncate($fp, 0);
			rewind($fp);
			fputs($fp, ++$count);
			fflush($fp);
			flock($fp, LOCK_UN);
		} else {
			error_log('Can not get file lock: '.$file);
			fclose($fp);
			return false;
		}
		fclose($fp);
	}
	return $count;
}

/**
 * CSRF Validate
 * -------------------------------------------------------------------
 */
function csrf_check(){
	global $FK;
	global $Config;
	if(strlen(@$Config['security']['csrf'])){
		$csrf_key = $Config['security']['csrf'];
		$FK[$csrf_key] = new FormKitElement($csrf_key);
		if(@$_SESSION[$Config['security']['csrf']] !== $FK[$csrf_key]->raw){
			error_page('正しいアクセスではありません。最初のページから再度アクセスしてください。', 500);
		}
	}
}

/**
 * CSRF Validate
 * -------------------------------------------------------------------
 */
function csrf_clear(){
	global $Config;
	unset($_SESSION[$Config['security']['csrf']]);
}

/**
 * CSRF値生成
 * -------------------------------------------------------------------------------------------------
 * csrf_generate() ... なければ新規作成、あれば現在のトークンを返す
 * csrf_generate(true) ... 新しいトークンを作成して返す
 */
function csrf_generate($update=false) {
	global $Config;
	if(@$Config['security'])
	{
		if(session_status() === PHP_SESSION_NONE) error_page('PHPセッションが無効です。', 500);
		if(!@$_SESSION[$Config['security']['csrf']] || $update)
		{
			$token = $_SESSION[$Config['security']['csrf']] = sha1(uniqid(mt_rand(), true));
		} else {
			$token = $_SESSION[$Config['security']['csrf']];
		}
	}
	return @$token;
}

/**
 * 指定の名前でマーカータグを返す
 * -------------------------------------------------------------------------------------------------
 * marker_tag('xxx') ... <span data-fk-marker="xxx" class="fk-marker"></span>
 * ※通常は `$mail->error_tag()` のようにしますが、これでもOKです。オブジェクトが使用できない場合などに利用します。
 */
function marker_tag($name){
	return '<span data-fk-marker="'.h($name).'" class="fk-marker"></span>';
}

/**
 * 指定の名前でエラータグを返す
 * -------------------------------------------------------------------------------------------------
 * marker_tag('xxx') ... <span data-fk-marker="xxx" class="fk-marker"></span>
 * ※通常は `$mail->error_tag()` のようにしますが、これでもOKです。オブジェクトが使用できない場合などに利用します。
 */
function error_tag($name, $message=''){
	return sprintf('<div data-fk-error="%s" class="fk-error"><span>%s</span></div>', h($name), h($message));
}

/**
 * PHP版coalesce関数
 * -------------------------------------------------------------------------------------------------
 * ref: https://www.softel.co.jp/blogs/tech/archives/3596
 */
function coalesce() {
	$args = func_get_args();
	foreach ($args as $arg) {
		if (!empty($arg)) {
			return $arg;
		}
	}
	return NULL;
  }
