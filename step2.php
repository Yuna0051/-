<?php $Config['before_validates'] = 'support' ?>
<?php require 'formkit/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<title>入力画面(step2)</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span>入力画面(商品選択)</span> &gt;
		<span class="now">入力画面(お客様情報)</span> &gt;
		<span>入力画面(お支払い方法)</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form method="post">
		<?= \FK\hiddens_tag() ?>
		<section id="customer">
			<h1 class="main_title">お客様情報</h1>
			<h4 class="sub_title">customer</h4>
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
						<th class="fk-req"><p>メールアドレス</p></th>
						<td>
							<input type="text" name="email" value="<?= $email ?>" data-fk-with="email2">
							<?= $email->marker_tag() ?>
							<?= $email->error_tag() ?>
							<p>確認用に再度入力して下さい。</p>
							<input type="text" name="email2" value="<?= $email2 ?>" data-fk-with="email">
							<?= $email2->marker_tag() ?>
							<?= $email2->error_tag() ?>					
						</td>
					</tr>
				</tbody>
			</table>
		</section>
		<div class="submit">
			<button formaction="step1.php?revalidate" data-fk-no-validate>前へ</button>
			<button formaction="step3.php">次へ</button>
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
