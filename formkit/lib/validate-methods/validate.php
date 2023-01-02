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
 * - 標準バリデートメソッド集
 */

/**
 * [REQ]
 * -------------------------------------------------------------------
 */
function REQ(){ return (new Validate())->REQ(func_get_args()); }
function __REQ()
{

	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function($element) use($args)
	{

		// 引数がある場合の処理
		if(@count($args))
		{
			global $FK;
			if(is_array($args[0]))
			{
				// 第一引数に配列が指定されていれば、配列[0]をeval実行
				// -------------------------------------------------------
				// ex. REQ(['$gender->raw==="男性"'])
				$rule = $args[0][0];
				extract($FK);
				$result = eval("return $rule;");
				if(!$result) return true;
			}
			else if(@count($args)===1)
			{
				// 引数が1つの時はその要素が空でない場合に必須となる
				// -------------------------------------------------------
				// ex. REQ('gender')
				if($FK[$args[0]]->is_empty()) return true;
			}
			else if(@count($args)===2)
			{
				// 引数2つの場合は == 比較してtrueなら必須となる
				// -------------------------------------------------------
				// ex. REQ('gender','男性')
				// また、比較対象が複数値だった場合は in_array() でチェックする
				$target_list = array();
				if(is_array($FK[$args[0]]->raw)){
					$target_list = array_merge($target_list, $FK[$args[0]]->raw);
				} else {
					$target_list = array($FK[$args[0]]->raw);
				}
				if(!in_array($args[1], $target_list)) return true;
			}
			else if(@count($args)===3)
			{
				// 引数3つの場合は 第二引数で指定されたオペレータで比較
				// -------------------------------------------------------
				// ex. REQ('gender','===','男性')
				$rule = "\${$args[0]}->raw $args[1] '{$args[2]}'";
				extract($FK);
				$result = eval("return $rule;");
				if(!$result) return true;
			}
		}
		$element->required = true;
		if($element->is_empty()) {
			$element->error_set('入力必須項目です。');
			return false;
		} else {
			return true;
		}
	};
}

/**
 * [LENGTH]
 * -------------------------------------------------------------------
 */
function LENGTH($min=null, $max=null, $ret=false){ return (new Validate())->LENGTH($min, $max, $ret); }
function __LENGTH($min=null, $max=null, $ret=false)
{
	return function ($element) use($min ,$max, $ret)
	{
		if(is_array($element->val)) return true;
		if($element->is_length) return true;
		$data = $element->val;

		// 第二引数が null なら第一引数を上限値とする。
		if($max===null) {
			$max = $min;
			$min = 0;
		}

		// 第三引数が有効なら、改行を無視する
		if(!$ret) {
			$data = str_replace("\r", '', $data);
			$data = str_replace("\n", '', $data);
		}

		// LENGTH()実行済みスイッチON
		$element->is_length = true;

		// 判定
		$len = mb_strlen($data);
		if($len<$min) {
			$sub = $min-$len;
			$element->error_set(sprintf('入力された文字があと %s 文字足りません。（%s%s文字まで）', $sub, ($min ? "{$min}～" : ''), $max));
			return false;
		}
		else if($len>$max) {
			$over = $len-$max;
			$element->error_set(sprintf('入力された文字が %s 文字オーバーしています。（%s%s文字まで）', $over, ($min ? "{$min}～" : ''), $max));
			return false;
		}
		return true;
	};
}

/**
 * [EMAIL]
 * -------------------------------------------------------------------
 * 正しいメールアドレスか（mode=1...ホスト存在チェックも行う）
 */
function EMAIL($mode=null){ return (new Validate())->EMAIL($mode); }
function __EMAIL($mode=null)
{
	return function($element) use ($mode)
	{
		// 「全角」英数字を「半角」に変換する
		$element->val = mb_convert_kana($element->val, 'a'); # 全角英数字→半角英数字

		// メール文法をチェック
		if(!mail_address_check($element->val)){
			$element->error_set('正しいメールアドレスを入力して下さい。');
			return false;
		}

		// ホストでチェック
		if($mode===1 && !mail_address_check($element->val, true)){
			$element->error_set('正しいドメインのメールアドレスを入力して下さい。');
			return false;
		}
		return true;
	};
}

