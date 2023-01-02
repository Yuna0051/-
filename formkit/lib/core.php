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
 * PHP init
 * -------------------------------------------------------------------
 */
ini_set('display_startup_errors', 0);
mb_internal_encoding('UTF-8');
mb_language('japanese');
date_default_timezone_set('Asia/Tokyo');

/**
 * global variables
 * -------------------------------------------------------------------
 */
global $Config;     // 設定ファイル
global $EchoMode;   // 出力モード（input|view|mail|csv）
global $FK;         // フォーム要素オブジェクト
global $Special;    // 特殊変数
global $ErrorNames; // フォーム要素オブジェクト
global $time;       // スクリプト実行時間をセット
$time = time();

if(@count($ErrorNames)) return;

/**
 * load requires
 * -------------------------------------------------------------------
 */
define('FK_DIR', dirname(dirname(__FILE__)));
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;
require_once FK_DIR.'/lib/PHPMailer/src/Exception.php';
require_once FK_DIR.'/lib/PHPMailer/src/PHPMailer.php';
require_once FK_DIR.'/lib/PHPMailer/src/SMTP.php';
require_once FK_DIR.'/lib/FormKitElement.php';
require_once FK_DIR.'/lib/Validate.php';
foreach
(
	array_merge(
		glob(FK_DIR.'/lib/template-methods/*.php'),
		glob(FK_DIR.'/lib/validate-methods/*.php')
	) as $file
) {
	if(!preg_match('/^_/', basename($file))) require_once $file;
}
require_once FK_DIR.'/lib/helpers.php';

/**
 * 特殊変数セット
 * -------------------------------------------------------------------
 */
$Special['__DATE__']   = run_date('Y-m-d');
$Special['__TIME__']   = run_date('H:i:s');
$Special['__DATEJP__'] = run_date('Y年m月d日');
$Special['__TIMEJP__'] = run_date('H時i分s秒');
$Special['__UA__']     = getenv('HTTP_USER_AGENT');
$Special['__IP__']     = coalesce(getenv('HTTP_X_REAL_IP'), getenv('HTTP_X_FORWARDED_FOR'), getenv('REMOTE_ADDR'));
$Special['__HOST__']   = gethostbyaddr($Special['__IP__']);

/**
 * keep the before config
 * -------------------------------------------------------------------
 */
if(@count($Config)) $before_Config = $Config;

/**
 * load config file
 * -------------------------------------------------------------------
 */
if(@$_REQUEST['fk_config_dir']) define('CONFIG_DIR', FK_DIR . '/' . $_REQUEST['fk_config_dir']);
if(!defined('CONFIG_DIR')) define('CONFIG_DIR', FK_DIR);
require_once CONFIG_DIR.'/config.php';

/**
 * PHP version check
 * -------------------------------------------------------------------
 */
if(!@$Config['php_version_no_check']) {
	if(version_compare(PHP_VERSION, '5.3.0', '<')){
		error_page('このフォームプログラムはPHPバージョン 5.3 未満では動作しません。（PHP '.PHP_VERSION.'）', 500);
	}
}

/**
 * auto http->https redirect
 * -------------------------------------------------------------------
 */
if(@$Config['security']['ssl_redirect']) {
	if(empty($_SERVER['HTTPS']) && empty($_SERVER['HTTP_X_FORWARDED_HTTPS'])) {
		header("Location: https://{$_SERVER['HTTP_HOST']}{$_SERVER['REQUEST_URI']}");
		exit;
	}
}

/**
 * session start
 * -------------------------------------------------------------------
 */
ini_set('session.cookie_httponly', true);
if(@$Config['security'] && $Config['security']['ssl_cookie']) {
	ini_set('session.cookie_secure', true);
	if($Config['security']['csrf'] && !(isset($_SERVER['HTTPS']) || isset($_SERVER['HTTP_X_FORWARDED_HTTPS']))) {
		error_page('セキュリティ設定にてSSL及びCSRFが有効になっているため、HTTPでのアクセスは禁止されています。', 500);
	}
}
session_cache_limiter('private_no_expire');
@session_start();

/**
 * set other system default
 * -------------------------------------------------------------------
 */
