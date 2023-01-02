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
 * フォーム要素クラス定義
 * -------------------------------------------------------------------------------------------------
 */
class FormKitElement
{
	/**
	 * メンバ変数
	 * ---------------------------------------------------------------
	 */
	public $name;        // 要素名
	public $raw;         // 最初に入ってきた値。変更されない。
	public $val;         // 出力するための値。バリデートとかで変更されていく。
	public $validated;   // バリデート済みか
	public $error;       // バリデート失敗のエラーメッセージ
	public $is_errorset; // ERRORSET()でエラーメッセージが上書きされたらtrueになる。
	public $is_length;   // LENGTHチェックが実行されたらtrueになる。（要素ごとに1どのみの実行なので）
	public $required;    // 必須項目か否か（バリデート時にREQ()でセットされ、JS側で利用するものです）
	public $file;        // ファイル要素の時にセットされる
	public $bear_echo;   // trueにすると一度だけエスケープ無しで出力する（HTMLタグ出力時など）※出力後、falseに戻る。

	/**
	 * コンストラクタ
	 * ---------------------------------------------------------------
	 */
	public function __construct($name='')
	{
		global $Config;
		$this->name        = $name;
		$this->raw         = clean(@$_REQUEST[$name]);
		$this->val         = $this->raw;
		$this->validated   = false;
		$this->error       = null;
		$this->is_errorset = null;
		$this->required    = null;
		$this->file        = array();
		$this->bear_echo   = false;

		// 一時ファイルの削除指示が来ていれば削除する
		if(
			is_array($this->val)
			&& array_key_exists('delete', $this->val)
			&& array_key_exists('tmp_name', $this->val)
			&& strlen($this->val['delete'])
			&& strlen($this->val['tmp_name'])
		){
			$delete_file = $Config['system']['tmp_dir'] . '/' . $this->val['tmp_name'];
			if(is_file($delete_file)){
				if(unlink($delete_file)){
					$this->raw['org_name'] = '';
					$this->raw['tmp_name'] = '';
					$this->val['org_name'] = '';
					$this->val['tmp_name'] = '';
				}
			}
		}

		// ファイルデータが送られてきたのなら一時ディレクトリに移動
		if(isset($_FILES[$this->name]) && $_FILES[$this->name]['type']){
			// 既存の一時ファイルが存在しているのであれば削除してしまう。（確認画面でリロードを繰り返すと取りこぼしが発生するので、それは別処理で掃除する）
			$old_file = $Config['system']['tmp_dir'] . '/' . $this->val['tmp_name'];
			if(is_file($old_file)) unlink($old_file);

			// 一時ファイル移動
			$tmp_name = tempnam($Config['system']['tmp_dir'], 'fk-') . '.' . pathinfo($_FILES[$this->name]['name'], PATHINFO_EXTENSION);
			move_uploaded_file($_FILES[$this->name]['tmp_name'], $tmp_name);
			unlink(pathinfo($tmp_name, PATHINFO_DIRNAME) . '/' . pathinfo($tmp_name, PATHINFO_FILENAME));
			$this->val = array();
			$this->val['org_name'] = conv_in($_FILES[$this->name]['name']);
			$this->val['tmp_name'] = basename($tmp_name);
		}

		// ファイル情報をfileメンバ変数にセットする
		if(
			is_array($this->val) &&
			array_key_exists('org_name', $this->val) &&
			(
				array_key_exists('tmp_name', $this->val) ||
				array_key_exists('local_name', $this->val)
			)
		){
			$this->file = array();
			// 一時ファイル名から危険文字を除去
			if(strlen($this->val['tmp_name'])) {
				$this->val['tmp_name'] = preg_replace('/[^0-9A-Za-z\-\_\.]/', '', $this->val['tmp_name']);
			}
			// Ajaxでの事前チェック。ファイルタイプやサイズはFileAPIにてクライアント側JSで取得・送信する。
			if (array_key_exists('local_name', $this->val) && strlen($this->val['local_name'])) {
				$this->file['org_name']  = basename($this->val['local_name']);
				$this->file['mime_type'] = $this->val['mime_type'];
				$this->file['file_size'] = $this->val['file_size'];
				$this->file['width']     = @$this->val['width'];
				$this->file['height']    = @$this->val['height'];
				$this->val = 'file';
			}
			// 一時ファイルがある場合
			else if(array_key_exists('tmp_name', $this->val) && is_file($Config['system']['tmp_dir'] . '/' . $this->val['tmp_name'])) {
				$this->file['org_name']  = $this->val['org_name'];
				$this->file['tmp_name']  = $this->val['tmp_name'];
				$this->file['mime_type'] = mime_content_type($Config['system']['tmp_dir'] . '/' . $this->val['tmp_name']);
				$this->file['file_size'] = filesize($Config['system']['tmp_dir'] . '/' . $this->val['tmp_name']);
				list(
					$this->file['width'],
					$this->file['height']
				) = getimagesize($Config['system']['tmp_dir'] . '/' . $this->file['tmp_name']);
				$this->val = 'file';
			}
			// 一時ファイル消えてる可能性があるので、エラー
			else {
				$this->val = null;
			}
		}
	}

