
以下の内容でお申し込みフォームが送信されました。

送信日時：<?= $__DATEJP__ ?> <?= $__TIMEJP__ ?>

---------------------------------------

■ 商品選択
<?= $support->join('、') ?>

■ お名前
<?= $shimei ?>

■ お名前（ふりがな）
<?= $kana ?>

■ メールアドレス
<?= $email ?>

■ お電話番号
<?= $tel1 ?>-<?= $tel2 ?>-<?= $tel3 ?>

■ご住所
〒 <?= $zip ?>
<?= $pref ?> <?= $address1->empty_label() ?> <?= $address2->empty_label() ?>

■生年月日
<?= $birth_year ?>年 <?= $birth_month ?>月 <?= $birth_day ?>日

---------------------------------------

以上です。
