<?php $Config['before_validates'] = 'support,shimei,kana,email,email2' ?>
<?php require 'formkit/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<title>入力画面(step3)</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span>入力画面(商品選択)</span> &gt;
		<span>入力画面(お客様情報)</span> &gt;
		<span class="now">入力画面(お支払い方法)</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form method="post">
		<?= \FK\hiddens_tag() ?>
		<section id="payment">
			<h1 class="main_title">お支払い方法</h1>
			<h4 class="sub_title">payment</h4>
			<table>
				<tbody>
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
		</section>
		<div class="submit">
			<button formaction="step2.php?revalidate" data-fk-no-validate>前へ</button>
			<button formaction="check.php">確認画面へ</button>
		</div>
		<aside style="display: none;"><?= \FK\copyright_tag(); ?></aside>
	</form>
</main>
<footer>
    <div class="footer_inner">
        <a href="#" style="opacity: 0;">お申し込みはこちら</a>
        <div>
            <img src="/images/クラブペイ.png" alt="クラブペイ">
            <ul>
                <li>〒100-0000</li>
                <li>東京都港区浜松町0丁目0番00号</li>
                <li>000ダイヤビル0F</li>
                <li>TEL:000-000-0000</li>
            </ul>
        </div>
    </div>
    <small>&copy; クラブペイ.</small>
</footer>

<script src="formkit/app/fk.js"></script>

</body>
</html>
