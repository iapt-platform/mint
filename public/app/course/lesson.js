var renderer = new marked.Renderer();
renderer.code = function(code, language) {
    if (language == "mermaid") return '<pre class="mermaid">' + code + "</pre>";
    else return "<pre><code>" + code + "</code></pre>";
};

function lesson_show(id) {
    $.get(
        "../course/lesson_get.php", {
            id: id,
        },
        function(data, status) {
            let arrLesson = JSON.parse(data);
            let html = "";
            for (const lesson of arrLesson) {
                html += '<div class="card" style="display:flex;margin:1em;padding:10px;">';

                html += '<div style="flex:7;">';
                html += '<div class="pd-10">';
                html +=
                    '<div class="title" style="padding-bottom:5px;font-size:200%;font-weight:600;">' +
                    lesson["title"] +
                    "</div>";
                html += '<div style="">';
                let summary = "";
                try {
                    summary = marked(lesson["summary"], { renderer: renderer });
                } catch {}
                html += '<div class="summary"  style="padding-bottom:5px;">' + summary + "</div>";
                let live = "";
                try {
                    live = marked(lesson["live"], { renderer: renderer });
                } catch {}
                html += '<div class="summary"  style="padding-bottom:5px;">' + live + "</div>";
                let replay = "";
                try {
                    replay = marked(lesson["replay"], { renderer: renderer });
                } catch {}
                html += '<div class="summary"  style="padding-bottom:5px;">' + replay + "</div>";
                let attachment = "";
                try {
                    attachment = note_init(lesson["attachment"]);
                } catch {}
                html += '<div class="summary"  style="padding-bottom:5px;">' + attachment + "</div>";
                html += "</div>";
                html += "</div>";
                html += "</div>";

                html += '<div style="flex:3;max-width:15em;">';
                let d = new Date();
                d.setTime(lesson["date"]);
                let strData = d.toLocaleDateString();
                let strTime = d.toLocaleTimeString();
                html += "<div >" + gLocal.gui.date + "：" + strData + "</div>";
                html += "<div >" + gLocal.gui.time + "：" + strTime + "</div>";
                let dt = lesson["duration"] / 60;
                let sdt = "";
                if (dt > 59) {
                    sdt += Math.floor(dt / 60) + gLocal.gui.h;
                }
                let m = dt % 60;
                if (m > 0) {
                    sdt += (dt % 60) + gLocal.gui.mins;
                }
                html += "<div >" + gLocal.gui.duration + "：" + sdt + "</div>";
                let now = new Date();

                let lesson_time = "";
                if (now < lesson["date"]) {
                    lesson_time = gLocal.gui.not_started;
                } else if (now > lesson["date"] && now < lesson["date"] + dt * 1000) {
                    lesson_time = gLocal.gui.in_progress;
                } else {
                    lesson_time = gLocal.gui.already_over;
                }
                html += '<div ><span class="lesson_status">' + lesson_time + "</span></div>";

                html += "</div>";

                html += "</div>";
            }
            $("#lesson_list").html(html);
			note_refresh_new();
            mermaid.initialize({startOnLoad:true});
        }
    );
}

