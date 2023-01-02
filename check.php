<?php require 'formkit/app/fk-check.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<meta name="viewport" content="width=device-width">
<title>確認画面 - 複数ページサンプルフォーム - FormKit</title>
</head>
<body>

<!-- ナビリンク -->
<?php include '../navi.php' ?>

<main>
	<h1>複数ページサンプルフォーム</h1>
	<div class="steps">
		<span>入力画面(step1)</span> &gt;
		<span>入力画面(step2)</span> &gt;
		<span>入力画面(step3)</span> &gt;
		<span class="now">確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form method="post">
		<?= \FK\hiddens_tag() ?>
		<table>
			<tbody>
				<tr>
					<th class="fk-req"><p>好きな色はなんですか？（複数選択可）</p></th>
					<td>
						<?= $colors->join("\n") ?><?= $colors_text->wrap('：')->empty_label('') ?>
					</td>
				</tr>
				<tr>
					<th><p>好きなフルーツはなんですか？（複数選択可）</p></th>
					<td>
						<?= $fruit->join("\n") ?><?= $fruit_text->wrap('：')->empty_label('') ?>
					</td>
				</tr>
				<tr>
					<th class="fk-req"><p>ご意見・ご感想</p></th>
					<td>
						<?= $kansou ?>
					</td>
				</tr>
				<tr>
					<th class="fk-req"><p>お名前</p></th>
					<td>
						<?= $shimei ?>
					</td>
				</tr>
				<tr>
					<th class="fk-req"><p>お名前（フリガナ）</p></th>
					<td>
						<?= $kana ?>
					</td>
				</tr>
				<tr>
					<th class="fk-req"><p>同意して下さい。</p></th>
					<td>
						<?= $agree ?>
					</td>
				</tr>
			</tbody>
		</table>
		<div class="submit">
			<button formaction="step3.php?revalidate" data-fk-no-validate>前へ</button>
			　　　
			<button formaction="thanks.php">送信する</button>
		</div>
		<aside><?= \FK\copyright_tag(); ?></aside>
	</form>
</main>

<script src="formkit/app/fk.js"></script>

</body>
</html>
