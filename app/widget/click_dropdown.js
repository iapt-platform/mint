function click_dropdown_init() {
  $(".click_dropdown_button").click(function () {
    $(this).siblings(".click_dropdown_content").first().show();
  });

  $(".click_dropdown_cancel").click(function () {
    $(this).parent().parent().hide();
  });
}