/**
 * [MINMAX]
 * -------------------------------------------------------------------
 * 数値としてのバリデート
 */
function MINMAX($min=null, $max=null, $floatmode=false){ return (new Validate())->MINMAX($min, $max, $floatmode); }
function __MINMAX($min=null, $max=null, $floatmode=false)
{
	return function ($element) use($min ,$max, $floatmode)
	{
		// 「全角」数字を「半角」に変換
		$element->val = mb_convert_kana($element->val, 'n'); # 全角数字→半角数字

		// 余計な文字を除去
		if(!$floatmode){
			// [整数モード] 半角数値、ハイフン以外を除去（小数点があれば最初に小数点の右側を除去）
			$element->val = preg_replace('/\..*/', '', $element->val);
			$element->val = preg_replace('/[^\-\d]/', '', $element->val);
		} else {
			// [小数モード] 半角数値、ハイフン、小数点以外を除去（複数の小数点があれば1つにまとめる）
			$element->val = preg_replace('/\.(\d*?)\..*$/', '.$1', $element->val);
			$element->val = preg_replace('/\.$/', '', $element->val);
			$element->val = preg_replace('/[^\-\d\.]/', '', $element->val);
		}

		// 値が残っていればチェック
		if($element->is_exist())
		{
			// 数値範囲をチェックします
			if($element->val>=$min && $element->val<=$max) return true;
			$element->error_set(sprintf('入力された数値が範囲外です。（%s～%s）', $min, $max));
			return false;
		}
		return true;
	};
}

/**
 * [NUM]
 * -------------------------------------------------------------------
 * 半角数字の長さ制限
 */
function NUM($min, $max=null){ return (new Validate())->NUM($min, $max); }
function __NUM($min, $max=null)
{
	return function ($element) use($min ,$max)
	{
		// 引数が1つの場合、長さ固定にする。つまりNUM(4)はNUM(4,4)と同義。
		if($max===null) $max = $min;

		// 「全角」数字を「半角」に変換
		$element->val = mb_convert_kana($element->val, 'n'); # 全角数字→半角数字

		// 余計な文字を除去（0-9のみに）
		$element->val = preg_replace('/\D/', '', $element->val);

		// 値が残っていればチェック
		if($element->is_exist())
		{
			// 数値範囲をチェックします
			if(strlen($element->val)>=$min && strlen($element->val)<=$max) return true;
			$element->error_set('入力された数値の長さが範囲外です。');
			return false;
		}
		return true;
	};
}

/**
 * [REGEX]
 * -------------------------------------------------------------------
 */
function REGEX($regex, $replace=null){ return (new Validate())->REGEX($regex, $replace); }
function __REGEX($regex, $replace=null)
{
	return function ($element) use($regex, $replace)
	{
		if(isset($replace)) {
			$element->val = preg_replace($regex, $replace, $element->val);
			return true;
		} else {
			if(preg_match($regex, $element->val)){
				return true;
			} else {
				$element->error_set('入力された値は正しくありません。');
				return false;
			}
		}
	};
}

/**
 * [SAME]
 * -------------------------------------------------------------------
 */
function SAME($target){ return (new Validate())->SAME($target); }
function __SAME($target)
{
	return function ($element) use($target)
	{
		global $FK;
		if($element->name!=$target) $FK[$target]->validate(); // 対象の要素を先にバリデートしておく（万が一自分の要素名が入ってたら無視）
		if(strval($element) != strval($FK[$target])){
			$element->error_set('同じ値が入力されませんでした。');
			return false;
		}
		return true;
	};
}

/**
 * [ITEM]
 * -------------------------------------------------------------------
 */
function ITEM(){ return (new Validate())->ITEM(func_get_args()); }
function __ITEM()
{

	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		if(is_array($element->val)){
			// 複数値の場合、全てが指定値であるかチェック
			$hit_count = 0;
			foreach($element->val as $val){
				if(in_array($val, $args)) $hit_count++;
			}
			if($hit_count===@count($element->val)) return true;
		} else if(in_array($element->val, $args)) {
			return true;
		} else {
			$element->error_set('正しい選択をしてください。');
			return false;
		}
	};
}

/**
 * [EXT]
 * -------------------------------------------------------------------
 * ファイル種類の限定
 */