	/**
	 * 存在しないメソッドの場合、外部ライブラリの方を実行
	 * ---------------------------------------------------------------
	 */
	public function __call($name, $arguments) {
		if(function_exists("\\FK\\__$name")){
			$new_this = call_user_func_array("\\FK\\__$name", array_merge([clone $this], $arguments));
		} else {
			error_page('メソッドがありません: '.$name, 500);
		}
		return $new_this;
	}

	/**
	 * 値を出力する
	 * ---------------------------------------------------------------
	 */
	public function __toString()
	{
		if(is_array($this->val)) {
			return $this->join()->val;
		} else if ($this->bear_echo) {
			$this->bear_echo = false;
			return conv_out($this->val);
		} else if (gettype($this->val)!=='object') {
			return trans($this->val);
		}
		return '';
	}

	/**
	 * 該当要素のバリデートを実行する
	 * ---------------------------------------------------------------
	 */
	public function validate($force=false)
	{
		global $Config;
		global $FK;


		// 既にバリデート完了している場合は実行しない（但し引数がtrueなら強制実行する）
		if((!$force) && $this->validated) return true;

		// バリデート完了チェックを最初にいれてしまう（じゃないと、内部でループしだす場合があるので）
		$this->validated = true;

		// 循環してるっぽい場合は強制的に打ち止めする
		if(@count(debug_backtrace())>10){
			error_log("Formkit Error: Element {$this->name} may have validate infinite loop.");
			return true;
		}

		// バリデート実行
		$valids = @$Config['validate']['list'][$this->name];
		if(gettype(@$valids)=='object'){
			$error = false;

			// サブバリデートの場合、バリデートで参照する要素の値をセットする
			if(preg_match('/^_/', $this->name)) {
				$book = array();
				foreach($valids->arguments_list as $arg){
					if(@array_key_exists($arg, $FK)){
						@$book[$arg] ++;
					}
				}
				foreach($book as $key=>$value){
					$this->val .= $FK[$key]->val;
				}
			}

			// 設定ファイルに記述された、全ての要素に最初に実行する共通メソッドを実行
			if(@$Config['validate']['before']){
				foreach($Config['validate']['before']->validate_list as $func) {
					if(!$func($this)) $error = true;
				}
			}

			// メインのバリデータリストを実行
			foreach($valids->validate_list as $func) {
				if(!$func($this)) $error = true;
			}

			// 設定ファイルに記述された、全ての要素に最後に実行する共通メソッドを実行
			if(@$Config['validate']['after']){
				foreach($Config['validate']['after']->validate_list as $func) {
					if(!$func($this)) $error = true;
				}
			}

			// 最後に実行するバリデータリストを実行（主にREQ()）
			if($this->required && @count($valids->validate_list_after)) {
				foreach($valids->validate_list_after as $func) {
					if(!$func($this)) $error = true;
				}
			}

			// 必須じゃないものは、空でもOKにする。但しサブバリデートの場合は無視
			if($this->is_empty() && !$this->required && !preg_match('/^_/', $this->name)){
				$error = false;
				$this->error = '';
			}

			// エラー処理
			if($error) {
				if(!$this->error) $this->error = '入力された値が正しくありません。'; // 標準エラーメッセージ（バリデート関数側で指定されなかった場合にこれが設定される）
				return false;
			}
		}

		return true;
	}

	/**
	 * 該当要素が空白・未選択状態かどうかを返す。
	 * ---------------------------------------------------------------
	 */
	public function is_empty()
	{
		$check_string = '';
		if(is_array($this->val)){
			if(array_key_exists('local_name', $this->file) || array_key_exists('tmp_name', $this->file)){
				// ファイル要素の場合
				$check_string = $this->file['local_name']
					? $this->file['local_name']
					: $this->file['tmp_name']
				;
			} else {
				// 複数選択要素の場合
				$check_string = join('', $this->val);
			}
		} else {
			// 単数要素の場合
			$check_string = (string)$this->val;
		}
		return strlen($check_string) ? false : true;
	}

	/**
	 * エラーメッセージをセットします。
	 * ---------------------------------------------------------------
	 * エラーがまだセットされていない場合にのみ、セットします。
	 */
	public function error_set($message)
	{
		if(!strlen($this->error)) $this->error = $message;
		return @$element->error;
	}

}
