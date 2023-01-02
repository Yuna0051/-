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
 * バリデートクラスメソッド定義
 * -------------------------------------------------------------------------------------------------
 * - 変換メソッド集
 */

/**
 * [CONV]
 * -------------------------------------------------------------------
 * PHPのmb_convert_kana()を実行して変換
 */
function CONV($option){ return (new Validate())->CONV($option); }
function __CONV($option)
{
	return function ($element) use($option)
	{
		if(!is_array($element->val))
			$element->val = mb_convert_kana($element->val, $option);
		return true;
	};
}

/**
 * [DELRETURN]
 * -------------------------------------------------------------------
 * 改行コード除去
 * NORETURN() ... \r \n \r\n \f 全て削除します。
 */
function DELRETURN(){ return (new Validate())->DELRETURN(); }
function __DELRETURN()
{
	return function ($element)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_ereg_replace('\r|\n|\r\n|f', '', $element->val);
		return true;
	};
}

/**
 * [DELWORD]
 * -------------------------------------------------------------------
 * 指定文字を除去
 * DELWORD('あいう|山田')
 */
function DELWORD($words=''){ return (new Validate())->DELWORD($words); }
function __DELWORD($words='')
{
	return function ($element) use($words)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_ereg_replace($words, '', $element->val);
		return true;
	};
}

/**
 * [ZEN2HAN]
 * -------------------------------------------------------------------
 * 全角→半角に変換する
 * ZEN2HAN()
 */
function ZEN2HAN(){ return (new Validate())->ZEN2HAN(); }
function __ZEN2HAN()
{
	return function ($element)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_convert_kana($element->val, 'rnaskhc');
		return true;
	};
}

/**
 * [HAN2ZEN]
 * -------------------------------------------------------------------
 * 半角→全角に変換する
 * HAN2ZEN()
 */
function HAN2ZEN(){ return (new Validate())->HAN2ZEN(); }
function __HAN2ZEN()
{
	return function ($element)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_convert_kana($element->val, 'RNASKHV');
		return true;
	};
}
