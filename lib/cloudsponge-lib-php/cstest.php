<html> 
<head> 
  <title>URL Test</title> 
</head> 
<body> 
<form method="get">
URL:<br/> 
<input type="text" size="60" name="url" value="<?=$_GET['url']?>"/> <br />
FORM Body:<br /> 
<textarea rows="10" cols="40" name="body"><?=$_GET['body']?></textarea> <br />
<input type="submit"/>
</form>

<hr/>

<?php
  require_once('csimport.php');
  
//  print_r($_GET['url']);
//  print_r($_GET['body']);
//  echo CSImport::forward_auth($_GET, $_POST);
  if ($_GET['body']) {
    print "posting to url: {$_GET['url']} <br/>";
    print_r(CSImport::post_url($_GET['url'], $_GET['body']));
  } else {
    print "getting url: " + $_GET['url'] + "<br/>";
    print_r(CSImport::get_url($_GET['url']));
  }

?>
<hr />

</body>
</html>

