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
</style>

<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 0 10px;'>
    <div class='index_inner '>
        <div style='display:flex;'>
            <div style='font-size:280%;flex:7;'>
                <?php echo ucenter_getA($_GET["userid"]);?>
            </div>
            <div  style="display: inline-block;">
                <button class="icon_btn" title=<?php echo $_local->gui->watch;?>>
                    <svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#eye_enable"></use>
					</svg>
                </button>
                <button class="icon_btn" title=<?php echo $_local->gui->share_to;?>>
                    <svg class="icon">
						<use xlink:href="../studio/svg/icon.svg#share_to"></use>
					</svg>
                </button>
            </div>
        </div>

        <div id="main_tag"  style="">
            <span tag=<?php echo "jianjie";?>><?php echo $_local->gui->introduction;?></span>

            <a href="trans.php?userid=<?php echo $_GET["userid"];?>">
                <span tag=<?php echo $_local->gui->translation;?>>  
                    <?php echo $_local->gui->translation;?>
                </span>
            </a>
            <?php 
                if(isset($currChannal) && $currChannal=="course"){
                    echo '<span class="select" tag="'.$_local->gui->lesson.'">'.$_local->gui->lesson.'</span>';
                }
                else{
                    echo '<a href="course.php?userid='.$_GET["userid"].'"><span tag="'.$_local->gui->lesson.'">'.$_local->gui->lesson.'</span></a>';
                }
            ?>
            <a href="foot_step.php?userid=<?php echo $_GET["userid"];?>">
            <span tag=<?php echo $_local->gui->EXP;?>><?php echo $_local->gui->EXP;?></span>
            </a>
            <span tag=<?php echo $_local->gui->statistical_data;?>><?php echo $_local->gui->statistical_data;?></span>
        </div>
    </div>
</div>