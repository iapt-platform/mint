<div class="sutta_top_blank"></div>
<div class="sutta_top_blank"></div>
<div class="editor_wizard">
	<div class="editor_wizard_caption"><?php echo $module_gui_str['editor_wizard']['1006'];?></div>
	<div class="editor_wizard_nav">
		<button type="button" onclick="wizard_new_finish()"><?php echo $module_gui_str['editor_wizard']['1011'];?></button>
	</div>
		<!--right side begin-->
		<table>
			<tr>
			<td width="60%" style="vertical-align: top;">
				<div >
					<div id="wizard_sutta_preview">
						<?php echo $module_gui_str['editor_wizard']['1007'];?>
					</div>
				</div>
			</td>
			<td width="40%"  style="vertical-align: top;">
			<div >
				
				<div>
					<button type="button" onclick="wizard_fileNewPreview()"><?php echo $module_gui_str['editor_wizard']['1007'];?></button>

					<input id="chk_title" type="checkbox" checked="true" /><?php echo $module_gui_str['editor_wizard']['1008'];?>

				</div>
				<ul class="common-tab">
					<li id="NewFilePali" class="common-tab_li" onclick="wizard_show_input('new_input_pali','NewFilePali')"><?php echo $module_gui_str['editor_wizard']['1009'];?></li>
					<li id="NewFileTran1" class="common-tab_li" onclick="wizard_show_input('new_input_Tran1','NewFileTran1')"><?php echo $module_gui_str['editor_wizard']['1010'];?> 1</li>
					<li id="NewFileTran2" class="common-tab_li" onclick="wizard_show_input('new_input_Tran2','NewFileTran2')"><?php echo $module_gui_str['editor_wizard']['1010'];?> 2</li>
				</ul>
				<div id="new_input_pali">
				<h2>Pāli</h2>
				<input id="paliauthor" type="input" value="author"/>
				<p><textarea id="txtNewInput" rows="15" cols="100%"></textarea></p>
				</div>
				
				<div id="new_input_Tran1"  >
					<h2><?php echo $module_gui_str['editor_wizard']['1010'];?> 1</h2>
					<div id="tran1">				
						<select id="tranlanguage1">
							<option value="en" selected>English</option>				
							<option value="zh" >简体中文</option>
							<option value="tw" >繁體中文</option>
						</select>
						<input id="tranauthor1" type="input" value="author"/>
						<p><textarea id="txtNewInputTran1" rows="15" cols="100%" ></textarea></p>
					</div>
				</div>
				<div id="new_input_Tran2"  >
					<h2><?php echo $module_gui_str['editor_wizard']['1010'];?> 2</h2>
					<div id="tran2">
						<select id="tranlanguage2">
							<option value="en" >English</option>						
							<option value="zh" selected>简体中文</option>
							<option value="tw" >繁體中文</option>
						</select>
						<input id="tranauthor2" type="input" value="author"/>
						<p><textarea id="txtNewInputTran2" rows="15" cols="100%" ></textarea></p>
					</div>				
				</div>
			</div>
			</td>
			</tr>
			</table>
</div>
	
		<!--right side end-->

