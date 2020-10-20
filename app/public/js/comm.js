var VisibleMenu = ""; // 記錄目前顯示的子選單的 ID

// 顯示或隱藏子選單
function switchMenu(theMainMenu, theSubMenu, theEvent) {
  var SubMenu = document.getElementById(theSubMenu);
  if (SubMenu.style.display == "none") {
    // 顯示子選單
    SubMenu.style.display = "block";
    hideMenu(); // 隱藏子選單
    VisibleMenu = theSubMenu;
  } else {
    // 隱藏子選單
    if (theEvent != "MouseOver" || VisibleMenu != theSubMenu) {
      SubMenu.style.display = "none";
      VisibleMenu = "";
    }
  }
}

// 隱藏子選單
function hideMenu() {
  if (VisibleMenu != "") {
    document.getElementById(VisibleMenu).style.display = "none";
  }
  VisibleMenu = "";
}
function com_show_sub_tree(obj) {
  eParent = obj.parentNode;
  var x = eParent.getElementsByTagName("ul");
  if (x[0].style.display == "none") {
    x[0].style.display = "block";
    obj.getElementsByTagName("span")[0].innerHTML = "-";
  } else {
    x[0].style.display = "none";
    obj.getElementsByTagName("span")[0].innerHTML = "+";
  }
}

//check if the next sibling node is an element node
function com_get_nextsibling(n) {
  let x = n.nextSibling;
  if (x != null) {
    while (x.nodeType != 1) {
      x = x.nextSibling;
      if (x == null) {
        return null;
      }
    }
  }
  return x;
}

function com_guid(trim = true, hyphen = false) {
  //guid生成器
  if (trim) {
    if (hyphen) {
      var tmp = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
    } else {
      var tmp = "xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx";
    }
  } else {
    if (hyphen) {
      var tmp = "{xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx}";
    } else {
      var tmp = "{xxxxxxxxxxxxxxxxxxxxxxxxxxxxxxxx}";
    }
  }

  var guid = tmp.replace(/[xy]/g, function (c) {
    var r = (Math.random() * 16) | 0,
      v = c == "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
  return guid.toUpperCase();
}
function com_uuid() {
  //guid生成器
  let tmp = "xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx";
  let uuid = tmp.replace(/[xy]/g, function (c) {
    var r = (Math.random() * 16) | 0,
      v = c == "x" ? r : (r & 0x3) | 0x8;
    return v.toString(16);
  });
  return uuid.toLowerCase();
}
function com_xmlToString(elem) {
  var serialized;
  try {
    serializer = new XMLSerializer();
    serialized = serializer.serializeToString(elem);
  } catch (e) {
    serialized = elem.xml;
  }
  return serialized;
}

function com_getPaliReal(inStr) {
  var paliletter = "abcdefghijklmnoprstuvyāīūṅñṭḍṇḷṃ";
  var output = "";
  inStr = inStr.toLowerCase();
  inStr = inStr.replace(/ṁ/g, "ṃ");
  inStr = inStr.replace(/ŋ/g, "ṃ");
  for (x in inStr) {
    if (paliletter.indexOf(inStr[x]) != -1) {
      output += inStr[x];
    }
  }
  return output;
}

function getCookie(c_name) {
  if (document.cookie.length > 0) {
    c_start = document.cookie.indexOf(c_name + "=");
    if (c_start != -1) {
      c_start = c_start + c_name.length + 1;
      c_end = document.cookie.indexOf(";", c_start);
      if (c_end == -1) c_end = document.cookie.length;
      return unescape(document.cookie.substring(c_start, c_end));
    } else {
      return "";
    }
  } else {
    return "";
  }
}

function setCookie(c_name, value, expiredays) {
  var exdate = new Date();
  exdate.setDate(exdate.getDate() + expiredays);
  document.cookie =
    c_name +
    "=" +
    escape(value) +
    (expiredays == null ? "" : "; expires=" + exdate.toGMTString() + ";path=/");
}

function copy_to_clipboard(strInput) {
  const input = document.createElement("input");
  input.setAttribute("readonly", "readonly");
  input.setAttribute("value", strInput);
  document.body.appendChild(input);
  //	input.setSelectionRange(0, strInput.length);
  //	input.focus();
  input.select();
  if (document.execCommand("copy")) {
    document.execCommand("copy");
    console.log("复制成功");
    ntf_show("“" + strInput + "”" + gLocal.gui.copied_to_clipboard);
  }
  document.body.removeChild(input);
}
