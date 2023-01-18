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
 * [__uc description]
 * -------------------------------------------------------------------
 */
function __uc($element)
{
	$element->val = strtoupper($element->val);
	return $element;
}

/**
 * [__lc description]
 * -------------------------------------------------------------------
 */
function __lc($element)
{
	$element->val = strtolower($element->val);
	return $element;
}

/**
 * [__wrap description]
 * -------------------------------------------------------------------
 */
function __wrap($element, $head=null, $tail=null)
{
	if($element->is_exist()) {
		$element->val = conv_in($head) . $element->val . conv_in($tail);
	}
	return $element;
}

/**
 * [__tail description]
 * -------------------------------------------------------------------
 */
function __tail($element, $tail=null)
{
	return __wrap($element, null, conv_in($tail));
}

/**
 * 現在値がなければ引数に指定された値をセットする
 * -------------------------------------------------------------------
 * `reinput` 時の時は実行しない
 */
function __def($element, $data)
{
	global $FK;
	if(
		   (!isset($_REQUEST[$element->name]))
		&& (!isset($_SESSION['FK']))
	) {
		$element->val = conv_in($data);
	}
	return $element;
}

/**
 * 行頭に指定の文字を挿入する
 * -------------------------------------------------------------------------------------------------
 * 複数選択なら、自動で\nで連結してから行う。
 */
function __indent($element, $cap)
{
	if(is_array($element->val)) $element->val = join("\n", $element->val);
	if(strlen($element->val)) {
		$lump = '';
		foreach(preg_split('/\n/', $element->val) as $line){
			$lump .= conv_in($cap) . $line . "\n";
		}
		$element->val = $lump;
	}
	return $element;
}

/**
 * 文字列をマスクする
 * -------------------------------------------------------------------------------------------------
 * @param: マスクする文字列
 * @param: マスクしない下桁
 */
function __mask($element, $char='*', $bear=0)
{
	$string = $element->val;
	if($bear>0) {
		$head = substr($string, 0, strlen($string) - $bear);
		$tail = substr($string, -$bear);
	} else {
		$head = $string;
		$tail = '';
	}
	$head_len = strlen($head);
	$head = str_repeat(conv_in($char), $head_len);
	$head = substr($head, 0, $head_len);
	$element->val = $head.$tail;

	return $element;
}

/**
 * ファイル情報をセットする
 * -------------------------------------------------------------------------------------------------
 * ex. $pic->file_org_name() ... オリジナルファイル名
 * ex. $pic->file_tmp_name() ... 一時ファイル名
 * ex. $pic->file_size()     ... ファイルサイズ
 * ex. $pic->file_type()     ... ファイルタイプ
 * ex. $pic->file_preview()  ... ファイルプレビューへのURL
 */
