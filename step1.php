<?php $Config['before_validates'] = '' ?>
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
<title>入力画面(商品選択)</title>
</head>
<body>
<header>
		<a href="/index.html" class="BtnHome"><img src="/images/クラブペイ.png" alt="クラブペイ"></a>
</header>
<main>
	<div class="steps">
		<span class="now">入力画面(商品選択)</span> &gt;
		<span>入力画面(お客様情報)</span> &gt;
		<span>確認画面</span> &gt;
		<span>完了画面</span>
	</div>
	<noscript>JavaScriptを有効にして下さい。</noscript>
	<form action="step2.php" method="post">
		<?= \FK\hiddens_tag() ?>
        <section id="products">
            <h1 class="main_title">商品一覧</h1>
            <h4 class="sub_title">products</h4>
            <div class="checkAll">
                <input type="button" onClick="checkAllBox(true)" value="全選択">
                <input type="button" onClick="checkAllBox(false)" value="全解除">
            </div>
            <div class="product">
                <div class="product_item">
                    <input type="checkbox" name="support[]" value="クラブ接続サポート<?= $support->checked('クラブ接続サポート') ?>">
                    <div class="product_inner">
                        <h2>クラブ接続<br>サポート</h2>
                        <img src="/images/service01.png" alt="クラブ接続サポート">                
                        <p>770円<span>(税込)/月</span></p>
                        <p>(最大2ヶ月無料)</p>
                    </div>
                    <a href="download/support1.pdf" download>約款PDF</a>
                </div>
                 <div class="product_item">
                    <input type="checkbox" name="support[]" value="クラブ駆け付けサポート<?= $support->checked('クラブ駆け付けサポート') ?>">
                    <div class="product_inner">
                        <h2>クラブ駆け付け<br>サポート</h2>
                        <img src="/images/service02.png" alt="クラブ駆け付けサポート">                
                        <p>660円<span>(税込)/月</span></p>
                        <p>(最大2ヶ月無料)</p>
                    </div>    
                    <a href="download/support2.pdf" download>約款PDF</a>
                </div>
                <div class="product_item">
                    <input type="checkbox" name="support[]" value="クラブガード<?= $support->checked('クラブガード') ?>">
                    <div class="product_inner">
                        <h2>クラブガード<br>&nbsp;</h2>
                        <img src="/images/service03.png" alt="クラブガード">               
                        <p>770円<span>(税込)/月</span></p>
                        <p>(最大2ヶ月無料)</p>
                    </div>
                    <a href="download/support3.pdf" download>約款PDF</a>
                </div>
                <div class="product_item">
                    <input type="checkbox" name="support[]" value="クラブ保証<?= $support->checked('クラブ保証') ?>">
                    <div class="product_inner">
                        <h2>クラブ保証<br>&nbsp;</h2>
                        <img src="/images/service04.png" alt="クラブ保証">                
                        <p>770円<span>(税込)/月</span></p>
                        <p>(最大2ヶ月無料)</p>
                    </div>    
                    <a href="download/support4.pdf" download>約款PDF</a>
               </div>
           </div>
		   <?= $support->error_tag() ?>
        </section>
        <div class="privacy_check">
            <label><input type="checkbox" name="privacy" ?>　個人情報取扱の内容を確認した上で同意します。<a href="download/privacypolicy.pdf">個人情報の取扱についてを表示</a></label><br>
            <?= $privacy->error_tag() ?>
        </div>
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
                <li>TEL:0120-959-303</li>
                <li>対応時間:平日11〜18時</li>
            </ul>
        </div>
    </div>
    <small>&copy; クラブペイ.</small>
</footer>

<script>
const checkSupport = document.getElementsByName("support[]")

function checkAllBox(trueOrFalse) {
  for(i = 0; i < checkSupport.length; i++) {
    checkSupport[i].checked = trueOrFalse
  }
}
</script>
<script src="formkit/app/fk.js"></script>

</body>
</html>
