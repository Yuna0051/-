<?php require 'formkit/app/fk-check.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<title>確認画面 - 複数ページサンプルフォーム - FormKit</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span>入力画面(商品選択)</span> &gt;
		<span>入力画面(お客様情報)</span> &gt;
		<span>入力画面(お支払い方法)</span> &gt;
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
							<?= $support ?>
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
						<th class="fk-req">メールアドレス</th>
						<td><?= $email->mail_link_tag() ?></td>
					</tr>
					<tr>
						<th class="fk-req"><p>同意して下さい。</p></th>
						<td>
							<?= $agree ?>
						</td>
					</tr>
				</tbody>
			</table>
		</section>
		<div class="submit">
			<button formaction="step3.php?revalidate" data-fk-no-validate>前へ</button>
			<button formaction="thanks.php">送信する</button>
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
