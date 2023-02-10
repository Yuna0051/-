<?php $Config['before_validates'] = 'support,shimei,kana,email,email2,tel1,tel2,tel3,zip,pref,address1,address2,birth_year,birth_month,birth_day' ?>
<?php require 'formkit/app/fk-check.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
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
		<span>入力画面(商品選択)</span> &gt;
		<span>入力画面(お客様情報)</span> &gt;
		<span class="now">確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form method="post">
		<?= \FK\hiddens_tag() ?>
		<section id="check">
			<h1 class="main_title">確認画面</h1>
			<h4 class="sub_title">check</h4>
			<table>
				<tbody>
					<tr>
						<th class="fk-req"><p>商品選択</p></th>
						<td>
							<?= $support->join('、') ?>
						</td>
					</tr>
					<tr>
						<th class="fk-req"><p>お名前</p></th>
						<td>
							<?= $shimei ?>
						</td>
					</tr>
					<tr>
						<th class="fk-req"><p>お名前（ふりがな）</p></th>
						<td>
							<?= $kana ?>
						</td>
					</tr>
					<tr>
						<th class="fk-req">メールアドレス</th>
						<td><?= $email->mail_link_tag() ?></td>
					</tr>
					<tr>
						<th class="fk-req">お電話番号</th>
						<td><?= $tel1 ?>-<?= $tel2 ?>-<?= $tel3 ?></td>
					</tr>
					<tr>
						<th class="fk-req">ご住所</th>
						<td>
							〒：<?= $zip ?><br>
							都道府県：<?= $pref ?><br>
							市区町村/番地：<?= $address1 ?><br>
							建物名/階など：<?= $address2 ?>	
						</td>				
					</tr>
					<tr>
						<th class="fk-req">生年月日</th>
						<td><?= $birth_year ?>年 <?= $birth_month ?>月 <?= $birth_day ?>日</td>
					</tr>
				</tbody>
			</table>
		</section>
		<div class="submit">
			<button formaction="thanks.php" style="visibility: hidden; width:0; height:0; margin:0;">送信する</button>
			<button formaction="step2.php?revalidate" data-fk-no-validate class="prev">前へ</button>
			<button formaction="thanks.php">送信する</button>
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
                <li>TEL:0120-959-303</li>
                <li>対応時間:平日11〜18時</li>
            </ul>
        </div>
    </div>
    <small>&copy; クラブペイ.</small>
</footer>

<script src="formkit/app/fk.js"></script>

</body>
</html>
