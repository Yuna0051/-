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
 * サブバリデートメソッド定義
 * -------------------------------------------------------------------------------------------------
 * 複数の要素を使って新たなバリデートを定義するものです。
 */

/**
 * [NONG]
 * -------------------------------------------------------------------
 * 指定要素にエラー状態がひとつもないないかどうか
 * ex. NONG('zip1','zip2')
 * ※エラーメッセージをまとめるのに使用可能
 * ※すべて未入力状態かOK状態ならtrue、エラー状態が1つ以上あればfalse
 */
function NONG(){ return (new Validate())->NONG(func_get_args()); }
function __NONG()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		global $FK;
		$error_count = 0;
		foreach($args as $key){
			if($element->name!=$key) $FK[$key]->validate(); // 対象の要素を先にバリデートしておく（万が一自分の要素名が入ってたら無視）
			if(strlen($FK[$key]->error)) $error_count ++;
		}
		if($error_count) {
			$element->error_set('入力にミスがあります。');
			return false;
		}
		return true;
	};
}

/**
 * [ALLOK]
 * -------------------------------------------------------------------
 * 指定要素が全てOK状態であるかどうか
 * ex. ALLOK('zip1','zip2')
 * ※エラーメッセージをまとめるのに使用可能
 * ※すべてOK状態ならtrue、エラー状態が1つ以上あればfalse
 */
function ALLOK(){ return (new Validate())->ALLOK(func_get_args()); }
function __ALLOK()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		global $FK;
		$out_count = 0;
		foreach($args as $key){
			if($element->name!=$key) $FK[$key]->validate(); // 対象の要素を先にバリデートしておく（万が一自分の要素名が入ってたら無視）
			if(strlen($FK[$key]->error)) $out_count ++;
		}
		if($out_count) {
			$element->error_set('未入力/エラーがあります。');
			return false;
		}
		return true;
	};
}

/**
 * [ANYOK]
 * -------------------------------------------------------------------
 * 指定要素のどれかが正常に値が入っているかどうか。第一引数で最低個数を指定可能（数値）
 * ※エラー要素が一つでもあればエラーになります。
 * ex. ANYOK('zip1','zip2')
 * ex. ANYOK(2,'comment1','comment2','comment3')
 */
function ANYOK(){ return (new Validate())->ANYOK(func_get_args()); }
function __ANYOK()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		// 第一引数が数値なら最低数としてセット
		$count = 1;
		if(is_int($args[0])) $count = array_shift($args);

		// カウント
		global $FK;
		$error_count = 0;
		$first_error_message = '';
		$not_error_count = 0;
		foreach($args as $key){
			if($element->name!=$key) $FK[$key]->validate(); // 対象の要素を先にバリデートしておく（万が一自分の要素名が入ってたら無視）
			if(strlen($FK[$key]->error)) {
				$error_count ++;
				if(!$first_error_message) $first_error_message = $FK[$key]->error;
			} else if(strlen($FK[$key]->val)) {
				$not_error_count ++;
			}
		}
		if($error_count){
			$element->error_set($first_error_message);
			return false;
		}
		if($not_error_count < $count){
			$element->error_set($count.' 箇所以上の入力が必要です。');
			return false;
		}
		return true;
	};
}

/**
 * [GLUE]
 * -------------------------------------------------------------------
 * ex. GULE('@','mail_head','mail_foot')->EMAIL(1)
 */
function GLUE(){ return (new Validate())->GLUE(func_get_args()); }
function __GLUE()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		global $FK;
		$glue = array_shift($args);
		$list = array();
		foreach($args as $key){
			if($element->name!=$key) $FK[$key]->validate(); // 対象の要素を先にバリデートしておく（万が一自分の要素名が入ってたら無視）
			array_push($list, $FK[$key]->val);
		}
		$element->val = implode($glue, array_filter($list, 'strlen'));

		return true;
	};
}

/**
 * [DATES]
 * -------------------------------------------------------------------
 * 正しい日付かチェック
 * DATES() ... 当要素が文字列として日時を正しく表しているかチェック（'2017-01-01', '2017/1/1' 等）
 * DATES('year','month','day') ... 各要素の値が日時を正しく表しているかチェック
 */
function DATES(){ return (new Validate())->DATES(func_get_args()); }
function __DATES()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		if(@count($args)===3) {
			global $FK;
			// すべて空なら何もしない
			if(empty($FK[$args[0]]->val) && empty($FK[$args[1]]->val) && empty($FK[$args[2]]->val)) return true;
			// 年が4桁である
			if(preg_match('/^\d{4}$/', $FK[$args[0]]->val)) {
				// 引数３つあれば各要素を[年][月][日]として日付チェック
				list($year_label, $month_label, $day_label) = $args;
				if($FK[$month_label]->val && $FK[$day_label]->val && $FK[$year_label]->val) {
					if(checkdate($FK[$month_label]->val, $FK[$day_label]->val, $FK[$year_label]->val)) return true;
				}
			}
		} else {
			// 当要素の値が日付文字列かチェック
			$element->val = preg_replace('/^(\d+\D+\d+\D+\d+).*$/', '$1', $element->val);
			$dates = preg_split('/\D+/', $element->val);
			if(@count($dates)===3 && checkdate($dates[1], $dates[2], $dates[0])) return true;
		}
		$element->error_set('正しい日付ではありません。');
		return false;
	};
}
