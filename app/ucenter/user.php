	<!--显示模式-->
	<script>
		var g_langrage="en";
		var g_currLink="";
		function user_init(strPage){
			g_currLink = strPage;
		}

	</script>
	<style>
	#user_info {
		background-color: var(--tool-bg-color2);
	}
	#user_info::after {
    margin-right: 10px;
	}
	.dropdown-content a {
		cursor: pointer;
	}
	#user_info{
		width:20em;
	}
	#user_info_welcome{
    border-bottom: 1px solid var(--tool-line-color);
    padding: 10px;
	}
	#user_info_name{
		font-size:200%;
	}
	#user_info_welcome2{
		text-align:right;
	}
	#user_bar{
		border: 2px solid var(--btn-border-color);
		border-radius: 99px;
		color: var(--btn-color);
		padding: 2px 2px 2px 15px;
		height: min-content;
		display: flex;
	}
	.new_account{
		border: 2px solid var(--btn-border-color);
		border-radius: 99px;
		color: var(--btn-color);
		padding: 5px 10px;
		height: min-content;
	}
	.new_account:hover{
		background: var(--btn-border-color);

	}

	</style>
		<div style="margin:auto;" class="dropdown" onmouseover="switchMenu(this,'user_info')" onmouseout="hideMenu()">
			
				<?php
				if(isset($_COOKIE["userid"])){
				?>
			<div id="user_bar" >
				<span style="padding: 4px 0;">
					<?php echo $_COOKIE["nickname"]; ?>
				</span>
				<button class="dropbtn icon_btn" onClick="switchMenu(this,'user_info')" id="use_mode">	
					<svg class="icon" xmlns="http://www.w3.org/2000/svg"  viewBox="0 0 32 32" id="ic_user_32px" style="fill: var(--tool-link-hover-color);">
						<path d="M20,4A16,16,0,1,0,36,20,16,16,0,0,0,20,4Zm0,4.8a4.8,4.8,0,1,1-4.8,4.8A4.8,4.8,0,0,1,20,8.8Zm0,22.72a11.521,11.521,0,0,1-9.6-5.152c.04-3.176,6.408-4.928,9.6-4.928s9.552,1.752,9.6,4.928A11.521,11.521,0,0,1,20,31.52Z" transform="translate(-4 -4)"/></svg>
				</button>
			</div>
			<div class="dropdown-content" id="user_info">
				<div id="user_info_welcome">
				<div id="user_info_welcome1"><?php echo $_local->gui->welcome;?></div>
				<div id="user_info_name"><?php echo $_COOKIE["nickname"];?></div>
				<div id="user_info_welcome2"><?php echo $_local->gui->to_the_dhamma;?></div>
				</div>
				<a href="../studio/setting.php" target="_blank">
					<span>
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#ic_settings"></use>
					</svg>
						<?php echo $_local->gui->setting;//用户设置?>
					</span>
				</a>
				<a href="../sync" target="_blank">
					<span>
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#ic_autorenew_24px"></use>
					</svg>
						<?php echo $_local->gui->sync;//同步数据?>
					</span>
				</a>
				<a href='../ucenter/index.php?op=logout'>
					<svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#ic_exit_to_app_24px"></use>
					</svg>
					<?php echo $_local->gui->logout;?>
				</a>
			</div>

				<?php
				}
				else{
					?>
			<span style="display: flex;">
				<div  style="padding: 7px; margin-right: 10px;"><a href='../ucenter/'><?php echo $_local->gui->login;?></a></div>
				<div class="new_account"><a href='../ucenter/index.php?op=new'><?php echo $_local->gui->new_account;?></a></div>
			</span>
				<?php
				}
				?>


		</div>