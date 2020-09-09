<script>
		
	function goto_url(obj,url){
		var id=obj.getAttributeNode("id").value;
		if(id!=gCurrPage){
			window.location.assign(url);
		}
	}
	
</script>
		<div class="index_left_panal">
			<ul class="navi_button">
				<li id="pali_canon" onclick="goto_url(this,'../studio/index_pc.php?language=<?php echo $currLanguage; ?>')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#ic_add_circle"></use>
						</svg>	
					</span>	
					<span class="navi_text">
						<?php echo $_local->gui->pali_canon;?>
					</span>
				</li>
				<li id="recent_scan" onclick="goto_url(this,'../studio/index_recent.php?language=<?php echo $currLanguage; ?>')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#recent_scan"></use>
						</svg>	
					</span>	
					<span class="navi_text">				
					<?php echo $_local->gui->recent_scan;?>
					</span>
				</li>
				<li id="share_doc" onclick="goto_url(this,'../studio/index_share.php?language=<?php echo $currLanguage; ?>')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#collabration_2"></use>
						</svg>
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->collaborate;?>
					</span>
				</li>
				<li id="group_index"  onclick="goto_url(this,'../studio/group.php?language=<?php echo $currLanguage; ?>')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#ic_two_person"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->group;?>
					</span>
				</li>
				<li id="recycle_bin"  onclick="goto_url(this,'../studio/recycle.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#ic_delete"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->recycle_bin;?>
					</span>
				</li>
				<li id="course"  onclick="goto_url(this,'../course/my_course_index.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#class_video"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->lesson;?>
					
					</span>
				</li>

				<li id="channal"  onclick="goto_url(this,'../channal/my_channal_list.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#class_video"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo "Channal";?>
					
					</span>
				</li>

				<li id="udict"  onclick="goto_url(this,'../udict/my_dict_list.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#class_video"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo "单词本";?>
					
					</span>
				</li>

				<li id="term"  onclick="goto_url(this,'../term/my_dict_list.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#class_video"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo "术语";?>
					
					</span>
				</li>				
			</ul>
		</div>
		
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
			<svg class="icon" style="height: 3.5em;width: 15em;padding-top: 15px;">
				<use xlink:href="../public/images/svg/wikipali_banner.svg#wikipali_banner"></use>
			</svg>
			</div>
			<div >
					<div>
						<div>
							<input id="search_input" type="input" placeholder='<?php echo $_local->gui->undone_function;?>' onkeyup="search_input_keyup(event,this)" style="margin-left: 0.5em;width: 40em;max-width: 80%" onfocus="search_input_onfocus()">
						</div>
						<div id="pre_search_result">
							<div id="pre_search_chapter" class="pre_serach_block">
								<div id="pre_search_chapter_title"   class="pre_serach_block_title">
									<div id="pre_search_chapter_title_left">我的文档</div>
									<div id="pre_search_chapter_title_right"></div>
								</div>
								<div id="pre_search_chapter_content"   class="pre_serach_content">
								</div>
							</div>
							<div id="pre_search_sent"  class="pre_serach_block">
								<div id="pre_search_sent_title"   class="pre_serach_block_title">
									<div id="pre_search_sent_title_left">群组文档</div>
									<div id="pre_search_sent_title_right"></div>								
								</div>
								<div id="pre_search_sent_content"   class="pre_serach_content">
								</div>
							</div>
							<div id="pre_search_word"  class="pre_serach_block">
								<div id="pre_search_word_title"   class="pre_serach_block_title">
									<div id="pre_search_word_title_left">
									<?php echo $_local->gui->group;?>
									</div>
									<div id="pre_search_word_title_right"></div>								
								</div>
								<div id="pre_search_word_content"   class="pre_serach_content">
								</div>
							</div>			
						</div>					
					</div>
				
			</div>
			<div class="toolgroup1">
			
			<?php 
			include "../lang/lang.php";
			include "../ucenter/user.php";
			?>			
			</div>
		</div>	
		<!--tool bar end -->
		