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
						<!--<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#dhammacakkha"></use>
						</svg>-->	
						<svg class="icon" xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" version="1.1" id="dhammacakkha" width="24" height="24" viewBox="0 0 60 60" style="enable-background:new 0 0 60 60;" xml:space="preserve">
							<path d="M30,0C13.458,0,0,13.458,0,30s13.458,30,30,30s30-13.458,30-30S46.542,0,30,0z M50.236,42.907  c-0.208,0.325-0.424,0.644-0.648,0.959c-0.032,0.045-0.063,0.091-0.096,0.136c-0.214,0.298-0.437,0.589-0.664,0.877  c-0.045,0.057-0.088,0.115-0.134,0.171c-0.221,0.274-0.45,0.541-0.683,0.805c-0.054,0.062-0.106,0.125-0.161,0.187  c-0.285,0.317-0.578,0.628-0.88,0.929c-0.303,0.302-0.614,0.596-0.932,0.882c-0.056,0.05-0.114,0.097-0.17,0.147  c-0.27,0.238-0.544,0.473-0.824,0.699c-0.051,0.041-0.103,0.08-0.154,0.12c-0.293,0.232-0.591,0.459-0.895,0.678  c-0.04,0.029-0.081,0.057-0.122,0.086c-0.318,0.226-0.641,0.445-0.97,0.655c-0.016,0.01-0.032,0.02-0.048,0.03l-8.026-13.899  c0.02-0.015,0.037-0.035,0.058-0.05c0.535-0.415,1.015-0.895,1.43-1.43c0.016-0.02,0.035-0.037,0.05-0.058l13.899,8.026  C50.257,42.873,50.247,42.89,50.236,42.907z M9.769,17.086c0.204-0.318,0.416-0.63,0.634-0.938c0.037-0.052,0.073-0.105,0.111-0.157  c0.208-0.289,0.424-0.571,0.645-0.851c0.051-0.065,0.101-0.131,0.153-0.196c0.213-0.264,0.434-0.522,0.659-0.777  c0.062-0.07,0.121-0.143,0.183-0.213c0.284-0.316,0.576-0.625,0.876-0.925c0.302-0.302,0.612-0.594,0.929-0.88  c0.062-0.056,0.126-0.108,0.188-0.163c0.263-0.232,0.53-0.461,0.803-0.681c0.057-0.046,0.116-0.09,0.174-0.136  c0.287-0.227,0.579-0.45,0.876-0.664c0.043-0.031,0.087-0.061,0.131-0.092c0.319-0.226,0.642-0.445,0.972-0.656  c0.014-0.009,0.027-0.017,0.041-0.026l8.025,13.899c-0.02,0.015-0.037,0.035-0.058,0.05c-0.535,0.415-1.015,0.895-1.43,1.43  c-0.016,0.02-0.035,0.037-0.05,0.058L9.733,17.144C9.745,17.125,9.757,17.106,9.769,17.086z M36.318,25.111  c-0.415-0.535-0.895-1.015-1.43-1.43c-0.02-0.016-0.037-0.035-0.058-0.05l8.017-13.885c2.977,1.895,5.511,4.429,7.406,7.406  l-13.885,8.017C36.353,25.149,36.334,25.132,36.318,25.111z M25.169,36.369l-8.018,13.886c-2.977-1.895-5.511-4.429-7.406-7.406  l13.886-8.018c0.015,0.02,0.035,0.037,0.05,0.058c0.415,0.535,0.895,1.015,1.43,1.43C25.132,36.334,25.149,36.353,25.169,36.369z   M35,30c0,2.758-2.242,5-5,5s-5-2.242-5-5s2.242-5,5-5S35,27.242,35,30z M33.102,22.627c-0.66-0.279-1.367-0.465-2.102-0.557V6.025  c3.578,0.146,7.018,1.076,10.123,2.709L33.102,22.627z M29,22.069c-0.736,0.092-1.443,0.278-2.102,0.557l-8.025-13.9  C21.977,7.098,25.418,6.171,29,6.025V22.069z M22.627,26.898c-0.279,0.66-0.465,1.367-0.557,2.102H6.024  c0.146-3.582,1.074-7.023,2.703-10.127L22.627,26.898z M22.069,31c0.092,0.735,0.278,1.442,0.557,2.102L8.733,41.124  C7.101,38.018,6.171,34.578,6.024,31H22.069z M26.898,37.374c0.66,0.279,1.367,0.465,2.102,0.557v16.045  c-3.578-0.146-7.018-1.076-10.124-2.709L26.898,37.374z M31,37.931c0.736-0.092,1.443-0.278,2.102-0.557l8.025,13.9  C38.023,52.902,34.582,53.83,31,53.976V37.931z M37.373,33.102c0.279-0.66,0.465-1.367,0.557-2.102h16.045  c-0.146,3.582-1.073,7.023-2.702,10.128L37.373,33.102z M37.931,29c-0.092-0.736-0.278-1.442-0.557-2.102l13.893-8.021  c1.632,3.105,2.562,6.545,2.709,10.123H37.931z"/>
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
				<li id="share_doc" style="display:none;" onclick="goto_url(this,'../studio/index_share.php?language=<?php echo $currLanguage; ?>')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#collabration_2"></use>
						</svg>
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->collaborate;?>
					</span>
				</li>
				<li id="group_index"  onclick="goto_url(this,'../group/index.php?language=<?php echo $currLanguage; ?>')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#ic_two_person"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->group;?>
					</span>
				</li>
				<li id="recycle_bin" style="display:none;" onclick="goto_url(this,'../studio/recycle.php')">
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
						<?php echo $_local->gui->academy;?>
					</span>
				</li>

				<li id="channal"  onclick="goto_url(this,'../channal/my_channal_index.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/svg/icon.svg#channel_leaves"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->channel;?>
					
					</span>
				</li>

				<li id="udict"  onclick="goto_url(this,'../udict/my_dict_list.php')">
					<span  class="navi_icon">
						<svg t="1599756246402" class="icon" viewBox="0 0 1024 1024" version="1.1" xmlns="http://www.w3.org/2000/svg" p-id="2108" width="24" height="24"><path d="M414.737993 572.87622h206.71925L518.097618 346.364701z" p-id="2109"></path><path d="M837.672784 118.253807H198.222569V95.362749h693.928934c30.088247 0 54.478719-21.291683 54.47872-47.681375 0-26.289731-24.390472-47.681374-54.47872-47.681374H143.943772c-17.893011 0-33.686841 7.497071-43.682936 19.192503-6.897306 7.996876-10.995705 17.893011-10.995705 28.688793v147.642328c0 2.499024 0.199922 4.998048 0.699726 7.497071-0.399844 3.498633-0.699727 6.997267-0.699726 10.4959v714.820773c0 52.379539 48.980867 95.262788 108.857477 95.262788h639.550176c59.876611 0 108.857478-42.883249 108.857478-95.262788V213.516595c0.099961-52.379539-48.880906-95.262788-108.857478-95.262788z m-81.168294 615.459586c-4.19836 7.29715-14.194455 10.895744-29.888324 10.895744-10.295978 0.399844-18.592737-1.699336-24.890278-6.29754-6.29754-4.598204-10.995705-10.695822-14.094494-18.192893l-49.880515-101.060524H397.444748l-37.185474 75.970324c-2.998829 6.197579-6.097618 12.095275-9.096447 17.79305-2.998829 5.697774-6.497462 10.995705-10.395939 15.893792-3.898477 4.898087-8.496681 8.696603-13.59469 11.59547-5.098009 2.898868-10.995705 4.298321-17.693088 4.298321-12.095275 0-21.491605-2.798907-28.089028-8.296759-6.697384-5.497852-6.697384-15.69387 0-30.688013l190.425615-403.442405c2.998829-6.997267 8.296759-12.695041 15.893791-16.79344 7.597032-4.19836 17.393206-6.29754 29.488482-6.29754 12.695041 0 22.991019 2.09918 30.787973 6.29754 7.896915 4.19836 13.294807 9.796173 16.293635 16.79344l191.325264 404.841859c4.798126 10.4959 5.098009 19.392425 0.899648 26.689574z" p-id="2110"></path></svg>
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->userdict;?>
					
					</span>
				</li>

				<li id="term"  onclick="goto_url(this,'../term/my_dict_list.php')">
					<span  class="navi_icon">
						<svg class="icon">
							<use xlink:href="../studio/plugin/system_term/icon.svg#icon_term"></use>
						</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->wiki_term;?>
					</span>
				</li>

				<li id="article"  onclick="goto_url(this,'../article/my_article_index.php')">
					<span  class="navi_icon">
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#article_1"></use>
					</svg>	
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->text."DIY";?>
					</span>
				</li>

				<li id="collect"  onclick="goto_url(this,'../article/my_collect_index.php')">
					<span  class="navi_icon">
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#collection"></use>
					</svg>					
					</span>	
					<span class="navi_text">
					<?php echo $_local->gui->anthology;?>
					</span>
				</li>


			</ul>
		</div>
		
		<!-- tool bar begin-->
		<div class='index_toolbar'>
			<div id="index_nav">
			<a href="../pcdl/">
				<svg class="icon" style="height: 3em;width: 15em;padding-top: 3px;">
					<use xlink:href="../public/images/svg/wikipali_banner.svg#wikipali_banner"></use>
				</svg>
			</a>
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
			include "../ucenter/user.php";
			include "../lang/lang.php";
			?>			
			</div>
		</div>	
		<!--tool bar end -->
		