var _nsy_book_dir = "";
var _nsy_book_id = "";

function nissaya_load(nsyid,book, para, begin = 0, end = 0){
	if(nsyid==0){
		nissaya_get_books(book, para);
	}else{
		nissaya_get(nsyid,book, para, begin, end );
	}
}
function render_nissaya_books(list,book,para,begin=0,end=0){
	let books = JSON.parse(list);
	let html ="<ol>";
	for (const iterator of books) {
		html += "<li>";
		html += "<a href='index.php?book="+book+"&par="+para+"&begin="+begin+"&end="+end+"&nsyid=a"+iterator.nsyid+"'>";
		html +=  iterator.nsyid + "-" + iterator.name;
		html += "</a>";
		html += "</li>";
	}
	html += "</ol>";
	return html;
}
function nissaya_get_books(book, para) {
	$.get(
		"../nissaya/get_book_list.php",
		{
			book: book,
			para: para,
		},
		function (data) {
			let result = JSON.parse(data);
			if (result.error == "") {
				if (result.data.length > 0) {
					//找到的书的列表
					$("#contence").html(render_nissaya_books(result.data,book,para));
				}
			}
		}
	);

}
function nissaya_get(nissayabook,book, para, begin = 0, end = 0) {
	if (book == 0 || para == 0) {
		return;
	}
	$.get(
		"../nissaya/get.php",
		{
			book: book,
			para: para,
			begin: begin,
			end: end,
			nsyid:nissayabook,
		},
		function (data) {
			let result = JSON.parse(data);
			if (result.error == "") {
				if (result.data.length > 0) {
					//找到的书的列表
					for (const iterator of result.data) {
					}
					$("#contence").html(render_nissaya_init(result.data[0]));
					insert_new_end(1);
				}
			}
		}
	);
}

function render_on_page(params) {
	//加入前导零补足3位
	let prefix='';
	if(params.page < 10){
		prefix='00';
	}else if(params.page < 100){
		prefix='0';
	}
	let filename = params.dir + "/" + params.book + "/" + prefix + params.page + ".png";
	let html = "";

	html += "<div class='img_box' dir='" + params.dir + "' book='" + params.book + "' page='" + params.page + "'>";
	html += "<div>" + filename + "</div>";
	let filePath = ASSETS_SERVER + "/nissaya/";
	if (params.show) {
		html += "<img class='book_page' src='" + filePath + filename + "' />";
	} else {
		html += "<img class='book_page' data-src='" + filePath + filename + "' />";
	}
	html += "</div>";
	return html;
}
function render_nissaya_init(data) {
	let dir = data.dir;
	return render_on_page({
		dir: dir,
		book: data.nsyid,
		page: data.nsypagenumber,
		show: true,
	});
}

function insert_new_befor(num = 2) {
	let head = $("#contence").children().first();
	let pageid = parseInt(head.attr("page"));
	if (pageid <= 1) {
		return "";
	}
	let html = render_on_page({
		dir: head.attr("dir"),
		book: head.attr("book"),
		page: parseInt(head.attr("page")) - 1,
		show: false,
	});
	$("#contence").prepend(html);
}
function insert_new_end(num = 2) {
	let last = $("#contence").children().last();
	let html = render_on_page({
		dir: last.attr("dir"),
		book: last.attr("book"),
		page: parseInt(last.attr("page")) + 1,
		show: false,
	});
	$("#contence").append(html);
}

function check_end() {
	let last = $(".book_page").last();
	let attr = last.attr("src");
	if (typeof attr == typeof undefined || attr == false || attr.length == 0) {
		return;
	} else {
		insert_new_end();
	}
}
function check_head() {
	let first = $(".book_page").first();
	let attr = first.attr("src");
	if (typeof attr == typeof undefined || attr == false || attr.length == 0) {
		return;
	} else {
		insert_new_befor();
	}
}
window.onload = function () {
	window.onscroll = () => {
		checkImgs();
	};
	function checkImgs(el) {
		const imgs = document.querySelectorAll(".book_page");
		Array.from(imgs).forEach((el) => {
			// 在这里更换不同的方法即可
			if (isInViewPort(el)) loadImg(el);
		});
		check_end();
		check_head();
	}
	function loadImg(el) {
		if (!el.src) {
			const source = el.dataset.src;
			el.src = source;
		}
		// 这里应该是有一个优化的地方，设一个标识符标识已经加载图片的index，当滚动条滚动时就不需要遍历所有的图片，只需要遍历未加载的图片即可。
	}

	function isInViewPort(el) {
		const viewPortHeight =
			window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
		let rect = el.parentNode.getBoundingClientRect();
		if (rect.top > viewPortHeight || rect.bottom < 0) {
			return false;
		} else {
			return true;
		}
	}
};

/*
作者：付出
链接：https://juejin.cn/post/6844903725249609741
来源：掘金
著作权归作者所有。商业转载请联系作者获得授权，非商业转载请注明出处。
*/
/*
function isInViewPortOfOne(el) {
	// viewPortHeight 兼容所有浏览器写法
	const viewPortHeight = window.innerHeight || document.documentElement.clientHeight || document.body.clientHeight;
	const offsetTop = el.offsetTop;
	const scrollTop = document.documentElement.scrollTop;
	const top = offsetTop - scrollTop;
	console.log("top", top);
	// 这里有个+100是为了提前加载+ 100
	return top <= viewPortHeight + 100;
}
*/
