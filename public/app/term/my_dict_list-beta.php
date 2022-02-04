<?php
require_once '../studio/index_head.php';
?>
<body id="file_list_body"  onLoad="my_term_onload()">
	<script src="../channal/channal.js"></script>

    <script src="../term/my_term.js"></script>
    <script src="../term/term_edit_dlg.js"></script>
	<link type="text/css" rel="stylesheet" href="../term/term_edit_dlg.css"/>
	<script >
	var gCurrPage="term";
	</script>

	<style>
	#term {
		background-color: var(--btn-border-color);

	}
	#term:hover{
		background-color: var(--btn-border-color);
		color: var(--btn-color);
		cursor:auto;
    }
    #word_list{
        width:unset;
    }
	#setting_user_dict_nav{
		width:95%;
		display:inline-flex;
		justify-content: space-between;
}
	}
	</style>

<?php
require_once '../studio/index_tool_bar.php';
?>

	<div class="index_inner" style="    margin-left: 18em;margin-top: 5em;">
		<div id="word_list"  class="file_list_block">

		<div class="tool_bar">
	<div>
	<?php echo $_local->gui->userdict; ?>
	</div>

	<div>
		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->add; ?></span>
			<button id="file_add" type="button" class="icon_btn" title=" ">
				<a href="../course/my_channal_new.php">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
				</svg>
				</a>
			</button>
		</span>

		<span class="icon_btn_div">
			<span class="icon_btn_tip"><?php echo $_local->gui->recycle_bin; ?></span>
			<button id="to_recycle" type="button" class="icon_btn" title="delete">
				<svg class="icon">
					<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
				</svg>
			</button>
		</span>
	</div>

</div>
<script src="../../node_modules/gridjs/dist/gridjs.umd.js"></script>
<link
      href="../../node_modules/gridjs/dist/theme/mermaid.min.css"
      rel="stylesheet"
    />

<div id="userfilelist"></div>
<script  type="module" src="my_dict_list.js"></script>
<?php
require_once '../studio/index_foot.php';
?>