if(!isset($Config['system']['tmp_dir'])) $Config['system']['tmp_dir'] = sys_get_temp_dir();
if(!isset($Config['charset']['form_template'])) $Config['charset']['form_template'] = 'UTF-8';
if(!isset($Config['charset']['mail_template'])) $Config['charset']['mail_template'] = 'UTF-8';
if(!isset($Config['charset']['csv_output'])) $Config['charset']['csv_output'] = 'SJIS-win';
if(!isset($Config['empty_label'])) $Config['empty_label'] = '<span class="fk-empty-label">（未入力）</span>';
if(!isset($Config['hiddens_vars_key'])) $Config['hiddens_vars_key'] = '__vars';
header('Content-Type: text/html; charset='.$Config['charset']['form_template']);

/**
 * overwrite the before config
 * -------------------------------------------------------------------
 */
if(isset($before_Config)) $Config = array_merge($Config, $before_Config);

/**
 * Cache disable
 * -------------------------------------------------------------------
 */
if(!@$Config['security']['no_cache_headers']===false){
	header('Pragma: no-cache');
	header('Cache-Control: no-cache,no-store');
	header('Expires: Thu, 01 Dec 1994 16:00:00 GMT');
}

/**
 * Security headers
 * -------------------------------------------------------------------
 */
 if(@$Config['security']['security_header_xss_protection'] || @$Config['security']['security_headers']){
	header('X-XSS-Protection: 1; mode=block');
}
if(@$Config['security']['security_header_frame_options'] || @$Config['security']['security_headers']){
	header('X-Frame-Options: SAMEORIGIN');
}
if(@$Config['security']['security_header_content_type_options'] || @$Config['security']['security_headers']){
	header('X-Content-Type-Options: nosniff');
}

/**
 * Ajax return keys
 * -------------------------------------------------------------------
 * 項目キー返却Ajax通信
 */
if(
	@$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest'
	&& $_SERVER['REQUEST_METHOD'] === 'POST'
	&& getenv('PATH_INFO') == '/keys/'
)
{
	header('Content-type: application/json');
	echo json_encode(array('keys'=>array_keys(@$Config['validate']['list'])));
	exit;
}

/**
 * uploaded file preview
 * -------------------------------------------------------------------
 * URLクエリに `pv` が入ってくる場合は一時ファイルを出力して終了
 */
if(isset($_GET['pv']) && strlen($_GET['pv'])){
	$uploaded_file = $Config['system']['tmp_dir'] . '/' . $_GET['pv'];
	header('Content-type: '.mime_content_type($uploaded_file));
	readfile($uploaded_file);
	exit;
}

/**
 * check config
 * -------------------------------------------------------------------
 * 必要ファイルが足りない、及び設定ファイルが正しくない可能性がある場合はエラー（メールアドレスがおかしい等）
 */

/* --- PHP設定のアップロード容量を超えた設定になっていないか --- */
{
	$upload_max_filesize = trim(ini_get('upload_max_filesize'));
	$unit = strtolower($upload_max_filesize[strlen($upload_max_filesize)-1]);
	$upload_max_filesize = preg_replace('/\D/', '', $upload_max_filesize);
	switch($unit) {
		case 'g': $upload_max_filesize *= 1024 * 1024 * 1024; break;
		case 'm': $upload_max_filesize *= 1024 * 1024; break;
		case 'k': $upload_max_filesize *= 1024; break;
	}
	if(@$Config['max_limit_byte'] > $upload_max_filesize){
		error_page(
			'アップロードできるファイルサイズ上限が、PHPのサーバ設定値を超えています。<br>PHP側の許容量またはフォーム側の設定ファイルを調整してください。',
			403,
			'[PHP] upload_max_filesize => '.ini_get('upload_max_filesize')
		);
	}
}
/* --- メール設定がおかしくないか（簡易的な事前チェック） --- */
if(is_array(@$Config['mails'])) {
	foreach($Config['mails'] as $mail){
		# // メールテンプレートファイルの指定に変数が入っている場合はチェックしない
		if(preg_match('/\{\$[^\}]+\}/', $mail['template'])) continue;
		# // メールテンプレートファイルは存在しているか
		$mail_template = (preg_match('/^\//', $mail['template'])) ? $mail['template'] : CONFIG_DIR.'/'.$mail['template'];
		if(!is_file($mail_template)){
			error_page('メールテンプレートファイルが見つかりません。', 403, '[MAIL] template => '.$mail_template);
		}
		# // ちゃんと値が入っているか
		foreach(['subject', 'from', 'to'] as $key){
			if(!(is_array($mail[$key]) || strlen($mail[$key]))) {
				error_page('メール設定が正しくない箇所があります。', 403, '[MAIL] '.$key);
			}
		}
		# // メールアドレスは正しいものか
		foreach(['to', 'cc', 'bcc', 'reply_to'] as $key){
			if(isset($mail[$key])){
				$set = is_array($mail[$key]) ? $mail[$key] : [$mail[$key]];
				foreach($set as $key=>$value){
					$mail_address = preg_match('/^\d+$/', $key) ? $value : $key;
					if(!preg_match('/\{\$/', $mail_address)){ // 「{$」が含まれる場合は変数なのでチェック対象としない
						$mail_analy = analy_mail_address($mail_address);
						if($mail_analy[0] && !mail_address_check($mail_analy[0])){
							error_page('メールアドレスが正しくない箇所があります。', 403, '[MAIL] '.$mail_address);
						}
					}
				}
			}
		}
	}
}

