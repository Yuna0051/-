<?php require 'formkit/app/fk-send.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<meta name="viewport" content="width=device-width">
<title>完了画面 - 複数ページサンプルフォーム - FormKit</title>
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
		<span>確認画面</span> &gt;
		<span class="now">完了画面</span>
	</div>
	<p>送信完了！</p>
	<div class="submit">
		<a href="./" class="button">入力トップに戻る</a>
	</div>
</main>

</body>
</html>
