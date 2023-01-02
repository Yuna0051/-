<?php $Config['before_validates'] = '' ?>
<?php require 'formkit/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<meta name="viewport" content="width=device-width">
<title>入力画面(step1) - 複数ページサンプルフォーム - FormKit</title>
</head>
<body>

<!-- ナビリンク -->
<?php include '../navi.php' ?>

<main>
	<h1>複数ページサンプルフォーム</h1>
	<p class="desc">
		アンケートなど、入力画面が複数ページに渡る場合のサンプルフォームです。通常構成（入力→確認→送信/完了）のフォームとは少々設定方法が違いますので、詳細はマニュアルをご参照下さい。
	</p>
	<div class="steps">
		<span class="now">入力画面(step1)</span> &gt;
		<span>入力画面(step2)</span> &gt;
		<span>入力画面(step3)</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form action="step2.php" method="post">
		<?= \FK\hiddens_tag() ?>
		<table>
			<tbody>
				<tr>
					<th class="fk-req"><p>好きな色はなんですか？（複数選択可）</p></th>
					<td>
						<label><input type="checkbox" name="colors[]" value="白" <?= $colors->checked('白') ?>> 白</label><br>
						<label><input type="checkbox" name="colors[]" value="青" <?= $colors->checked('青') ?>> 青</label><br>
						<label><input type="checkbox" name="colors[]" value="赤" <?= $colors->checked('赤') ?>> 赤</label><br>
						<label><input type="checkbox" name="colors[]" value="緑" <?= $colors->checked('緑') ?>> 緑</label><br>
						<label><input type="checkbox" name="colors[]" value="黒" <?= $colors->checked('黒') ?>> 黒</label><br>
						<label><input type="checkbox" name="colors[]" value="その他" <?= $colors->checked('その他') ?> data-fk-with="colors_text" data-fk-disabled-to="colors_text">
						その他 <input type="text" name="colors_text" value="<?= $colors_text ?>"><?= $colors_text->marker_tag() ?></label>
						<?= $colors->error_tag() ?>
						<?= $colors_text->error_tag() ?>
					</td>
				</tr>
				<tr>
					<th><p>好きなフルーツはなんですか？（複数選択可）</p></th>
					<td>
						<label><input type="checkbox" name="fruit[]" value="りんご" <?= $fruit->checked('りんご') ?>> りんご</label><br>
						<label><input type="checkbox" name="fruit[]" value="バナナ" <?= $fruit->checked('バナナ') ?>> バナナ</label><br>
						<label><input type="checkbox" name="fruit[]" value="みかん" <?= $fruit->checked('みかん') ?>> みかん</label><br>
						<label><input type="checkbox" name="fruit[]" value="ぶどう" <?= $fruit->checked('ぶどう') ?>> ぶどう</label><br>
						<label><input type="checkbox" name="fruit[]" value="桃" <?= $fruit->checked('桃') ?>> 桃</label><br>
						<label><input type="checkbox" name="fruit[]" value="その他" <?= $fruit->checked('その他') ?> data-fk-with="fruit_text" data-fk-disabled-to="fruit_text">
						その他 <input type="text" name="fruit_text" value="<?= $fruit_text ?>"><?= $fruit_text->marker_tag() ?></label>
						<?= $fruit->error_tag() ?>
						<?= $fruit_text->error_tag() ?>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="submit">
			<button>次へ</button>
		</div>
		<aside><?= \FK\copyright_tag(); ?></aside>
	</form>
</main>

<script src="formkit/app/fk.js"></script>

</body>
</html>
