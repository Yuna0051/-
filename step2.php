<?php $Config['before_validates'] = 'support' ?>
<?php require 'formkit/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<meta name="format-detection" content="telephone=no">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<title>入力画面(お客様情報)</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span>入力画面(商品選択)</span> &gt;
		<span class="now">入力画面(お客様情報)</span> &gt;
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
						<th class="fk-req"><p>お名前（ふりがな）</p></th>
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
					<tr>
						<th class="fk-req"><p>お電話番号</p></th>
						<td>
							<input type="tel" name="tel1" value="<?= $tel1 ?>"> -
							<input type="tel" name="tel2" value="<?= $tel2 ?>"> -
							<input type="tel" name="tel3" value="<?= $tel3 ?>">
							<!-- width:28% -->
							<?= $tel3->marker_tag() ?>
							<?= $tel3->error_tag() ?>						
						</td>
					</tr>
					<tr>
						<th class="fk-req"><p>ご住所</p></th>
						<td>
						〒 <input type="text" name="zip" value="<?= $zip ?>" data-fk-ajaxzip="'zip','','pref','address1'">
						<?= $zip->marker_tag() ?>
						<?= $zip->error_tag() ?>
						<br>
						都道府県
						<select name="pref">
						<option value="">（１つ選択して下さい）</option>
						<?= \FK\pref_options_tag($pref, '宮城県') ?>
						</select> <?= $pref->marker_tag() ?>
						<br>
						市区町村/番地 <input type="text" name="address1" value="<?= $address1 ?>">
						<?= $address1->marker_tag() ?>
						<?= $address1->error_tag() ?>
						<br>
						建物名/階など <input type="text" name="address2" value="<?= $address2 ?>">
						<?= $address2->marker_tag() ?>
						<?= $address2->error_tag() ?>
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
				</tbody>
			</table>
		</section>
		<div class="submit">
			<button formaction="check.php" style="visibility: hidden; width:0; height:0; margin:0;">確認画面へ</button>
			<button formaction="step1.php?revalidate" data-fk-no-validate class="prev">前へ</button>
			<button formaction="check.php">確認画面へ</button>
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
