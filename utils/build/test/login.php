<?php
      Header( 'Cache-Control: no-cache, must-revalidate');
      Header( 'Pragma: no-cache');
    error_reporting(E_ALL | E_NOTICE | E_STRICT | E_WARNING );
    ini_set('display_errors', 1);

$js = json_decode(file_get_contents(__DIR__ . '/tests/_data/admin.json'));
?><!DOCTYPE html>
<html lang="$site.language">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
</head>

<body>
<form action="/admin/login/?backurl=<?php
//echo urlencode('/admin/plugins/');
echo urlencode('/admin/shop/hosting/');
?>" method="post" id="autoform">
<input type="hidden" name="email" value="<?php echo $js->email; ?>">
<input type="hidden" name="password" value="<?php echo $js->password; ?>">
<input type="hidden" name="remember" value="remember">
</form>
<script type="text/javascript">
document.getElementById('autoform').submit();
</script>
</body>

</html>