function lesson_load(lesson_id) {
    $.get(
        "../course/lesson_get.php", {
            id: lesson_id,
        },
        function(data, status) {
            let html = "";
            let lesson_info = JSON.parse(data);
            if (lesson_info) {
                //head
                html += "<div id='course_info_head' class='course_info_block'>";

                html += "<div id='course_info_head_title'>";
                html += "<div id='course_title'>" + lesson_info.title + "</div>";
                html += "<div id='course_subtitle'>" + lesson_info.subtitle + "</div>";
                html += "</div>";
                html += "</div>";
                //end of head

                let d = new Date();
                d.setTime(lesson_info.date);
                let strData = d.toLocaleDateString();
                let strTime = d.toLocaleTimeString();
                let dt = lesson_info["duration"] / 60;
                let strDuration = "";
                if (dt > 59) {
                    strDuration += Math.floor(dt / 60) + gLocal.gui.h;
                }
                let m = dt % 60;
                if (m > 0) {
                    strDuration += (dt % 60) + gLocal.gui.mins;
                }

                html += "<div id='lesson_info_head_2' class='course_info_block'>";
                html += "<div class='info_item'>" + "<span>"+gLocal.gui.time_arrange+"：</span><span>" + strData + " " + strTime + "</span></div>";
                html +=
                    "<div class='info_item'>" + "<span>" + gLocal.gui.duration + "：</span><span>" + strDuration + "</span></div>";
                html += "<div class='info_item'>" + "<span>" + gLocal.gui.speaker + "：<span>";
                html +=
                    "<a href='../uhome/course.php?userid=" +
                    lesson_info.teacher +
                    "'>" +
                    lesson_info.teacher_info.nickname +
                    "</a>";
                html += "</span>";
                //html += "<span>地区:缅甸</span>";
                html += "</div>";
                html += "</div>";
                // end of course_info_head_2

                //live
                if (lesson_info.live && lesson_info.live.length > 0) {
                    html += "<div id='course_info_live' class='course_info_block'>";
                    html += "<h2>" + gLocal.gui.notice_live + "</h2>";
                    try {
                        html += marked(lesson_info.live);
                    } catch (e) {
                        html += e.message;
                    }
                    html += "</div>";
                }
                //end of live

                //replay
                if (lesson_info.replay && lesson_info.replay.length > 0) {
                    html += "<div id='course_info_replay' class='course_info_block'>";
                    html += "<h2>" + gLocal.gui.record_replay + "</h2>";
                    try {
                        html += marked(lesson_info.replay);
                    } catch (e) {
                        html += e.message;
                    }
                    html += "</div>";
                }
                //end of replay

                //attachment
                if (lesson_info.attachment && lesson_info.attachment.length > 0) {
                    html += "<div id='course_info_attachment' class='course_info_block'>";
                    html += "<h2>" + gLocal.gui.attachment + "</h2>";
                    try {
                        html += "<div class='course_info_content'>";
                        html += note_init(lesson_info.attachment);
                        html += "</div>";
                    } catch (e) {
                        html += e.message;
                    }
                    html += "</div>";
                }
                //end of attachment

                //content
                if (lesson_info.summary && lesson_info.summary.length > 0) {
                    html += "<div id='course_info_content' class='course_info_block'>";
                    html += "<h2>" + gLocal.gui.detaile + "</h2>";
                    try {
                        html += marked(lesson_info.summary);
                    } catch (e) {
                        html += e.message;
                    }
                    html += "</div>";
                }
                //end of attachment
                $("#lesson_info").html(html);
                $("#page_title").text(lesson_info.title);
                note_refresh_new();
                render_course_info(lesson_info.course_id);
                render_lesson_list(lesson_info.course_id, lesson_info.id);
            }
        }
    );
}

function render_course_info(course_id) {
    $.get(
        "../course/course_get.php", {
            id: course_id,
        },
        function(data, status) {
            let html = "";
            let course_info = JSON.parse(data);
            if (course_info) {
                let html = "";
                html += "<div id='parent_title'>";
                html += "<a href='../course/course.php?id=" + course_info.id + "'>";
                html += course_info.title;
                html += "</a>";
                html += "</div>";
                html += "<div id='course_button'>";
                html += "<button>"+gLocal.gui.watch+"</button>";
                html += "<button>"+gLocal.gui.sign_up+"</button>";
                html += "</div>";
                $("#course_info").html(html);
            }
        }
    );
}

