<?php require 'formkit2/app/fk-check.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit2/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<meta name="format-detection" content="telephone=no">
<title>確認画面</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span>入力画面</span> &gt;
		<span class="now">確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form method="post">
		<?= \FK\hiddens_tag() ?>
		<section id="customer">
			<h1 class="main_title">確認画面</h1>
			<h4 class="sub_title">check</h4>
			<table>
				<tbody>
					<tr>
						<th class="fk-req"><p>お名前</p></th>
						<td>
							<?= $shimei ?>
						</td>
					</tr>
					<tr>
						<th class="fk-req">メールアドレス</th>
						<td><?= $email->mail_link_tag() ?></td>
					</tr>
					<tr>
						<th class="fk-req">お電話番号</th>
						<td><?= $tel ?></td>
					</tr>
					<tr>
						<th class="fk-req">生年月日</th>
						<td><?= $birth_year ?>年 <?= $birth_month ?>月 <?= $birth_day ?>日</td>
					</tr>
					<tr>
						<th class="fk-req">お問い合わせ内容</th>
						<td><?= $comment ?></td>
					</tr>
				</tbody>
			</table>
		</section>
		<div class="submit">
			<button formaction="ContactThanks.php" style="visibility: hidden; width:0; height:0; margin:0;">送信する</button>
			<button formaction="ContactForm.php?revalidate" class="prev">前へ</button>
			<button formaction="ContactThanks.php">送信する</button>
		</div>
	</form>
</main>
<footer>
    <div class="footer_inner">
        <a href="#" style="opacity: 0;">お申し込みはこちら</a>
        <div>
            <img src="/images/クラブペイ.png" alt="クラブペイ">
            <ul>
				<li>〒815-0033</li>
                <li>福岡県福岡市南区大橋1-15-6</li>
                <li>アルボーレ大橋4F</li>
                <li>TEL:092-707-1727</li>
            </ul>
        </div>
    </div>
    <small>&copy; クラブペイ.</small>
</footer>

<script src="formkit2/app/fk.js"></script>

</body>
</html>
