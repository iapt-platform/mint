<?php
include_once( 'ckeditor/ckeditor.php' ) ;
?>

<html>
<head>
</head>
<body>
<?php 
$CKEditor = new CKEditor();
$config = array();
$config['toolbar'] = array(
	array( 'Source', '-', 'Bold', 'Italic', 'Underline', 'Strike' ),
	array( 'Image', 'Link', 'Unlink', 'Anchor' )
	);
/*
$events['instanceReady'] = 'function (ev) {
		alert("Loaded: " + ev.editor.name);
		}';	
*/
$events=array();
$id="id_text";
$CKEditor->textareaAttributes = array( "rows" => 8, "cols" => 30 , "id"=>"id_text");
$CKEditor->editor("field1", "<p>Initial value.</p>", $config, $events);
?>
</body>
</html>