/**
 * check csv directory
 * -------------------------------------------------------------------
 * CSVディレクトリが書き込み可能かチェック
 */
if(isset($Config['csv']) && strlen(@$Config['csv']['file']))
{
	// フルパスに変換
	if(!preg_match('/^\//', $Config['csv']['file'])) {
		$Config['csv']['file'] = CONFIG_DIR . '/' . $Config['csv']['file'];
	}

	// ディレクトリが存在しなければ作成
	$csvdir = dirname($Config['csv']['file']);
	if(!file_exists($csvdir)) {
		mkdir($csvdir);
	}

	// 書き込み権限が無ければエラー
	if(!is_writable($csvdir)) {
		error_page("CSVが書き込めるディレクトリを設定してください。 : $csvdir");
	}

	// CSV保存ディレクトリにアクセス禁止の.htaccessを作成（既に.htaccessがあれば何もしない。）
	if(@$Config['csv']['dir_deny']){
		if(!is_file("$csvdir/.htaccess")) {
			file_put_contents("$csvdir/.htaccess", "Deny from all\n");
		}
	}
}

/**
 * check count directory
 * -------------------------------------------------------------------
 * countディレクトリが書き込み可能かチェック
 */
if(isset($Config['count']) && strlen(@$Config['count']['file']))
{
	// フルパスに変換
	if(!preg_match('/^\//', $Config['count']['file'])) {
		$Config['count']['file'] = CONFIG_DIR . '/' . $Config['count']['file'];
	}

	// ディレクトリが存在しなければ作成
	$count = dirname($Config['count']['file']);
	if(!file_exists($count)) {
		mkdir($count);
	}

	// 書き込み権限が無ければエラー
	if(!is_writable($count)) {
		error_page("CSVが書き込めるディレクトリを設定してください。 : $count");
	}

	// CSV保存ディレクトリにアクセス禁止の.htaccessを作成（既に.htaccessがあれば何もしない。）
	if(@$Config['count']['dir_deny']){
		if(!is_file("$count/.htaccess")) {
			file_put_contents("$count/.htaccess", "Deny from all\n");
		}
	}
}

/**
 * delete old upfiles
 * -------------------------------------------------------------------
 * 一時ディレクトリに保存されたファイルの古いものを削除
 */
$del_expire = strtotime('24 hours ago');
foreach (glob($Config['system']['tmp_dir'].'/fk-*') as $filename) {
	if(filemtime($filename)<$del_expire){
		unlink($filename);
	}
}

/**
 * Ajax通信(POST)かどうかのチェック
 * -------------------------------------------------------------------
 */
$is_ajax = (
	@$_SERVER['HTTP_X_REQUESTED_WITH'] === 'XMLHttpRequest' &&
	$_SERVER['REQUEST_METHOD'] === 'POST'
);

/**
 * GET/POSTデータの文字エンコードを内部エンコード(UTF-8)に合わせる
 * -------------------------------------------------------------------
 */