function EXT(){ return (new Validate())->EXT(func_get_args()); }
function __EXT()
{

	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		$ok = 0;
		if(!$element->val) return true;
		if(!array_key_exists('org_name', $element->file)) return true;
		foreach($args as $arg){
			if(preg_match("/\.$arg$/", $element->file['org_name'])) $ok ++;
		}
		if($ok){
			return true;
		} else {
			$element->error_set('正しい種類のファイルを選択してください。');
			return false;
		}
	};
}

/**
 * [MB]
 * -------------------------------------------------------------------
 * ファイル容量制限
 */
function MB($limit_mb){ return (new Validate())->MB($limit_mb); }
function __MB($limit_mb)
{
	// アップロードサイズの上限をチェックするためにグローバル変数に記憶
	global $Config;
	if(@$Config['max_limit_byte'] < $limit_mb*1024*1024) $Config['max_limit_byte'] = $limit_mb*1024*1024;

	return function ($element) use($limit_mb)
	{
		if(!$element->val) return true;
		if(!array_key_exists('file_size', $element->file)) return true;
		if($element->file['file_size'] <= $limit_mb * 1024 * 1024){
			return true;
		} else {
			$element->error_set('ファイルサイズが大きすぎます。');
			return false;
		}
	};
}

/**
 * [WIDTH]
 * -------------------------------------------------------------------
 * 画像ファイルの横幅を制限する
 * WIDTH(100) ... 100px以下
 * WIDTH(100,200) ... 100px～200px
 */
function WIDTH(){ return (new Validate())->WIDTH(func_get_args()); }
function __WIDTH()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		global $FK;
		if(!$element->is_image()){
			$element->error_set('指定のファイルは画像ファイルではありません。');
			return false;
		}
		$width = $element->file['width'];
		$height = $element->file['height'];
		if(@count($args)===1) {
			# // 引数が1つのとき
			if(!($width <= $args[0])) {
				$element->error_set('指定の画像ファイルのピクセル数の条件が合っていません。 '."[{$width}x{$height}ピクセル]");
				return false;
			}
		} else if(@count($args)===2) {
			# // 引数が2つのとき
			if(!($width >= $args[0] && $width <= $args[1])) {
				$element->error_set('指定の画像ファイルのピクセル数の条件が合っていません。 '."[{$width}x{$height}ピクセル]");
				return false;
			}
		} else {
			$element->error_set('設定ファイルのバリデート定義が間違っています。'.$element->name);
			return false;
		}
		return true;
	};
}

/**
 * [HEIGHT]
 * -------------------------------------------------------------------
 * 画像ファイルの横幅を制限する
 * HEIGHT(100) ... 100px以下
 * HEIGHT(100,200) ... 100px～200px
 */
function HEIGHT(){ return (new Validate())->HEIGHT(func_get_args()); }
function __HEIGHT()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		global $FK;
		if(!$element->is_image()){
			$element->error_set('指定のファイルは画像ファイルではありません。');
			return false;
		}
		$width = $element->file['width'];
		$height = $element->file['height'];
		if(@count($args)===1) {
			# // 引数が1つのとき
			if(!($height <= $args[0])) {
				$element->error_set('指定の画像ファイルのピクセル数の条件が合っていません。 '."[{$width}x{$height}ピクセル]");
				return false;
			}
		} else if(@count($args)===2) {
			# // 引数が2つのとき
			if(!($height >= $args[0] && $height <= $args[1])) {
				$element->error_set('指定の画像ファイルのピクセル数の条件が合っていません。 '."[{$width}x{$height}ピクセル]");
				return false;
			}
		} else {
			$element->error_set('設定ファイルのバリデート定義が間違っています。'.$element->name);
			return false;
		}
		return true;
	};
}

/**
 * [PREF]
 * -------------------------------------------------------------------
 * 都道府県の文字列かどうか
 * PREF()  ... 末尾の"都/道/府/県"が必要 → 「北海道」「宮城県」「東京都」などを許可する。
 * PREF(1) ... 末尾の"都/道/府/県"は無くてもOK → 上記の他に「宮城」「東京」なども許可する（例外として「北海」はおかしいのでNG）。
 */
