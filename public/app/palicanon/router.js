//构造函数
function Router() {
    this.routes = {};
    this.currentUrl = '';
}
Router.prototype.route = function(path, callback) {
    this.routes[path] = callback || function(){};//给不同的hash设置不同的回调函数
};
Router.prototype.refresh = function() {
    console.log(location.search.slice(1));//获取到相应的hash值
    this.currentUrl = location.search.slice(1) || '/';//如果存在hash值则获取到，否则设置hash值为/
    // console.log(this.currentUrl);
    let params = new URLSearchParams(document.location.search);
    let view = params.get("view");
    if(this.currentUrl&&this.currentUrl!='/'){
        this.routes[view]();//根据当前的hash值来调用相对应的回调函数
    }
 
};
Router.prototype.init = function() {
    window.addEventListener('load', this.refresh.bind(this), false);
    //window.addEventListener('hashchange', this.refresh.bind(this), false);
    window.onpopstate = function(event) {
        console.log("location: " + document.location + ", state: " + JSON.stringify(event.state));
        _view = event.state.view;
        _tags = event.state.tag;
        _channel = event.state.channel;
        list_tag = _tags.split(',');
        switch (_view) {
            case "community":
                community_onload();
                break;
            case "category":
                palicanon_onload();
                palicanonGetChapter();
                break;
            default:
                break;
        }
        
    };
}
//给window对象挂载属性
window.Router = new Router();
window.Router.init();