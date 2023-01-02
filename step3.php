<?php $Config['before_validates'] = 'colors,colors_text,fruit,fruit_text,kansou' ?>
<?php require 'formkit/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<meta name="viewport" content="width=device-width">
<title>入力画面(step3) - 複数ページサンプルフォーム - FormKit</title>
</head>
<body>

<!-- ナビリンク -->
<?php include '../navi.php' ?>

<main>
	<h1>複数ページサンプルフォーム</h1>
	<div class="steps">
		<span>入力画面(step1)</span> &gt;
		<span>入力画面(step2)</span> &gt;
		<span class="now">入力画面(step3)</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form method="post">
		<?= \FK\hiddens_tag() ?>
		<table>
			<tbody>
				<tr>
					<th class="fk-req"><p>お名前</p></th>
					<td>
						<input type="text" name="shimei" value="<?= $shimei ?>">
						<?= $shimei->marker_tag() ?>
						<?= $shimei->error_tag() ?>
					</td>
				</tr>
				<tr>
					<th class="fk-req"><p>お名前（フリガナ）</p></th>
					<td>
						<input type="text" name="kana" value="<?= $kana ?>">
						<?= $kana->marker_tag() ?>
						<?= $kana->error_tag() ?>
					</td>
				</tr>
				<tr>
					<th class="fk-req"><p>同意して下さい。</p></th>
					<td>
						<label><input type="checkbox" name="agree" value="同意"<?= $agree->checked('同意') ?>> 同意する</label>
						<?= $agree->marker_tag() ?>
						<?= $agree->error_tag() ?>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="submit">
			<button formaction="step2.php?revalidate" data-fk-no-validate>前へ</button>
			　　　
			<button formaction="check.php">確認画面へ</button>
		</div>
		<aside><?= \FK\copyright_tag(); ?></aside>
	</form>
</main>

<script src="formkit/app/fk.js"></script>

</body>
</html>
