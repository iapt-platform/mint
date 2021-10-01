
	<script src="../reader/right_tool_bar.js"></script>
<style>
	#right_float_pannal {
	position: fixed;
	height: calc(100% - 7.3em);
	top: 7.3em;
	left: 100%;
	width: calc(30vw - 0.6em);
	min-width: 20vw;
	color: var(--btn-color);
	background-color: var(--tool-bg-color);
	z-index: 20;
	-webkit-transition-duration: 0.4s;
	transition-duration: 0.4s;
	-webkit-contain: strict;
	contain: strict;
	z-index: 51;
}
#right_float_pannal > iframe {
	width: 100%;
	height: 100%;
}
.right_float_max #right_float_pannal {
	left: 50%;
	width: 50%;
}

.right_float_min #right_float_pannal {
	left: calc(100% - 30vw + 0.6em);
}
#right_float_pannal > #tool_bar {
	/*position: absolute;*/
	display: flex;
	width: 100%;
	justify-content: space-between;
}
#right_float_pannal > #tool_bar svg {
	fill: var(--bg-color);
	height: 2em;
	width: 2em;
}
#min_right_float {
	display: none;
}
.main_view_right_float_min {
	margin-right: calc(30vw - 0.6em);
}
.main_view_right_float_max {
	margin-right: 50%;
}
.main_view_right_float_min #right_pannal {
	display: none;
}
.main_view_right_float_max #right_pannal {
	display: none;
}
	</style>
			<select name="direction" onchange='setDirection(this)' class="show_pc">
			<option value="row"><?php echo $_local->gui->row_compare; ?></option>
			<option value="column"><?php echo $_local->gui->column_compare; ?></option>
		</select>
<?php
		if($_mode == "read"){
			echo "<select onchange='setDisplay(this)'>";
			echo "<option value='para'>{$_local->gui->each_paragraph}</option>";
			echo "<option value='sent'>{$_local->gui->each_sentence}</option>";
			echo "</select>";
		}
		if($_mode == "read"){
			echo "<button class='icon_btn active' title='{$_local->gui->show} {$_local->gui->each_paragraph}'>";
			echo $_local->gui->read;
			echo "</button>";

			echo "<button class='icon_btn'>";
			echo "<a onclick=\"setMode('edit')\"";

			echo " title='{$_local->gui->show} {$_local->gui->edit}'>{$_local->gui->edit}</a></button>";
		}
		else{
			echo "<button class='icon_btn'>";
			echo "<a onclick=\"setMode('read')\"";
			echo " title='{$_local->gui->show} {$_local->gui->read}'>";		
			echo $_local->gui->read;
			echo "</a>";
			echo "</button>";

			echo "<button class='icon_btn active'  title='{$_local->gui->show} {$_local->gui->edit}'>";
			echo $_local->gui->edit;
			echo "</button>";
		}
?>

				<button id="btn_show_dict" class='icon_btn' onclick="show_dict(this)">
				<?php echo $_local->gui->dict; ?>
				</button>
<div id="main_view_shell">
	<div id="right_float_pannal">
		<div id="tool_bar">
			<span>
			<button id="max_right_float" class="icon_btn" onclick="max_right_float(this)">
				<svg class='icon'><use xlink:href='../studio/svg/icon.svg#left_expand'></use></svg>
			</button>
			<button id="min_right_float" class="icon_btn" onclick="min_right_float(this)">
				<svg class='icon'><use xlink:href='../studio/svg/icon.svg#right_expand'></use></svg>
			</button>
			</span>
			<span id="tool_bar_title" style="font-size: 150%;">
				<?php echo $_local->gui->searching_instruction; ?><guide gid="dict_search_input" init="1"></guide>
			</span>
			<span>
			<button id="close_right_float" class="icon_btn" onclick="close_right_float()">
			<svg class='icon'><use xlink:href='../studio/svg/icon.svg#cross_with_circle'></use></svg>
			</button>
			</span>
		</div>
		<iframe id="dict" src="../dict/index.php?builtin=true" name="dict" title="wikipali"></iframe>
	</div>
</div>

<script>
	guide_init();
</script>