function __file_org_name($element) {
	if(isset($element->file) && array_key_exists('org_name', $element->file)) {
		$element->val = $element->file['org_name'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __file_tmp_name($element) {
	if(isset($element->file) && array_key_exists('tmp_name', $element->file)) {
		$element->val = $element->file['tmp_name'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __file_size($element) {
	if(isset($element->file) && array_key_exists('file_size', $element->file)) {
		$element->val = $element->file['file_size'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __file_type($element) {
	if(isset($element->file) && array_key_exists('mime_type', $element->file)) {
		$element->val = $element->file['mime_type'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __file_width($element) {
	if(isset($element->file) && array_key_exists('width', $element->file)) {
		$element->val = $element->file['width'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __file_height($element) {
	if(isset($element->file) && array_key_exists('height', $element->file)) {
		$element->val = $element->file['height'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __file_preview($element) {
	if(isset($element->file) && array_key_exists('tmp_name', $element->file)) {
		$element->val = $_SERVER['SCRIPT_NAME'].'?pv=' . $element->file['tmp_name'];
	} else {
		$element->val = '';
	}
	return $element;
}
function __is_image($element) {
	if(isset($element->file) && array_key_exists('mime_type', $element->file)) {
		return preg_match('/image\//', $element->file['mime_type']) ? true : false;
	}
	return false;
}
/**
 * ファイルプレビュータグをセットする
 * -------------------------------------------------------------------------------------------------
 * ex. $pic->file_preview_tag()
 * ex. $pic->file_preview_tag(array('is_link_target'=>false))
 */
function __file_preview_tag($element, $adjust=array()) {
	global $Config;

	// 引数標準設定
	$adjust = conv_in($adjust);
	if(!strlen(@$adjust['is_link']))          $adjust['is_link']          = true;
	if(!strlen(@$adjust['is_link_target']))   $adjust['is_link_target']   = true;
	if(!strlen(@$adjust['is_filename_show'])) $adjust['is_filename_show'] = true;
	if(!strlen(@$adjust['max_width']))        $adjust['max_width']        = '50px';
	if(!strlen(@$adjust['max_height']))       $adjust['max_height']       = '50px';
	if(!strlen(@$adjust['empty_label']))      $adjust['empty_label']      = @$Config['empty_label'];

	// 出力をエスケープせずにHTML出力したい
	$element->bear_echo = true;

	// ファイル名タグを先に作成（使いまわすので）
	$filename_tag = '';
	if(array_key_exists('org_name', @$element->file)){
		$filename_tag = '<span class="fk-preview-filename">'.$element->file['org_name'].'</span>';
	}

	// タグ構築
	if($element->is_empty()){
		$element->val = $adjust['empty_label'];
		return $element;
	}
	$url = $element->file_preview();
	if($element->is_image()) {
		// 画像の場合はイメージタグ
		$style = "max-width:{$adjust['max_width']};";
		if(@$adjust['max_height']) $style .= "max-height:{$adjust['max_height']};";
		$element->val = "<img src=\"{$url}\" style=\"{$style}\" class=\"fk-preview-image\" alt=\"*\">";
		if($adjust['is_filename_show']) {
			$element->val = $element->val . $filename_tag;
		}
	} else {
		// その他ファイルの場合はファイル名
		$element->val = $filename_tag;
	}
	if($adjust['is_link']){
		if($element->is_image()){
			// 画像の場合は別ウィンドウ
			$element->val =
				'<a href="'.$url.'" class="fk-preview-link image-file"'.($adjust['is_link_target'] ? ' target="_blank"' : '').'>'.
				$element->val.
				'</a>'
			;
		} else {
			// その他のファイルの場合はダウンロード
			$element->val =
				'<a href="'.$url.'" class="fk-preview-link" download="'.$element->file['org_name'].'">'.
				$element->val.
				'</a>'
			;
		}
	}
	return $element;
}

/**
 * アップロード済みファイルの情報表示
 * -------------------------------------------------------------------------------------------------
 * $shimei->file_control_tag()  ... 削除チェックボックスも表示
 * $shimei->file_control_tag(0) ... 削除チェックボックス無し
 * ※チェーン不可
 */
function __file_control_tag($element, $deletor=1) {
	$html =
		'<input type="hidden" name="'.$element->name.'[org_name]" value="'.$element->file_org_name().'">'."\n".
		'<input type="hidden" name="'.$element->name.'[tmp_name]" value="'.$element->file_tmp_name().'">'."\n"
	;
	if($element->file_upped()){
		$html .= sprintf(
			'<div class="fk-file-control">'.
				$element->file_preview_tag(array('max_width'=>'50px')).
				($deletor ? '<label class="fk-file-control-delete"><input type="checkbox" name="'.$element->name.'[delete]"></label>' : '').
			'</div>'
		);
	}
	return $html;
}

/**
 * ファイルがアップロードされているか
 * -------------------------------------------------------------------------------------------------
 * ※チェーン不可
 */
function __file_upped($element) {
	return (
		isset($element->file) &&
		array_key_exists('org_name', $element->file) &&
		strlen($element->file['org_name'])
	);
}

/**
 * 数値にしてセットする
 * -------------------------------------------------------------------------------------------------
 */
function __int($element) {
	$element->val = trans($element->is_set() ? intval($element->val) : '');
	return $element;
}

/**
 * コンマ付き数値にしてセットする
 * -------------------------------------------------------------------------------------------------
 */
function __comma($element) {
	$element->val = trans($element->is_set() ? number_format($element->int()->val) : '');
	return $element;
}

/**
 * 複数値の要素の場合に、指定の文字列で連結する
 * -------------------------------------------------------------------------------------------------
 */
function __join($element, $glue='-')
{
	$data = is_array($element->val) ? $element->val : (strlen($element->val) ? array($element->val) : null);
	$element->val = is_array($data)
		? join(conv_in($glue), array_map(function($string){ return conv_in(trans($string)); }, $data))
		: null
	;
	return $element;
}

/**
 * 定義済みのデータかどうか
 * -------------------------------------------------------------------------------------------------
 * ※チェーン不可
 */
function __is_set($element) {
	return isset($_REQUEST[$element->name]);
}

/**
 * データが存在しているかどうか（データ幅が1以上か）
 * -------------------------------------------------------------------------------------------------
 * ※チェーン不可
 */
function __is_exist($element) {
	return strlen($element->val) > 0 ? true : false;
}

/**
 * 引数にマッチする値があれば' checked'を返す
 * -------------------------------------------------------------------------------------------------
 * 第二引数がtrueならデフォルトでチェックが入る
 * ※チェーン不可
 */
function __checked($element, $param='', $default=null) {
	$param = conv_in($param);
	$default = conv_in($default);
	if(is_array($element->val))
	{
		if(@count($element->val)===0 && $default) return ' checked';
			else return in_array($param, $element->val, true) ? ' checked' : '';
	} else {
		if($element->val=='' && $default) return ' checked';
			else return strval($element->val)===strval($param) ? ' checked' : ''; // 文字列とし比較
	}
}

/**
 * 引数にマッチする値があれば' selected'を返す
 * -------------------------------------------------------------------------------------------------
 * ※チェーン不可
 */
function __selected($element, $param='', $default=null) {
	$param = conv_in($param);
	$default = conv_in($default);
	if(is_array($element->val))
	{
		if(@count($element->val)===0 && $default) return ' selected';
			else return in_array($param, $element->val, true) ? ' selected' : '';
	} else {
		if($element->val=='' && $default) return ' selected';
			else return strval($element->val)===strval($param) ? ' selected' : ''; // 文字列として比較
	}
}

/**
 * 該当要素のバリデートエラーメッセージをセットする/返す
 * -------------------------------------------------------------------------------------------------
 * ※チェーン不可
 */
function __error($element, $set_error='') {
	// 引数として入ってきたらセットする
	if(strlen($set_error)) $element->error = conv_in($set_error);
	return h($element->error);
}

/**
 * エラー表示用タグを返す
 * -------------------------------------------------------------------------------------------------
 * $shimei->error_tag()  ... エラーじゃなくても空の `div.fk-error` タグは出力する（JSバリデート使用の場合、必須）
 * $shimei->error_tag(0) ... エラーじゃない場合はタグすら出力しない
 * ※チェーン不可
 */
function __error_tag($element, $mode=1) {
	$html = error_tag($element->name, $element->error);
	if(strlen($element->error) || $mode){
		return $html;
	}
}

/**
 * マーカー表示用タグを返す
 * -------------------------------------------------------------------------------------------------
 * $shimei->marker_tag()
 * ※チェーン不可
 */
function __marker_tag($element) {
	return marker_tag($element->name);
}

/**
 * テキストデータ中にリンク（URL、メールアドレス）があれば自動的にすべてリンクタグで囲む
 * -------------------------------------------------------------------------------------------------
 * $email->link_tag() // url is <a href="http://aa">http://aa</a> .
 * $email->link_tag(true) // url is <a href="http://aa" target="_blank">http://aa</a> .
 */
function __auto_link_tag($element, $target='') {
	if($target===true) {
		$target = ' target="_blank"';
	} else if(strlen($target)) {
		$target = ' target="'.$target.'"';
	} else {
		$target = '';
	}
	$element->bear_echo = true;
	$element->val = h($element->val);
	$element->val = nl2br($element->val);
	$element->val = mb_eregi_replace("(https?|ftp)(://[[:alnum:]\+\$\;\?\.%,!#~*/:@&=_-]+)", '<a href="\\1\\2"'.$target.'>\\1\\2</a>' , $element->val);
	$element->val = mb_eregi_replace('((?:\w+\.?)*\w+@(?:\w+\.)+\w+)', '<a href="mailto:\\1">\\1</a>', $element->val);
	return $element;
}

/**
 * データをリンクのタグにして返す
 * -------------------------------------------------------------------------------------------------
 * $page->link_tag();                      // <a href="http://hogehoge">http://hogehoge</a>
 * $page->link_tag('LINK');                // <a href="http://hogehoge">LINK</a>
 * $page->link_tag('LINK',true);           // <a href="http://hogehoge" target="_blank">LINK</a>
 */
function __link_tag($element, $label='', $target='', $scheme='') {
	if(!strlen($label)) $label = $element->val;
	if($target===true) {
		$target = ' target="_blank"';
	} else if(strlen($target)) {
		$target = ' target="'.$target.'"';
	} else {
		$target = '';
	}
	if(strlen($scheme)) $scheme = $scheme.':';
	$html = sprintf(
		'<a href="%s%s"%s>%s</a>',
		$scheme,
		$element->val,
		$target,
		$label
	);
	return $html;
}

/**
 * データをメールリンクのタグにして返す
 * -------------------------------------------------------------------------------------------------
 * $mail->link_tag();         // <a href="mailto:test@test.test">test@test.test</a>
 * $mail->link_tag('メール'); // <a href="mailto:test@test.test">メール</a>
 */
function __mail_link_tag($element, $label='') {
	if(strlen($element->val)){
		if(!strlen($label)) $label = $element->val;
		$html = sprintf(
			'<a href="mailto:%s">%s</a>',
			$element->val,
			$label
		);
		return $html;
	}
	return trans($element->val);
}

/**
 * 空データの時に指定文字を出力する
 * -------------------------------------------------------------------------------------------------
 * $zip->empty_label('（未選択）');  // 空なら「（未選択）」と表示する
 * $zip->empty_label();              // 空ならなにも出力しない。
 */
function __empty_label($element, $string='') {
	if(strlen($element->val)==0){
		echo $string;
	} else {
		return trans($element->val);
	}
}

/**
 * 正規表現で文字を書き換える
 * -------------------------------------------------------------------
 * $date->replace('/^(\d{4})-(\d{1,2})-(\d{1,2})$/', '$1年$2月$3日'); // 「2020-12-31」→「2020年12月31日」
 */
function __replace($element, $regex, $eplacement)
{
	if($element->is_exist()) {
		$element->val = \preg_replace(conv_in($regex), conv_in($eplacement), $element->val);
	}
	return $element;
}
