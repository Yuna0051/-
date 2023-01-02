<!DOCTYPE html>
<html lang="ja">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title><?php echo $code ?> ERROR</title>
</head>
<body>
<h1><?php echo $code ?> ERROR</h1>
<p><?php echo $message ?></p>
<?php if($detail){ ?>
<hr>
<ul>
	<?php foreach((is_array($detail) ? $detail : [$detail]) as $val){ ?>
	<li><?php echo h($val) ?></li>
	<?php } ?>
</ul>
<?php } ?>
<!-- Own local error page of IE is padding up to 512 bytes so that it does not appear : not delete this line ! -->
<!-- Own local error page of IE is padding up to 512 bytes so that it does not appear : not delete this line ! -->
<!-- Own local error page of IE is padding up to 512 bytes so that it does not appear : not delete this line ! -->
<!-- Own local error page of IE is padding up to 512 bytes so that it does not appear : not delete this line ! -->
<!-- Own local error page of IE is padding up to 512 bytes so that it does not appear : not delete this line ! -->
</html>
