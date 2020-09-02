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
</style>

<div id='course_head_bar' style='background-color:var(--tool-bg-color1);padding:1em 10px 0 10px;'>
    <div class='index_inner '>
        <div style='display:flex;'>
            <div style='font-size:280%;flex:7;'>
                <?php echo ucenter_getA($_GET["userid"]);?>
            </div>
            <div  style="display: inline-block;"><button>关注</button><button>分享</button></div>
        </div>

        <div id="main_tag"  style="">
            <a href="trans.php?userid=<?php echo $_GET["userid"];?>"><span tag="译文">译文</span></a>
            <?php 
                if(isset($currChannal) && $currChannal=="course"){
                    echo '<span class="select" tag="课程">课程</span>';
                }
                else{
                    echo '<a href="course.php?userid='.$_GET["userid"].'"><span tag="课程">课程</span></a>';
                }
            ?>
            <span tag="经验">经验</span>
            <span tag="统计">统计</span>
        </div>
    </div>
</div>