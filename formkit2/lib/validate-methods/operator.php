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
 * - 特殊オペレータメソッド集
 */

/**
 * [FUNC]
 * -------------------------------------------------------------------
 */
function FUNC($func){ return (new Validate())->FUNC($func); }
function __FUNC($func)
{
	return function ($element) use($func)
	{
		return $func($element);
	};
}

/**
 * [ERRORSET]
 * -------------------------------------------------------------------
 * この時点でエラーメッセージがセットされている場合に、上書きする。
 * 但し、1要素中でメッセージ上書きが実行されるのは一度のみ。（この制限
 * により、１要素中に設定した各バリデートごとに直後に書いたERRORSET()
 * で独自のエラーメッセージがそれぞれ設定できるということです。
 */
function ERRORSET($overwrite){ return (new Validate())->ERRORSET($overwrite); }
function __ERRORSET($overwrite)
{
	return function ($element) use($overwrite)
	{
		if($element->is_errorset){
			return true;
		}
		if(strlen($element->error))
		{
			$element->error = $overwrite;
			$element->is_errorset = true;
		}
		return true;
	};
}
