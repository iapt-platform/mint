<?php 
$word=$_GET["word"];

 ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD HTML 4.01//EN" "http://www.w3.org/TR/html4/strict.dtd">
<html>

<frameset rows="50%,50%">

<frame src="ds_nav.php?word=<?php echo $word ?>" name="ds_nav">

<frameset cols="50%,50%">
<frame src="ds_modify.php?word=<?php echo $word ?>" name="ds_modify">
<frame src="ds_ref.php?word=<?php echo $word ?>" name="ds_ref">
</frameset>

</frameset>

</html>