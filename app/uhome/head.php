	<link type="text/css" rel="stylesheet" href="../course/style.css" />
	<link type="text/css" rel="stylesheet" href="../course/mobile.css" media="screen and (max-width:800px)" />
	<link type="text/css" rel="stylesheet" href="./style.css" />

<style>
    #main_video_win iframe{
        width:100%;
        height:100%;
    }
    #main_tag span{
        margin: 2px;
        color:black;
        padding: 2px 12px 0 12px;
        font-weight: 500;
        transition-duration: 0.2s;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        flex-wrap: nowrap;
        justify-content: center;
        font-size:110%;
        border: unset;
        border-radius: 0;
        border-bottom: 2px solid var(--nocolor);
    }
    #main_tag span:hover{
        background-color:unset;
        border-color: var(--link-hover-color);
    }
    #main_tag .select{
        border-bottom: 2px solid var(--link-color);
    }
    .index_inner .icon_btn .icon{
        fill: var(--btn-hover-bg-color);
    }
    .index_inner .icon_btn:hover .icon{
        fill: var(--btn-bg-color);
    }
    #footer_nav{
        display: none;
    }
	.user_home_category{
		display:flex;
	}
	.user_home_category li{
		font-size: 16px;
		margin-right:2em;
		padding:5px;
	}

</style>

<script src="./uhome.js"></script>

<div id='course_head_bar' >
    <div class='section_inner'>
			<div id='course_info_head' class='course_info_block <?php if(isset($currChannal) && $currChannal!="index"){echo "compact";}?>'>
				<div id='course_info_head_1'>
					<div id='course_info_head_face'>
						<img src='../../tmp/images/user/<?php echo $_GET["userid"];?>.jpg' />
					</div>
					<div id='course_info_head_title'>
						<div id='course_title'><?php echo ucenter_getA($_GET["userid"]);?></div>
						<div id='course_subtitle'></div>
						<div id='course_button'>
							<button class='disable'>+<?php echo $_local->gui->watch;?></button>
						</div>
					</div>
				</div>
			</div>

			<div id='' class='course_info_block'>
				<ul class="user_home_category">
					<li>
					<?php 
						if(isset($currChannal) && $currChannal=="index"){
							echo '<span class="select">'.$_local->gui->home.'</span>';
						}
						else{
							echo "<a href='index.php?userid={$_GET["userid"]}'>{$_local->gui->home}</a>";
						}
					?>
					</li>
					<li>
					<?php 
						if(isset($currChannal) && $currChannal=="palicanon"){
							echo '<span class="select">'.$_local->gui->translation.'</span>';
						}
						else{
							echo '<a href="palicanon.php?userid='.$_GET["userid"].'">'.$_local->gui->translation.'</a>';
						}
					?>		
					</li>
					<li>
					<?php 
						if(isset($currChannal) && $currChannal=="course"){
							echo '<span class="select">'.$_local->gui->lesson.'</span>';
						}
						else{
							echo '<a href="course.php?userid='.$_GET["userid"].'">'.$_local->gui->lesson.'</a>';
						}
					?>			
					</li>
					<li>
					<?php 
						if(isset($_GET["userid"]) && isset($_COOKIE["userid"]) ){
							if($_COOKIE["userid"]==$_GET["userid"]){
								$id = $_GET["userid"];
							}
							else{
								$id=false;
							}
						}
						else if(isset($_COOKIE["userid"])){
							$id=$_COOKIE["userid"];
						}
						else{
							$id = false;
						}
						if($id){
							if(isset($currChannal) && $currChannal=="foot-step"){
								echo '<span class="select">'.$_local->gui->EXP.'</span>';
							}
							else{
								echo '<a href="foot_step.php?userid='.$id.'">'.$_local->gui->EXP.'</a>';
							}							
						}

					?>		
					</li>			
				</ul>
			</div>

    </div>
</div>