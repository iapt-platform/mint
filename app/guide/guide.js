function guide_init() {
    $("guide").each(function () {
        if ($(this).offset().left < $(document.body).width() / 2) {
            $(this).append('<div  class="guide_contence" style="left: -5px;"></div>');
            $(".guide_contence:after").css("left", "0");
        }
        else {
            $(this).append('<div  class="guide_contence" style="right: -5px;"></div>');
            $(".guide_contence:after").css("right", "0");
        }
    });

    $("guide").mouseenter(function () {
        if ($(this).children(".guide_contence").first().html().length > 0) {
            return;
        }
        let gid = $(this).attr("gid");
        let guideObj = $(this);
        $.get("../guide/get.php",
            {
                id: gid
            },
            function (data, status) {
                try {
                    let jsonGuide = JSON.parse(data);
                    $("guide[gid='" + jsonGuide.id + "']").find(".guide_contence").html(marked(jsonGuide.data));
                }
                catch (e) {
                    console.error(e);
                }
            });
        /*        if ($(this).offset().left < $(document.body).width() / 2) {
                    $(".guide_contence:after").css("left", "0");
                }
                else {
                    $(".guide_contence:after").css("right", "0");
                }*/

    });

}