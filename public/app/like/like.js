function like_init() {
  $("like").each(function () {
    if ($(this).html().length == 0) {
      $(this).append(
        "<span class='like_icon'></span></span><span class='like_count'>0</span>"
      );
    }
  });
}