if(
    strlen(@$Config['charset']['form_template'])
    && (!preg_match('/utf\-?8/i', $Config['charset']['form_template'])) # UTF-8以外の時のみ実行
    && (!$is_ajax)
) {
    // PHPバグ回避用関数：http://tekitoh-memdhoi.info/views/768
    foreach($_GET     as &$data) { mb_convert_variables('UTF-8', $Config['charset']['form_template'], $data); }
    foreach($_POST    as &$data) { mb_convert_variables('UTF-8', $Config['charset']['form_template'], $data); }
    foreach($_REQUEST as &$data) { mb_convert_variables('UTF-8', $Config['charset']['form_template'], $data); }
    foreach($_FILES   as &$data) { mb_convert_variables('UTF-8', $Config['charset']['form_template'], $data); }
}

/**
 * シリアライズ化したフォームデータを戻す
 * -------------------------------------------------------------------
 */
if(strlen(@$_POST[$Config['hiddens_vars_key']])) {
	// ラジオ未選択などの場合は$_POSTに入ってこないので$_POST['__parts]にセットされているキーを存在するものにする
	if(isset($_POST['__parts'])) {
		$parts = explode(',', preg_replace('/[\[\]]/', '', $_POST['__parts']));
		foreach($parts as $key) {
			if(!isset($_POST[$key])) $_POST[$key] = ''; // 存在だけさせる
		}
	}

	// 保管してたシリアルデータを$_POSTに戻す（POST値が入ってきてるものはそれ優先）
	foreach(unserialize($_POST[$Config['hiddens_vars_key']]) as $key=>$value){
		if(!isset($_POST[$key])) $_POST[$key] = $_REQUEST[$key] = $value;
	}
}

/**
 * create formkit element object
 * -------------------------------------------------------------------
 * フォーム項目ごとにオブジェクトを作成する（許可された項目名のみ対象）
 */
$FK = array();
	foreach(array_keys($Config['validate']['list']) as $key){
		$FK[$key] = new FormKitElement($key);
	}
extract($FK);
extract($Special);
ob_start('__FK');

/**
 * Ajax validates
 * -------------------------------------------------------------------
 * バリデートAjax通信
 */
if($is_ajax && getenv('PATH_INFO') == '/validate/') {
	$error_count = 0;
	$elements = array();
	// バリデート該当の項目名を取得（__applyに列挙された項目名のみ）
	$keys = array();
	$post_keys = preg_split('/\,\s*/', $_POST['__apply']);
	rsort($post_keys); // アンダーバーから始まるグループ要素は後回しにしないとおかしくなるため
	foreach($post_keys as $key){
		if(isset($FK[$key]) && gettype($FK[$key])=='object'){
			array_push($keys, $key);
		}
	}

	// バリデートを実施する
	$old_values = array();
	foreach($keys as $key) {
		$old_values[$key] = $FK[$key]->raw;
		if(!$FK[$key]->validate()) $error_count ++;
	}

	// 返却する要素をまとめる（__apply指定）
	foreach(preg_split('/\s*\,\s*/', $_POST['__apply']) as $name){
		$applies[$name] = true;
	}
	$applies = array_keys($applies);

	// バリデート結果をまとめる
	foreach(array_unique(array_merge($keys, $applies)) as $key) {
		$status = '';
		if(@$FK[$key]){
			if(strlen($FK[$key]->error)) {
				$status = 'ng';
			} else {
				$status = 'ok';
				// 値が空で入力が任意のものはスタイル与えない。
				if($FK[$key]->is_empty() && !$FK[$key]->required){
					$status = '';
				}
			}
		}
		if(in_array($key, $applies)){ // applyに列挙されてきた項目のみ返却する
			$elements[$key] = array(
				'value'     => @$FK[$key]->val,
				'old_value' => @$old_values[$key],
				'error'     => @$FK[$key]->error,
				'status'    => @$status,
				'required'  => @$FK[$key]->required,
			);
		}
	}

// usleep(250000);
// usleep(1000000);

	// JSON返却
	header('Content-type: application/json');
	echo json_encode(array(
		'elements' => $elements,
		'error_count' => $error_count,
	));
	exit;
}

/**
 * PHP validates
 * -------------------------------------------------------------------
 * PHP側バリデート（失敗時は400 Bad Request）
 * ex. validation();     # 入ってきた$_POSTの要素のみすべてバリデートする。
 * ex. validation(true); # 設定で定義されている要素すべてをバリデートする。
 * ex. validation('shimei,kana'); # 指定された要素名のみバリデートする。
 * ex. validation(['shimei','kana']); # 上と同じ
 *
 * ※基本的には$_POSTに入ってきた要素のみバリデートを行います。
 *   その為、fk-check.phpとfk-send.phpでのみ全てのバリデートを実施して
 *   いますが、ご希望なら入力ページでも画面表示前に事前にPHP側バリデート
 *   可能です。その場合は、fk-input.phpロード前に
 *   $Config['before_validate']='onamae,age'
 *   などとすることで、画面表示前に指定要素のみバリデートチェックが可能です。
 */
