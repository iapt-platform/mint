<?PHP

include "../pcdl/html_head.php";
?>

<body>
    <script language="javascript" src="../course/course.js"></script>

    <?php
    require_once("../pcdl/head_bar.php");
    ?>

<link type="text/css" rel="stylesheet" href="./style.css" />
    <style>
        .disable {
            cursor: not-allowed;
            opacity: 0.5;
        }

        #main_video_win iframe {
            width: 100%;
            height: 100%;
        }

        #course_frame {
            display: flex;
        }

        #course_content {
            margin: 0;
            padding: 0;
        }

        #lesson_list {}

        #course_info_head_1 {
            display: grid;
            grid-template-columns: 200px 1fr;
            grid-gap: 20px;
        }

        #course_info_head_face {
            /*width: 200px;
            height: 200px;*/
        }

        #course_info_head_face img {
            width: 200px;
            height: 200px;
            border-radius: 12px;
            object-fit: cover;
        }

        .section_inner {
            max-width: 960px;
            margin: 0 auto;
        }

        #course_info_head_title {
            display: flex;
            flex-direction: column;
            word-wrap: break-word;
        }

        #course_title {
            font-size: 22px;
            font-weight: 700;
        }

        #course_subtitle {
            font-size: 13px;
            padding: 10px 0;
            word-wrap: break-word;
        }

        #course_button {
            margin-top: auto;
        }

        #lesson_list_shell {
            padding: 1rem;
            background-color: #f5f5f5;
        }

        .course_info_block {
            padding: 1rem 0;
        }

        #course_info_summary {
            border-top: 1px solid var(--border-line-color);
            border-bottom: 1px solid var(--border-line-color);
        }

        .course_info_block h2 {
            font-size: 16px;
        }

        #lesson_list {
            column-count: 2;
            position: relative;
        }

        #lesson_list .lesson_card {
            padding: 10px 10px 10px 0;
            -webkit-column-break-inside: avoid;
            page-break-inside: avoid;
            break-inside: avoid;
        }

        .datatime {
            display: inline;
        }

        .not_started {
            background-color: orangered;
            color: var(--bg-color);
        }

        .in_progress,
        .already_over {
            color: var(--btn-hover-bg-color);
        }

        .lesson_card .title {
            margin-top: 5px;
            font-size: 17px;
            font-weight: 600;
            display: -webkit-box;
            -webkit-box-orient: vertical;
            overflow: hidden;
            text-overflow: ellipsis;
            width: 100%;
            -webkit-line-clamp: 2;
        }

        #course_info_head_2 {
            margin: 1rem 0;
        }

        @media screen and (max-width:800px) {
            #lesson_list {
                column-count: 1;
            }

            #course_info_head_1 {
                grid-template-columns: 100px 1fr;
                grid-gap: 10px;
            }

            #course_info_head_face img {
                width: 100px;
                height: 100px;
                border-radius: 6px;
            }
        }
    </style>



    <div id='course_content'>
        <div id='course_info_shell' style='padding:1rem;'>
            <div id='course_info' class="section_inner"></div>
        </div>
        <div id='lesson_list_shell'>
            <div id='lesson_list' class="section_inner"></div>
        </div>
    </div>

    <script>
        course_load("<?php echo $_GET["id"]; ?>");
    </script>
    <?php
    include "../pcdl/html_foot.php";
    ?>