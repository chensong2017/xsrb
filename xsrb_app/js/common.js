	URL="http://114.55.183.75:801/index.php/Home/";
     function getRequestParam(name) {
		var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
		var r = window.location.search.substr(1).match(reg);
		return r != null?decodeURIComponent(r[2]):null;
		} 