function validation($validates=null) {
	global $Config;
	global $FK;
	$ErrorNames = array();
	$sets = array();
	if($validates===null){
		// 引数が null の場合は$_POSTに入ってきた要素のみ対象
		foreach(@$Config['validate']['list'] as $key=>$value){
			if(isset($_POST[$key])) $sets[$key] = $value;
		}
	}
	if($validates===true){
		// 引数が true の場合は定義された要素すべて対象
		$sets = @$Config['validate']['list'];
	} else if(strlen($validates)) {
		// 引数が文字列なら指定の要素名のみ対象
		foreach(preg_split('/\W+/', $validates) as $key){
			$sets[$key] = @$Config['validate']['list'][$key];
		}
	} else if(is_array($validates)) {
		// 引数が配列なら指定の要素名のみ対象
		foreach($validates as $key){
			$sets[$key] = @$Config['validate']['list'][$key];
		}
	}
	foreach(array_keys($sets) as $key) {
		if(!$FK[$key]->validate()) $ErrorNames[] = $key;
	}
	if(@count($ErrorNames))
	{
		// PHP側の再入力画面表示が指定されていた場合
		if(isset($Config['reinput_url']) && strlen($Config['reinput_url']))
		{
			// 項目オブジェクトをセッションに記録して、再入力画面にジャンプ
			$_SESSION['FK'] = serialize($FK);
			$_SESSION['FK_ERROR_COUNT'] = @count($ErrorNames);
			header("Location: {$Config['reinput_url']}?reinput");
			exit;
		}

		// 上記以外なら202エラーを表示（再入力画面へのボタン付き）
		foreach($ErrorNames as $key){
			unset($FK[$key]); // エラーとなっている要素をクリアにする（じゃないと、再入力画面を表示する前にまたエラー画面になっちゃうので）
		}
		$action = preg_replace('/\?.*$/', '', $_SERVER['HTTP_REFERER']).'?revalidate';
		error_page(
			'正しいデータが入力されませんでした。<br>'.
			'（この画面はJavaScriptチェックが無効、または再入力画面が指定されていない場合に表示されます。）<br>'.
			($action?
				"<form action=\"$action\" method=\"post\" enctype=\"multipart/form-data\">".
				hiddens_tag().
				"<input type=\"submit\" value=\"再入力する\"></form>"
			:''),
			403,
			$ErrorNames
		);
	}
}

/**
 * reinput
 * -------------------------------------------------------------------
 * 再入力画面表示
 * ※セッションに記憶した全ての項目オブジェクトをロードする
 * ※$Config['reinput_url']に「?reinput」が付いたURLになります。
 */
if(isset($_GET['reinput'])){
	if(isset($_SESSION['FK'])){
		$FK = unserialize($_SESSION['FK']);
		$FK_ERROR_COUNT = $_SESSION['FK_ERROR_COUNT'];
		extract($FK);
	}
} else {
	// 別のページに遷移したらセッションから削除
	unset($_SESSION['FK']);
}

/**
 * Mail sending
 * -------------------------------------------------------------------
 * メール送信処理
 */
