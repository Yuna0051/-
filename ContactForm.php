<?php require 'formkit2/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="formkit2/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<title>お問い合わせ</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span class="now">入力画面</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form action="ContactCheck.php" method="post">
		<?= \FK\hiddens_tag() ?>
		<section id="customer">
			<h1 class="main_title">お問い合わせ</h1>
			<h4 class="sub_title">contact</h4>
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
					<tr>
					<tr>
						<th class="fk-req"><p>お電話番号</p></th>
						<td>
							<input type="text" name="tel" value="<?= $tel ?>">
							<?= $tel->marker_tag() ?>
							<?= $tel->error_tag() ?>						
						</td>
					</tr>
					<tr>
						<th class="fk-req"><p>生年月日</p></th>
						<td>
							<div data-fk-group="_birthday">
								<input type="text" name="birth_year" value="<?= $birth_year ?>" placeholder="西暦でご記入ください"> /
								<input type="text" name="birth_month" value="<?= $birth_month ?>"> /
								<input type="text" name="birth_day" value="<?= $birth_day ?>">
								<?= $_birthday->marker_tag() ?>
								<?= $_birthday->error_tag() ?>
							</div>						
						</td>
					</tr>
					<tr>
						<th class="fk-req">お問い合わせ内容</th>
						<td>
							<textarea name="comment"><?= $comment ?></textarea>
							<?= $comment->marker_tag() ?>
							<?= $comment->error_tag() ?>
						</td>
					</tr>
				</tbody>
			</table>
		</section>
		<div class="submit">
			<button>次へ</button>
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
