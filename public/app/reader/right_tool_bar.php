
	<script src="../reader/right_tool_bar.js"></script>
<style>

#min_right_float {
	display: none;
}
.main_view_right_float_min {
	margin-right: calc(30vw - 1.6em);
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
.btn_main {
    background-color: var(--link-hover-color);
    color: var(--btn-hover-color);
}
.btn_main:hover {
    background-color: var(--link-color);
}

.btn_group {
    display: inline-block;
    background-color: var(--border-line-color);
    border-radius: 4px;
    padding: 3px 2px;
    margin: 0 3px;
}
.btn_focus {
    background-color: var(--bg-color);
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
			echo "<option value='sent' ";
			if($_display=="sent"){
				echo "selected";
			}
			echo ">{$_local->gui->each_sentence}</option>";
			echo "</select>";
		}
?>
<div class="btn_group">
<?php
		if($_mode == "read"){
			echo "<span class='icon_btn btn_focus'  ";
			echo " title='{$_local->gui->show} {$_local->gui->read}'>";
			echo $_local->gui->read;
			echo "</span>";

			echo "<button class='icon_btn' onclick=\"setMode('edit')\" ";
			echo " title='{$_local->gui->show} {$_local->gui->edit}'>";
			echo $_local->gui->translate;
			echo "</button>";
		}
		else{
			echo "<button class='icon_btn' onclick=\"setMode('read')\" ";
			echo " title='{$_local->gui->show} {$_local->gui->read}'>";		
			echo $_local->gui->read;
			echo "</button>";

			echo "<span class='icon_btn btn_focus'  ";
			echo " title='{$_local->gui->show} {$_local->gui->edit}'>";
			echo $_local->gui->translate;
			echo "</span>";
		}
?>
</div>
				<button id="btn_show_dict" class='icon_btn' style="color: var(--main-color);" onclick="show_dict(this)">
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