if(!function_exists('submit'))
{
	function submit(){

		# -- グローバル変数
		global $Config;
		global $EchoMode;
		global $FK;
		global $Special;

		# -- UA正規表現チェック
		if(@$Config['security']['ua_deny_regex'])
		{
			if(preg_match($Config['security']['ua_deny_regex'], getenv('HTTP_USER_AGENT')))
			{
				error_page('許可されていないブラウザからのアクセスです。', 403);
			}
		}

		# -- マルチバイト文字が存在しているかチェック
		if(@$Config['security']['need_multibyte'])
		{
			if(!preg_match('/[^\x01-\x7E]/', $_POST['__vars']))
			{
				error_page('入力データに全角文字が含まれていません。', 403);
			}
		}

		# -- IPチェック
		if(@count(@$Config['security']['deny_ips']))
		{
			$hit = 0;
			foreach($Config['security']['deny_ips'] as $ip)
			{
				if($ip==$Special['__IP__']) $hit ++;
			}
			if($hit) error_page('許可されていないIPからのアクセスです。', 403);
		}

		# -- 連続送信チェック
		if(@$Config['security']['sendable_latency_sec'])
		{
			# -- ディレクトリ用意
			$dir = sys_get_temp_dir() .  '/fk-sent-ips';
			if(!file_exists($dir)) mkdir($dir);
			$check_file = $dir . '/' . $Special['__IP__'];
			# -- チェック
			if(file_exists($check_file))
			{
				$elapsed_time = time() - filemtime($check_file);
				if($elapsed_time <= $Config['security']['sendable_latency_sec']){
					error_page('送信タイミングが早すぎます。しばらく経ってから再度お試しください。', 403);
				} else {
					unlink($check_file);
				}
			}
			# -- ファイル作成
			touch($check_file);
		}

		# -- 送信処理
		extract($FK);
		if($_SERVER['REQUEST_METHOD'] === 'POST')
		{
			# -- カウント更新
			if(isset($Config['count']) && @$Config['count']['file']) {
				$count = increment_file($Config['count']['file']);
				if($count===false){
					error_page('カウンタファイルの操作に失敗しました。');
				}
				# -- 特殊変数にセット
				$Special['__COUNT__']  = @$count;
			}

			// 特殊変数展開
			$FK = array_merge($FK, $Special);
			extract($FK); // 再展開

			# -- CSV保存
			if(isset($Config['csv']) && @$Config['csv']['file'])
			{
				echo_mode('csv');

				# -- CSVヘッダ行用意
				$lines = array();
				if(!file_exists($Config['csv']['file'])) {
					array_push($lines, array_keys($Config['csv']['list']));
				}

				# -- データ行を用意してファイルに記録
				$cols = replace_values($Config['csv']['list'], $FK);
				array_push($lines, $cols);
				if(@$Config['csv']['sub_lists']){
					foreach($Config['csv']['sub_lists'] as $list){
						$cols = replace_values($list, $FK);
						array_push($lines, $cols);
					}
				}
				if(! file_put_contents($Config['csv']['file'], arr2csv($lines, $Config['charset']['csv_output']), FILE_APPEND|LOCK_EX)){
					error_page('CSVへの書き込みに失敗しました。');
				}
			}

			# -- メール送信モードに切り替え
			echo_mode('mail');

			# -- 各種メールを処理
			$keep_template_charset = @$Config['charset']['mail_template'];
			if(@$Config['mails']) {
				foreach(@$Config['mails'] as $mail)
				{
					# -- From,To情報の準備（変数文字列を実データに書き換え）
					$mail = replace_values($mail, $FK);

					# -- メール本文用意（<?php～>が行末にあっても改行コードを見た目のまま残す処理をしている）
					if(preg_match('/^\//', $mail['template']))
					{
						$mail_file = $mail['template'];
					} else {
						$mail_file = CONFIG_DIR.'/'.$mail['template'];
					}
					if(!file_exists($mail_file)) error_page('メールテンプレートファイルが見つかりません。', 403, '[MAIL] template => '.$mail_file);
					$tmpfile = tempnam(sys_get_temp_dir(), 'fk-');
					file_put_contents($tmpfile,
						preg_replace(
							'/[^\#]\?\>\r?/m' , // 「＜?= ～ ?＞」→改行入る        ＜?= ～ #?＞→改行入らない（PHP標準の挙動）
							"?>\n" ,
							conv_in(file_get_contents($mail_file)) # UTF-8に変換して保存
						)
					);
					$Config['charset']['mail_template'] = 'UTF-8'; # 上記でテンプレートをUTF-8に変換したので切り替え。
					ob_start();
					include $tmpfile;
					$body = ob_get_clean();
					unlink($tmpfile);
					$to_charset = @$mail['is_utf8'] ? 'UTF-8' : 'ISO-2022-JP-MS';

					# -- メール送信準備
					$mailer = new PHPMailer();
					$mailer->Subject          = mb_encode_mimeheader($mail['subject'], $to_charset);
					$mailer->Encoding         = '7bit';
					$mailer->CharSet          = $to_charset;
					$mailer->CharSetForHeader = @$mail['is_utf8'] ? 'UTF-8' : 'ISO-2022-JP'; // メールヘッダには「ISO-2022-JP-MS」の「-MS」を外したいので。
					$mailer->Body             = $to_charset==='UTF-8' ? $body : mb_convert_encoding($body, $to_charset, 'UTF-8');
					if(isset($mail['is_html'])) {
						$mailer->isHTML($mail['is_html']);
						if($mail['is_html']) $mailer->AltBody = strip_tags($mailer->Body);
					}

					# -- Fromセット
					list($mail_address, $mail_label) = analy_mail_address($mail['from']);
					if(strlen($mail_label)){
						$mailer->setFrom(
							$mail_address,
							mb_encode_mimeheader($mail_label, 'UTF-8')
						);
					} else {
						$mailer->setFrom($mail_address);
					}

					# -- Toセット
					if($mail['to']){
						$to_list = is_array($mail['to']) ? $mail['to'] : [$mail['to']];
						foreach($to_list as $to){
							list($mail_address, $mail_label) = analy_mail_address($to);
							if(strlen($mail_label)){
								$mailer->addAddress(
									$mail_address,
									mb_encode_mimeheader($mail_label, 'UTF-8')
								);
							} else {
								$mailer->addAddress($mail_address);
							}
						}
					}

					# -- CCセット
					if(@$mail['cc']){
						$cc_list = is_array($mail['cc']) ? $mail['cc'] : [$mail['cc']];
						foreach($cc_list as $cc){
							list($mail_address, $mail_label) = analy_mail_address($cc);
							if(strlen($mail_label)){
								$mailer->addCC(
									$mail_address,
									mb_encode_mimeheader($mail_label, 'UTF-8')
								);
							} else {
								$mailer->addCC($mail_address);
							}
						}
					}

					# -- BCCセット
					if(@$mail['bcc']){
						$bcc_list = is_array($mail['bcc']) ? $mail['bcc'] : [$mail['bcc']];
						foreach($bcc_list as $bcc){
							list($mail_address, $mail_label) = analy_mail_address($bcc);
							if(strlen($mail_label)){
								$mailer->addBCC(
									$mail_address,
									mb_encode_mimeheader($mail_label, 'UTF-8')
								);
							} else {
								$mailer->addBCC($mail_address);
							}
						}
					}

					# -- Reply-Toセット
					if(@$mail['reply_to']){
						$reply_list = is_array($mail['reply_to']) ? $mail['reply_to'] : [$mail['reply_to']];
						foreach($reply_list as $reply_to){
							list($mail_address, $mail_label) = analy_mail_address($reply_to);
							if(strlen($mail_label)){
								$mailer->AddReplyTo(
									$mail_address,
									mb_encode_mimeheader($mail_label, 'UTF-8')
								);
							} else {
								$mailer->AddReplyTo($mail_address);
							}
						}
					}

					# -- アップロードしたファイルを添付する
					if(@$mail['attach_upfile']){
						foreach($FK as $key=>$value) {
							if(isset($value->file) && array_key_exists('tmp_name', $value->file)) {
								$mailer->addAttachment(
									$Config['system']['tmp_dir'] . '/' . $value->file['tmp_name'],
									mb_encode_mimeheader($value->file['org_name'], 'UTF-8')
								);
							}
						}
					}

					# -- SMTP送信モード
					if(@$mail['is_smtp']){
						$mailer->IsSMTP();
						$mailer->SMTPAuth   = $mail['smtp_auth'];
						$mailer->Host       = $mail['smtp_host'];
						$mailer->Port       = $mail['smtp_port'];
						$mailer->Username   = $mail['smtp_user'];
						$mailer->Password   = $mail['smtp_pass'];
						$mailer->SMTPSecure = $mail['smtp_secure'];
					}

					# -- メール送信（DEMOモードならしない）
					if(!is_file(FK_DIR.'/DEMO') || !is_file(CONFIG_DIR.'/DEMO'))
						$res = $mailer->send();

					# -- メールテンプレートの文字エンコードを戻しておく
					$Config['charset']['mail_template'] = $keep_template_charset;
				}
			}

			# -- 添付ファイル削除
			foreach($FK as $key=>$value) {
				if(isset($value->file) && array_key_exists('tmp_name', $value->file)) {
					unlink($Config['system']['tmp_dir'] . '/' . $value->file['tmp_name']);
				}
			}
		}
	}
}
