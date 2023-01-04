<?php $Config['before_validates'] = '' ?>
<?php require 'formkit/app/fk-input.php' ?>
<!DOCTYPE html>
<html>
<head>
<meta charset="utf-8">
<link rel="stylesheet" href="formkit/app/fk.css">
<link rel="stylesheet" href="custom.css">
<link rel="stylesheet" href="styles/style.css">
<meta name="viewport" content="width=device-width">
<title>入力画面(step1)</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span class="now">入力画面(商品選択)</span> &gt;
		<span>入力画面(お客様情報)</span> &gt;
		<span>入力画面(お支払い方法)</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form action="step2.php" method="post">
		<?= \FK\hiddens_tag() ?>
        <section id="products">
            <h1 class="main_title">商品一覧</h1>
            <h4 class="sub_title">products</h4>
            <div class="product">
                <div class="product_item">
                    <input type="radio" name="support" value="クラブ接続サポート<?= $support->checked('クラブ接続サポート') ?>">
                    <form>
                    <div class="product_inner">
                        <h2>クラブ接続<br>サポート</h2>
                        <img src="/images/service01.png" alt="クラブ接続サポート">                
                        <p>770円<span>(税込)/月</span></p>
                        <p>(1ヶ月無料)</p>
                    </div>
                </div>
                 <div class="product_item">
                    <input type="radio" name="support" value="クラブ駆け付けサポート<?= $support->checked('クラブ駆け付けサポート') ?>">
                    <div class="product_inner">
                        <h2>クラブ駆け付け<br>サポート</h2>
                        <img src="/images/service02.png" alt="クラブ駆け付けサポート">                
                        <p>770円<span>(税込)/月</span></p>
                        <p>(1ヶ月無料)</p>
                    </div>    
                </div>
                <div class="product_item">
                    <input type="radio" name="support" value="クラブメディカル<?= $support->checked('クラブメディカル') ?>">
                    <div class="product_inner">
                        <h2>クラブ<br>メディカル</h2>
                        <img src="/images/service03.png" alt="クラブメディカル">               
                        <p>770円<span>(税込)/月</span></p>
                        <p>(1ヶ月無料)</p>
                    </div>
                </div>
                <div class="product_item">
                    <input type="radio" name="support" value="クラブ保証<?= $support->checked('クラブ保証') ?>">
                    <div class="product_inner">
                        <h2>クラブ保証<br>&nbsp;</h2>
                        <img src="/images/service04.png" alt="クラブ保証">                
                        <p>770円<span>(税込)/月</span></p>
                        <p>(1ヶ月無料)</p>
                    </div>    
                </div>
           </div>
		   <?= $support->error_tag() ?>
        </section>
		<div class="submit">
			<input type="submit" value="次へ">
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