function PREF($is_omit=null){ return (new Validate())->PREF($is_omit); }
function __PREF($is_omit=null)
{
	return function ($element) use($is_omit)
	{
		$result = null;
		if($is_omit)
		{
			$result = preg_match('/^(?:北海道|青森県|岩手県|宮城県|秋田県|山形県|福島県|東京都|神奈川県|埼玉県|千葉県|茨城県|栃木県|群馬県|山梨県|新潟県|長野県|富山県|石川県|福井県|愛知県|岐阜県|静岡県|三重県|大阪府|兵庫県|京都府|滋賀県|奈良県|和歌山県|鳥取県|島根県|岡山県|広島県|山口県|徳島県|香川県|愛媛県|高知県|福岡県|佐賀県|長崎県|熊本県|大分県|宮崎県|鹿児島県|沖縄県|青森|岩手|宮城|秋田|山形|福島|東京|神奈川|埼玉|千葉|茨城|栃木|群馬|山梨|新潟|長野|富山|石川|福井|愛知|岐阜|静岡|三重|大阪|兵庫|京都|滋賀|奈良|和歌山|鳥取|島根|岡山|広島|山口|徳島|香川|愛媛|高知|福岡|佐賀|長崎|熊本|大分|宮崎|鹿児島|沖縄)$/', $element->val);
		} else {
			$result = preg_match('/^(?:北海道|青森県|岩手県|宮城県|秋田県|山形県|福島県|東京都|神奈川県|埼玉県|千葉県|茨城県|栃木県|群馬県|山梨県|新潟県|長野県|富山県|石川県|福井県|愛知県|岐阜県|静岡県|三重県|大阪府|兵庫県|京都府|滋賀県|奈良県|和歌山県|鳥取県|島根県|岡山県|広島県|山口県|徳島県|香川県|愛媛県|高知県|福岡県|佐賀県|長崎県|熊本県|大分県|宮崎県|鹿児島県|沖縄県)$/', $element->val);
		}
		if($result) return true;
		$element->error_set('正しい都道府県を選択してください。');
		return false;
	};
}

/**
 * [COUNTS]
 * -------------------------------------------------------------------
 * radio,checkboxの選択数をバリデートする
 * COUNTS(1) ... 1個のみ許可
 * COUNTS(3,5) ... 3～5個の間許可
 */
function COUNTS(){ return (new Validate())->COUNTS(func_get_args()); }
function __COUNTS()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		global $FK;
		if(@count($args)===1) {
			# // 引数が1つのときはイコール比較
			if(!(@count($element->val) == $args[0])) {
				$element->error_set(sprintf('%s 個選択してください。', $args[0]));
				return false;
			}
		} else if(@count($args)===2) {
			# // 引数が2つのときは範囲
			if(!(@count($element->val) >= $args[0] && @count($element->val) <= $args[1])) {
				$element->error_set(sprintf('%d ～ %d 個選択してください。', $args[0], $args[1]));
				return false;
			}
		} else {
			$element->error_set('設定ファイルのバリデート定義が間違っています。'.$element->name);
			return false;
		}
		return true;
	};
}

/**
 * [TEL]
 * -------------------------------------------------------------------
 * 電話番号入力バリデート
 * TEL()     ... 半角0～9と半角の「-」「+」のみで構成されているかチェック
 * TEL('()') ... 上記に加え「(」と「)」が使用できる
 */
function TEL($allow=''){ return (new Validate())->TEL($allow); }
function __TEL($allow='')
{
	return function ($element) use($allow)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_convert_kana($element->val, 'a'); # 全角英数字→半角英数字
		$allow_regex = '';
		foreach(preg_split("//u", $allow, -1, PREG_SPLIT_NO_EMPTY) as $char){
			$allow_regex .= '\\' . $char;
		}
		if(preg_match('/^[0-9\-\+'.$allow_regex.']*$/u', $element->val)){
			return true;
		}
		$element->error_set('使用できない文字が含まれています。');
		return false;
	};
}

/**
 * [HIRA]
 * -------------------------------------------------------------------
 * ひらがな入力バリデート
 * HIRA()       ... ひらがな＋全角空白＋半角空白＋改行のみで構成されているかチェック
 * HIRA('、。') ... 上記に加え「、」と「。」が使用できる
 */
