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
 * バリデートクラス定義
 * -------------------------------------------------------------------------------------------------
 */
class Validate
{
	/**
	 * メンバ変数
	 * ---------------------------------------------------------------
	 */
	public $validate_list; // バリデータを格納するリスト
	public $validate_list_after; // 最後に実行するバリデータを格納するリスト（主にREQ()）
	public $arguments_list; // このバリデートで渡しているすべての引数（あとでサブバリデートの実行で利用する）

	/**
	 * コンストラクタ
	 * ---------------------------------------------------------------
	 */
	public function __construct($name='') {
		$this->validate_list = array();
		$this->validate_list_after = array();
		$this->arguments_list = array();
	}

	/**
	 * validates/*.php に定義されているメソッドを実行
	 * ---------------------------------------------------------------
	 */
	public function __call($name, $arguments) {

		// リストに追加してゆく
		array_push(
			$this->validate_list,
			call_user_func_array("\\FK\\__$name", $arguments)
		);

		// REQ()は最後にも（もう一度）実行する
		if($name==='REQ') {
			array_push(
				$this->validate_list_after,
				call_user_func_array("\\FK\\__$name", $arguments)
			);
		}

		// 引数を記憶
		if(is_array($arguments)){
			foreach($arguments as $arg){
				if(is_array($arg)) {
					foreach($arg as $ar){
						if(is_array($ar)) continue;
						array_push($this->arguments_list, $ar);
					}
				} else {
					array_push($this->arguments_list, $arg);
				}
			}
		}
		return $this;
	}

	/**
	 * 直接プログラム中などからバリデート用関数を利用したい場合
	 * ---------------------------------------------------------------
	 * $res = EMAIL(1)->run('tt@tt.tt');
	 */
	public function run($indata) {
		$element = new FormKitElement();
		$element->val = $indata;
		return (
			call_user_func(
				$this->validate_list[0],
				$element
			)
		);
	}
}
