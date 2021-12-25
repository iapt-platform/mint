<?PHP
include "../pcdl/html_head.php";
?>

<body>

    <?php
    require_once("../pcdl/head_bar.php");
    ?>
    <script language="javascript" src="../collect/index.js"></script>

    <style>
        h1 {
            font-size: 42px;
            font-weight: 700;
            margin: 0.3em 0;
        }

        h2 {
            font-size: 18px;
            font-weight: 700;
            margin: 0;
        }


        .disable {
            opacity: 0.4;
            cursor: not-allowed;
        }

        #main_tag span {
            margin: 2px;
            padding: 2px 12px;
            font-weight: 500;
            transition-duration: 0.2s;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            flex-wrap: nowrap;
            justify-content: center;
            font-size: 110%;
            border: unset;
            border-radius: 0;
            border-bottom: 2px solid var(--nocolor);
        }

        #main_tag span:hover {
            background-color: unset;
            color: unset;
            border-color: var(--link-hover-color);
        }

        #main_tag .select {
            border-bottom: 2px solid var(--link-color);
        }

        tag {
            background-color: var(--btn-color);
            margin: 0 0.5em;
            padding: 3px 5px;
            border-radius: 6px;
            display: inline-flex;
            border: 1.5px solid;
            border-color: #70707036;
        }

        tag .icon:hover {
            background-color: silver;
        }

        #footer_nav {
            display: none;
        }

        .index_inner .icon_btn .icon {
            fill: var(--btn-hover-bg-color);
        }

        .index_inner .icon_btn:hover .icon {
            fill: var(--btn-bg-color);
        }

        .article_title_list {
            margin-top: 18px;
            display: grid;
            grid-template-columns: 80px auto;
        }

        .collect_card {
            display: block;
            padding: 1rem 1.5rem;
        }

        .collect_title {
            font-size: 20px;
            font-weight: 700;
            width: calc(100% - 30px);
        }

        .subtitle {
            color: gray;
            text-overflow: ellipsis;
            margin-bottom: 10px;
        }

        .summary {
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            -webkit-line-clamp: 2;
        }

        .collect_head_bar {
            background-color: #212121;
            height: 280px;
            padding: 30px;
            color: white;
        }

        .collect_section {
            background-color: #f5f5f5;
        }

        .section_inner {
            max-width: 960px;
            margin: 0 auto;
        }

        .right_content,
        .left_content {
            width: 100%;
            padding: 1rem;
        }

        .search_section {
            background-color: #3a3a3a;
            text-align: center;
            line-height: 3.5rem;
            position: sticky;
            top: 50px;
            z-index: 80;
        }

        .tag {
            background-color: #6374EF;
            border-radius: 4px;
            color: white;
            padding: 3px 6px;
            margin: 0 5px 5px 0;
        }

        .article_title {
            text-overflow: ellipsis;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            -webkit-line-clamp: 2;
        }
    </style>
    <style media="screen and (min-width:800px)">
        .collect_section .section_inner {
            display: grid;
            grid-template-columns: auto 360px;
        }
    </style>
    <!--
<style media="screen and (max-width:800px)">
#right_pannal{
	display:none;
}
.when_right_fixed{
	padding-right:0;
}
.index_toolbar{
	position:unset;
}
#pali_pedia{
	font-size: 200%;
	margin-top: auto;
	margin-bottom: auto;
	padding-left: 0.5em;
}
.index_inner{
		display:block;
	}
	.card{
	display: flex;
    justify-content: space-between;
    padding-right: 5em !important;
}
.card_info{
	flex:5;
}
.article_title_link{
	flex:5;
}
#book_list>div{
    width:100% !important;
}

</style>-->
    <?php
    //

    require_once "../config.php";
    require_once "../public/_pdo.php";
    require_once '../media/function.php';
    require_once '../public/function.php';

    ?>
    <div id='course_head_bar' class='collect_head_bar'>
        <div class='section_inner'>
            <h1><?php echo $_local->gui->composition; ?></h1>
            <div style='max-width:30em'><?php echo $_local->gui->composition_intro; ?></div>
            <!--
            <div id="main_tag">
                <span tag="vinaya">sīla</span>
                <span tag="sutta">samadhi</span>
                <span tag="abhidhamma">paññā</span>
                <span tag="mūla">vatthu</span>
            </div>
            <div id="tag_selected" class="" style="padding-bottom:5px;margin:0.5em 0;"></div>-->
        </div>
    </div>

    <div class="search_section">
        <div style='font-size:140%;'>
            <span style="display:inline-block;max-width:20em;"><input class="disable" type="input" placeholder=<?php echo $_local->gui->search . '：' . $_local->gui->title . '&nbsp;OR&nbsp;' . $_local->gui->author; ?> style="background-color:var(--new-tool-input-text-bg); border:solid 1px var(--new-tool-btn-border)" /></span>
            <button class="icon_btn disable">
                <svg class="icon">
                    <use xlink:href="../studio/svg/icon.svg#ic_search"></use>
                </svg>
            </button>
        </div>
    </div>

    <div class="collect_section">
        <div class="section_inner">
            <div class="left_content">
                <div style="display:flex;">
                    <h2 style="margin-right:auto">
                        <?php echo $_local->gui->composition; ?>
                    </h2>
                    <div level="7" class="tag_others" style="padding-bottom:5px; margin-right:5px;">
                        <select>
                            <option><?php echo $_local->gui->all; ?></option>
                            <option><?php echo $_local->gui->ongoing; ?></option>
                            <option><?php echo $_local->gui->completed; ?></option>
                        </select>
                    </div>
                    <div level="8" class="tag_others" style="padding-bottom:5px;">
                        <select>
                            <option><?php echo $_local->gui->popular; ?></option>
                            <option><?php echo $_local->gui->recommendation; ?></option>
                            <option><?php echo $_local->gui->collection; ?></option>
                            <option><?php echo $_local->gui->rates; ?></option>
                            <option><?php echo $_local->gui->updated; ?></option>
                        </select>
                    </div>
                </div>
                <div id="book_list">
                </div>
            </div>
            <div class="right_content">
                <h2><?php echo $_local->gui->hot_topic; ?></h2>
                <div class="disable" style="display:flex; margin:1em 0; flex-wrap:wrap;">
                    <div class="tag">sīla</div>
                    <div class="tag">smādhi</div>
                    <div class="tag">paññā</div>
                    <div class="tag">jātaka</div>
                    <div class="tag">visuddhimagga</div>
                    <div class="tag">ānāpānassati</div>
                </div>
                <h2><?php echo $_local->gui->author; ?></h2>
                <div class="disable" style="margin:1em 0;">
                    <div class="list_with_head noselect">
                        <div class="head"><span class="head_img">Ko</span></div>
                        <div class="channal_list">Visuddhinanda Bhikkhu</div>
                    </div>
                    <div class="list_with_head noselect">
                        <div class="head"><span class="head_img">Ko</span></div>
                        <div class="channal_list">Kosalla Bhikkhu</div>
                    </div>
                    <div class="list_with_head noselect">
                        <div class="head"><span class="head_img">Ko</span></div>
                        <div class="channal_list">Paññābhinanda</div>
                    </div>
                </div>
                <h2><?php echo $_local->gui->language; ?></h2>
            </div>
        </div>

    </div>
    <div id="page_bottom" style="height:10em;">
        <?php echo $_local->gui->loading; ?>
    </div>
    <script>
        $(document).ready(function() {
            collect_load();
        });
    </script>
    <?php
    include "../pcdl/html_foot.php";
    ?>