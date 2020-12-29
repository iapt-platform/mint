var main_tag = "";
var list_tag = new Array();
var currTagLevel0 = new Array();
var allTags = new Array();
var arrMyTerm = new Array();

palicanon_load_term();

function palicanon_onload() {
    $("span[tag]").click(function() {
        $(this).siblings().removeClass("select");
        $(this).addClass("select");
        main_tag = $(this).attr("tag");
        list_tag = new Array();
        tag_changed();
        render_tag_list();
    });

    $("#tag_input").keypress(function() {
        tag_render_others();
    });
}

function palicanon_load_term() {
    $.post(
        "../term/get_term_index.php", {
            lang: "zh-hans",
        },
        function(data) {
            let result = JSON.parse(data);
            if (result.status == 0) {
                arrMyTerm = result.data;
            } else {
                alert(result.error);
            }
        }
    );
}

function tag_changed() {
    let strTags = "";
    if (list_tag.length > 0) {
        strTags = main_tag + "," + list_tag.join();
    } else {
        strTags = main_tag;
    }
    console.log(strTags);
    $.get(
        "book_tag.php", {
            tag: strTags,
        },
        function(data, status) {
            let arrBookList = JSON.parse(data);
            let html = "";
            allTags = new Array();
            for (const iterator of arrBookList) {
                let tag0 = "";
                let tags = iterator[0].tag.split("::");
                let currTag = new Array();
                currTag[main_tag] = 1;
                for (const scondTag of list_tag) {
                    currTag[scondTag] = 1;
                }
                for (let tag of tags) {
                    if (tag.slice(0, 1) == ":") {
                        tag = tag.slice(1);
                    }
                    if (tag.slice(-1) == ":") {
                        tag = tag.slice(0, -1);
                    }
                    if (currTagLevel0.hasOwnProperty(tag)) {
                        tag0 = tag;
                    }
                    if (!currTag.hasOwnProperty(tag)) {
                        if (allTags.hasOwnProperty(tag)) {
                            allTags[tag] += 1;
                        } else {
                            allTags[tag] = 1;
                        }
                    }
                }

                //html += "<div style='width:100%;'>";
                html += "<div class='sutta_row' >";
                html += "<div class='sutta_box'>" + tag0 + "</div>";
                html +=
                    "<div class='sutta_box'><a href='../reader/?view=chapter&book=" +
                    iterator[0].book +
                    "&para=" +
                    iterator[0].para +
                    "' target = '_blank'>" +
                    iterator[0].title +
                    "</a></div>";
                html += "<div class='sutta_box'>book:" + iterator[0].book + " para:" + iterator[0].para + "</div>";
                html += "<div class='sutta_tag'>tag=" + iterator[0].tag + "</div>";
                html += "</div>";
                //html += "</div>";
            }

            let newTags = new Array();
            for (const oneTag in allTags) {
                if (allTags[oneTag] < arrBookList.length) {
                    newTags[oneTag] = allTags[oneTag];
                }
            }
            allTags = newTags;
            allTags.sort(sortNumber);
            tag_render_others();
            $("#book_list").html(html);
        }
    );
}

function tag_render_others() {
    let strOthersTag = "";
    currTagLevel0 = new Array();
    $(".tag_others").html("");

    document.getElementById('main_tag').style.margin = 1 + "em";
    document.getElementById('main_tag').style.fontSize = 100 + "%";

    for (const key in allTags) {
        if (allTags.hasOwnProperty(key)) {
            if ($("#tag_input").val().length > 0) {
                if (key.indexOf($("#tag_input").val()) >= 0) {
                    strOthersTag = "<button class=\"canon-tag\" onclick =\"tag_click('" + key + "')\" >" + key + "</button>";
                }
            } else {
                let termKey = term_lookup_my(key, "", getCookie("userid"), "zh-cn");
                let keyname = key;
                if (termKey) {
                    keyname = termKey.meaning;
                }
                strOthersTag = "<button class=\"canon-tag\" onclick =\"tag_click('" + key + "')\" >" + keyname + "</button>";
            }
            let thisLevel = 100;
            if (tag_level.hasOwnProperty(key)) {
                thisLevel = tag_level[key].level;
                if (tag_level[key].level == 0) {
                    currTagLevel0[key] = 1;
                }
            }
            $(".tag_others[level='" + thisLevel + "']").html(
                $(".tag_others[level='" + thisLevel + "']").html() + strOthersTag
            );
        }
    }
}

function tag_click(tag) {
    list_tag.push(tag);
    render_tag_list();
    tag_changed();
}

function render_tag_list() {
    let strListTag = gLocal.gui.selected + "：";
    for (const iterator of list_tag) {
        strListTag += "<tag><span class=\"textt\">" + iterator + "</span>";
        strListTag += "<span class=\"tag-delete\" onclick =\"tag_remove('" + iterator + "')\">✕</span></tag>";
    }
    strListTag +=
        "<div style='display:inline-block;width:20em;'><input id='tag_input' type='input' placeholder='tag' size='20'  /></div>";
    $("#tag_selected").html(strListTag);
}

function tag_remove(tag) {
    for (let i = 0; i < list_tag.length; i++) {
        if (list_tag[i] == tag) {
            list_tag.splice(i, 1);
        }
    }
    render_tag_list();
    tag_changed();
}

function sortNumber(a, b) {
    return b - a;
}