function render_lesson_list(course_id, currLesson) {
    $.get(
        "../course/lesson_list.php", {
            id: course_id,
        },
        function(data, status) {
            let arrLesson = JSON.parse(data);
            let html = "";
            for (const lesson of arrLesson) {
                let currlessonStyle = "";
                if (lesson.id == currLesson) {
                    currlessonStyle = " curr_lesson";
                } else {
                    currlessonStyle = " not_curr_lesson";
                }
                //计算课程是否已经开始
                let now = new Date();
                let class_lesson_time = "";
                let dt = lesson["duration"] / 60;
                if (now < lesson["date"]) {
                    class_lesson_time = "not_started";
                } else if (now > lesson["date"] && now < lesson["date"] + dt * 1000) {
                    class_lesson_time = "in_progress";
                } else {
                    class_lesson_time = "already_over";
                }

                html += '<div class="lesson_card ' + currlessonStyle + '" >';
                let d = new Date();
                d.setTime(lesson["date"]);
                let strData = d.toLocaleDateString();
                let strTime = d.toLocaleTimeString();
                html += '<div class="pd-10">';
                html += "<div class='datatime " + class_lesson_time + "'>" + strData + " " + strTime + "</div>";
                html +=
                    '<div class="title" ><a href="../course/lesson.php?id=' +
                    lesson["id"] +
                    '">' +
                    lesson["title"] +
                    "</a></div>";

                html += "</div>";

                html += "</div>";
            }
            $("#lesson_list").html(html);
        }
    );
}

function lesson_get_timeline_settime(start, div) {
    let lessonTime;
    if (start) {
        lessonTime = new Date(parseInt(start));
    } else {
        lessonTime = new Date();
    }
    let month = lessonTime.getMonth() + 1;
    month = month > 9 ? month : "0" + month;
    let d = lessonTime.getDate();
    d = d > 9 ? d : "0" + d;
    let data = lessonTime.getFullYear() + "-" + month + "-" + d;
    let strData = "<input type='date'  id='" + div + "_date' value='" + data + "'/>";

    let H = lessonTime.getHours();
    H = H > 9 ? H : "0" + H;
    let M = lessonTime.getMinutes();
    M = M > 9 ? M : "0" + M;
    let strTime = "<input type='time'  id='" + div + "_time' value='" + H + ":" + M + "'/>";

    $("#form_" + div).html(strData + strTime);
}

function lesson_get_timeline_submit() {
    let start_date = new Date();
    let start_data = $("#start_date").val().split("-");
    let start_time = $("#start_time").val().split(":");

    start_date.setFullYear(start_data[0], parseInt(start_data[1]) - 1, start_data[2]);
    start_date.setHours(start_time[0], start_time[1]);

    let end_date = new Date();
    let end_data = $("#end_date").val().split("-");
    let end_time = $("#end_time").val().split(":");

    end_date.setFullYear(end_data[0], parseInt(end_data[1]) - 1, end_data[2]);
    end_date.setHours(end_time[0], end_time[1], 0, 0);

    location.assign("../course/lesson_get_timeline.php?start=" + start_date.getTime() + "&end=" + end_date.getTime());
}

function lesson_get_timeline_json(start, end) {
    $.get(
        "../ucenter/active_log_get.php", {
            start: start,
            end: end,
        },
        function(data) {
            $("#timeline_json").val(data);
            let json = JSON.parse(data);
            let html = "<table>";
            for (const row of json) {
                html += "<tr>";
                let start_date = new Date(parseInt(row.time));
                html += "<td>" + start_date.getHours() + ":" + start_date.getMinutes() + "</td>";

                html += "<td>" + gLocal.LogType[row.active] + "</td>";
                html += "<td>";
                switch (row.active) {
                    case "30":
                        html += "<a href='../dict/?word=" + row.content + "' target='_blank'>" + row.content + "</a>";
                        break;
                    case "11":
                        break;
                    case "20":
                        break;
                    default:
                        html += row.content;
                        break;
                }
                html += "</td>";
                html += "</tr>";
            }
            html += "</table>";
            $("#timeline_table").html(html);
        }
    );
}