function HIRA($allow=''){ return (new Validate())->HIRA($allow); }
function __HIRA($allow='')
{
	return function ($element) use($allow)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_convert_kana($element->val, 'HVc'); # 全/半角カナ→全角ひらがな
		$allow_regex = '';
		foreach(preg_split("//u", $allow, -1, PREG_SPLIT_NO_EMPTY) as $char){
			$allow_regex .= '\\' . $char;
		}
		if(preg_match('/^[ぁ-ゞー　 \r\n'.$allow_regex.']*$/u', $element->val)){
			return true;
		}
		$element->error_set('ひらがな以外の文字が混ざっています。');
		return false;
	};
}

/**
 * [KATA]
 * -------------------------------------------------------------------
 * カタカナ入力バリデート
 * KATA()       ... 全角カタカナ＋全角空白＋半角空白＋改行のみで構成されているかチェック
 * KATA('、。') ... 上記に加え「、」と「。」が使用できる
 */
function KATA($allow=''){ return (new Validate())->KATA($allow); }
function __KATA($allow='')
{
	return function ($element) use($allow)
	{
		if(is_array($element->val)) return false;
		$element->val = mb_convert_kana($element->val, 'KVC'); # 半角カナ/全角ひらがな→全角カナ
		$allow_regex = '';
		foreach(preg_split("//u", $allow, -1, PREG_SPLIT_NO_EMPTY) as $char){
			$allow_regex .= '\\' . $char;
		}
		if(preg_match('/^[ァ-ヾー　 \r\n'.$allow_regex.']*$/u', $element->val)){
			return true;
		}
		$element->error_set('カタカナ以外の文字が混ざっています。');
		return false;
	};
}

/**
 * [ZIP]
 * -------------------------------------------------------------------
 * 郵便番号入力バリデート
 * ZIP()     ... 「980-0001」「9800001」が通り、ハイフン形式に統一される。
 * ZIP(3)    ... 数値3桁のみ通る
 * ZIP(4)    ... 数値4桁のみ通る
 * ZIP(7)    ... 数値7桁のみ通り、自動でハイフン形式になる。
 * ZIP(null,false) ... 自動でハイフンが入らないようにする
 * ※自動的に半角に変換し、ハイフン・数字以外は自動的に除去します。
 */
function ZIP($num='', $auto_format=true){ return (new Validate())->ZIP($num, $auto_format); }
function __ZIP($num='', $auto_format=true)
{
	return function ($element) use($num, $auto_format)
	{
		if(is_array($element->val)) return false;
		$element->val = preg_replace('/ー/u', '-', $element->val); # 「ー」→「-」に変換。※「－」は↓で半角ハイフンになる。
		$element->val = mb_convert_kana($element->val, 'a'); # 全角英数字→半角英数字
		if($auto_format){
			$element->val = preg_replace('/^(\d{3})(\d{4})$/', '$1-$2', $element->val);
		}
		if($num>0){
			if(preg_match('/^\d{'.$num.'}$/', $element->val)) return true;
		} else {
			if(preg_match('/^\d{3}\-?\d{4}$/', $element->val)) return true;
		}
		return false;
	};
}

/**
 * [YEAR] [MONTH] [DAY]
 * -------------------------------------------------------------------
 * 日付の範囲内の数値か
 * YEAR()  ... 数値4桁
 * MONTH() ... 数値1～12
 * DAY()   ... 数値1～31
 */
# --- YEAR()
function YEAR(){ return (new Validate())->YEAR(func_get_args()); }
function __YEAR()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		if(preg_match('/^\d{4}$/', $element->val)){
			return true;
		} else {
			$element->error_set('正しい年数ではありません。');
			return false;
		}
	};
}
# --- MONTH()
function MONTH(){ return (new Validate())->MONTH(func_get_args()); }
function __MONTH()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		if(preg_match('/^\d{1,2}$/', $element->val) && 1<=$element->val && $element->val<=12){
			return true;
		} else {
			$element->error_set('正しい月数ではありません。');
			return false;
		}
	};
}
# --- DAY()
function DAY(){ return (new Validate())->DAY(func_get_args()); }
function __DAY()
{
	// 可変引数の展開
	$args = func_get_args();
	if(is_array(@$args[0])) $args = $args[0];

	return function ($element) use($args)
	{
		if(preg_match('/^\d{1,2}$/', $element->val) && 1<=$element->val && $element->val<=31){
			return true;
		} else {
			$element->error_set('正しい日数ではありません。');
			return false;
		}
	};
}

