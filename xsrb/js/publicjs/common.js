	/**
	 * 正式环境*/
	// new_ip = window.location.host;
	new_ip = "xsrb.wsy.me:801";
	// fdmUrl = "http://" + new_ip + "/index.php/NewSPZ/";
	fdmUrl="http://"+new_ip+"/saleStockSystem/index.php/admin/";
	URL = "http://" + new_ip + "/index.php/home/";
	newUrl = "http://chaogept.wsy.me/";//进销存环境勿删  	
    URL_Z_excel="http://xsrb.wsy.me/index.php/Home";

    fdmUrl1="http://chaogept.wsy.me/saleStockSystem/index.php/admin/";
    URL1 = "http://chaogept.wsy.me/saleStockSystem/index.php/admin/"; //陈松的url

  	
	/*
	 本地测试
	 * */
//	----------------------------------------------------------------------------------朱发明
//	URL="http://192.111.111.143/xsrb/code/index.php/home/";//朱发明的url
//	URL="http://192.111.111.191/xsrb_master/index.php/home/";
//	----------------------------------------------------------------------------------陈松
// 	URL = "http://192.111.111.149/xsrb_master/index.php/home/"; //陈松的url
//	URL_C="http://"+"192.111.111.140/xsrb_local_test/index.php/Home/";//陈松的url
//	fdmUrl='http://192.111.111.149/xsrb_master/index.php/NewSPZ/';
//	----------------------------------------------------------------------------------税国红
   	
   	
// 	URL = "http://192.111.111.199/xsrb/code/index.php/home/";
//	fdmUrl='http://192.111.111.143/xsrb/code/index.php/NewSPZ/';
//	----------------------------------------------------------------------------------进销存
//	newUrl = "http://192.111.111.143/"  
//	URL="http://192.111.111.123/xsrb/code/index.php/home/";//朱发明的url
//	URL="http://192.111.111.191/xsrb_master/index.php/home/";
//	----------------------------------------------------------------------------------陈松
// 	URL = "http://192.111.111.109/xsrb_master/index.php/home/"; //陈松的url
//	URL_C="http://"+"192.111.111.109/xsrb_local_test/index.php/Home/";//陈松的url
//	----------------------------------------------------------------------------------进销存
//	newUrl = "http://192.111.111.149/"  
	  
//	getUserInfo="http://192.111.111.191/xsrb_master/index.php/home/Member/getUserInfo/";
	
	/*
	 本地正式共用地址
	 * */
	 URL_C = URL;
	 URL2 = URL;
	 URL_Z = URL;
	 URL_Z_excel = URL;
	 
	 
	 
	/*
	 本地正式共用全局
	 * */ 
	 var fori;
	 var forj;
	 var table_flag = 1;
	 var flag_down = 0; //当且仅当表格元素发生mousedown事件之后（flag_down）才能执行mousemove的相关功能，从而实现拖拽选中表格元素的功能
	 var testVersion = localStorage.testVersion;
	  $(function() {
	 		if($("#alertMsgBoxUp").length > 0) {
	 			$("#alertMsgBoxUp").remove();
	 		}
	 		$(".tabsPageHeader li").click(function() {
	 			if(($(this).attr("tabid") == "w_table1")||($(this).attr("tabid") == "w_table4")) {
	 				var alert_str = '<div id="alertMsgBoxUp" class="alert">';
	 				alert_str += '<div class="alertContent">';
	 				alert_str += '<div class="info">';
	 				alert_str += '<div class="alertInner">';
	 				alert_str += '<h1>提示</h1>';
	 				alert_str += '<div class="msg">请注意 ---齐河与成都--- 防盗门库存表的区别来进行填写!</div>';
	 				alert_str += '</div>';
	 				alert_str += '<div class="toolBar"><ul>';
	 				alert_str += '<li><a class="button" id="mine_close_btn" rel="" onclick="alertClose()" href="javascript:"><span>确定</span></a></li>';
	 				alert_str += '</ul></div></div></div>';
	 				alert_str += '<div class="alertFooter"><div class="alertFooter_r"><div class="alertFooter_c"></div></div></div>';
	 				$("body").append(alert_str);
	 				$("#alertMsgBoxUp").css("top", "-500px");
	 				$("#alertMsgBoxUp").animate({
	 					"top": "0"
	 				}, 900);
	 			}
	 		})
	 	})
	 	//查询url参数
	 function getRequestParam(name) {
	 	var reg = new RegExp("(^|&)" + name + "=([^&]*)(&|$)", "i");
	 	var r = window.location.search.substr(1).match(reg);
	 	return r != null ? decodeURIComponent(r[2]) : null;
	 }

	 function find_word(str, substr) {
	 	if(/^\d+$/.test(str)) //纯数字返回假
	 		return false;
	 	else {
	 		if(str.indexOf(substr) >= 0) return true;
	 		else return false;
	 	}
	 }

	 // 测试版  片区  --by
	 function authorize_export() {
	 	var qt1 = getRequestParam("qt1");
		 console.log("===qt1="+qt1);
	 	if(qt1 == "0") { //片区
			$("#limit_xsrbqc").remove();//销售日报期初
			$("#limit_xsrblr").remove();//销售日报录入
			$("#limit_fdmspqc").remove();//防盗门商品期初
			$("#limit_fdmpd").remove();//防盗门商品盘点
			$("#limit_fdmszmx").remove();//防盗门收支明细
			$("#limit_fdmxsmx").remove();//防盗门销售明细
			$("#limit_smcpqc").remove();//数码产品期初
			$("#limit_smcpkc").remove();//数码产品库存表
	 	}
	 	if(qt1 == "1" || qt1 == "0") { //片区
			$("#limit_bmxx").remove();//销售日报部门信息
	 	}
		if((qt1 != 0) && (qt1 != 1)) { //经营部不能看汇总统计报表
			 if($("#summary_tree").length > 0) {
				 $("#summary_tree").remove();
			 }
		}
	 }

	function getEvent(){
	    if(window.event)    {return window.event;}
	    func=getEvent.caller;
	    while(func!=null){
	        var arg0=func.arguments[0];
	        if(arg0){
	            if((arg0.constructor==Event || arg0.constructor ==MouseEvent
	                || arg0.constructor==KeyboardEvent)
	                ||(typeof(arg0)=="object" && arg0.preventDefault
	                && arg0.stopPropagation)){
	                return arg0;
	            }
	        }
	        func=func.caller;
	    }
	    return null;
	}
	 function stopEvent() {
	 	var e=getEvent();
	 	if(e.preventDefault) {
	 		e.stopPropagation();
	 	} else if(window.event) {
	 		window.event.cancelBubble = true;
	 	}
	 }
	function alertClose(){
		$("#alertMsgBoxUp").each(function(){
			if($(this).css("top")=="-500px"){
				$(this).remove();
			}
			else{
			$("#alertMsgBoxUp").animate({
	 					"top": "-500px"
	 				}, 900);
        			$(this).remove();
			}

		})

	}
	 function GetYesterday(flag) {
	 	var today = new Date();
	 	var yesterday_milliseconds = today.getTime() - 1000 * 60 * 60 * 24;

	 	var yesterday = new Date();
	 	yesterday.setTime(yesterday_milliseconds);

	 	var strYear = yesterday.getFullYear();

	 	var strDay = yesterday.getDate();
	 	var strMonth = yesterday.getMonth() + 1;

	 	if(strMonth < 10) {
	 		strMonth = "0" + strMonth;
	 	}
	 	if(strDay < 10) {
	 		strDay = "0" + strDay;
	 	}
	 	strYesterday = String(strYear) +String(strMonth) + String(strDay);
	 	return strYesterday;
	 }
	  function GetYesterday2(flag) {
	 	var today = new Date();
	 	var yesterday_milliseconds = today.getTime() - 1000 * 60 * 60 * 24;

	 	var yesterday = new Date();
	 	yesterday.setTime(yesterday_milliseconds);

	 	var strYear = yesterday.getFullYear();

	 	var strDay = yesterday.getDate();
	 	var strMonth = yesterday.getMonth() + 1;

	 	if(strMonth < 10) {
	 		strMonth = "0" + strMonth;
	 	}
	 	if(strDay < 10) {
	 		strDay = "0" + strDay;
	 	}
	 	strYesterday = String(strYear) +"-"+String(strMonth) + "-"+String(strDay);
	 	return strYesterday;
	 }
	 function setDateSearch1(identifier, flag) {
	 	if(flag) {
	 		if($("#sdate" + identifier).val() == "") {
	 			$("#sdate" + identifier).val(GetYesterday2());
	 			$("#edate" + identifier).val(GetYesterday2());
	 		}
	 	} else {
	 		if($("#date" + identifier).val() == "") {
	 			$("#date" + identifier).val(GetYesterday2());
	 		}
	 	}
	 }
	 function setDateSearch(identifier, flag) {
	 	if(flag) {
	 		if($("#sdate" + identifier).val() == "") {
	 			$("#sdate" + identifier).val(getToday2());
	 			$("#edate" + identifier).val(getToday2());
	 		}
	 	} else {
	 		if($("#date" + identifier).val() == "") {
	 			$("#date" + identifier).val(getToday2());
	 		}
	 	}
	 }

	 function getToday() {
	 	var today = new Date();
	 	var now_date = today.getFullYear();
	 	var month_now = today.getMonth() + 1;
	 	if(month_now < 10) month_now = "0" + month_now;
	 	var day_now = today.getDate();
	 	if(day_now < 10) day_now = "0" + day_now;
	 	now_date = String(now_date) + String(month_now) + String(day_now);
	 	return now_date;
	 }

	 function getToday2() {
	 	var today = new Date();
	 	var now_date = today.getFullYear();
	 	var month_now = today.getMonth() + 1;
	 	if(month_now < 10) month_now = "0" + month_now;
	 	var day_now = today.getDate();
	 	if(day_now < 10) day_now = "0" + day_now;
	 	now_date = String(now_date) + "-" + String(month_now) + "-" + String(day_now);
	 	return now_date;
	 }

	 function checkFloat(obj) {
		 var td_class=obj.parent().attr('class');
		 var arr_class=td_class.split(" ");
		 var rowcode = parseInt(obj.parent().parent().attr("name"));
		 var colcode = parseInt(obj.parent().attr("name"));
		 //如果是门或整机销售成本
		 if($.inArray("door",arr_class)>0){
			 //获取同列门配的值
			 var bro=$("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode+1 )+ "]").children("td[name=" + colcode + "]").children("input").val();
			 var sum=parseFloat(obj.val())+parseFloat(bro);
			 sum=sum.toFixed(2);
			 //处理销售成本的显示和json
			 $("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode-1 )+ "]").children("td[name=" + colcode + "]").html(sum);
			 data_inputds.data[rowcode-1].tr[colcode].value=sum;
		 }
		 //如果是门配或配件销售成本
		 if($.inArray("parts",arr_class)>0){
			 //获取同列正门
			 var bro=$("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode-1) + "]").children("td[name=" + colcode + "]").children("input").val();
			 var sum=parseFloat(obj.val())+parseFloat(bro);
			 sum=sum.toFixed(2);
			 $("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode-2 )+ "]").children("td[name=" + colcode + "]").html(sum);
			 data_inputds.data[rowcode-2].tr[colcode].value=sum;
		 }
	 	//		var s=obj.val();
	 	//		s=s.replace(/[^\-\d\.]/g,"");
	 	//		s=s.replace(/^\./g,"");
	 	//		s=s.replace(/\.{1,}/g,".");
	 	//		obj.val(s);
	 }

	 function resize_table() {
	 	var tablewidth = parseInt($("#container").css("width"));
	 	if(table_flag) {
	 		if(tablewidth * 0.98 > 1000)
	 			$("table").css({
	 				"width": tablewidth * 0.98,
	 				"overflow": "auto"
	 			});
	 		else
	 			$("table").css({
	 				"min-width": "1000"
	 			});
	 	}
	 }

	 function tablesFixed(visWidth, visHeight, data, identifier) {
	 	//visWidth表格的宽,identifier各标签id的后缀名（用于标志表格）
	 	if(identifier == undefined) identifier = "";
	 	$("#fixColumn" + identifier).remove();
	 	$("#fixTitle" + identifier).remove();
	 	$("#fixDB" + identifier).remove();
	 	$("#pageContainer" + identifier).css({
	 		"min-width": "810px",
	 		"overflow": "auto",
	 		"position": "relative",
	 		"z-index": "10",
	 		"color": "#000!important"

	 	});
	 	//		$(".pageContent"+identifier).css("overflow-y","hidden");
	 	$("#containerLeft" + identifier).css({
	 		"position": "relative"
	 	});
	 	$("#pageContainer" + identifier).offset($(".pageContent" + identifier).offset());
	 	$("#pageContainer" + identifier).css({
	 		"width": visWidth + "px",
	 		"height": visHeight + "px",
	 		"min-width": "1050px"
	 	});
	 	$("#containeRight" + identifier).css("height", function() {
	 		return $("#tableRight" + identifier + "0").css("height");
	 	});

	 	var tableNum = $("#containeRight" + identifier + " table:last").index() + 1; //数据中部门数就，也就是右边的表格数
	 	var leftTableoffs = new Object();
	 	leftTableoffs.top = $("#pageContainer" + identifier).offset().top;
	 	leftTableoffs.left = $("#pageContainer" + identifier).offset().left;
	 	$("#containerLeft" + identifier).offset(leftTableoffs);
	 	var rightTableoffs = new Object();
	 	rightTableoffs.top = $(".pageContent" + identifier).offset().top;
	 	rightTableoffs.left = $("#pageContainer" + identifier).offset().left + $("#tableLeft" + identifier).outerWidth(true);
	 	$("#containeRight" + identifier).offset(rightTableoffs);
	 	rightTableoffs.left = $("#containeRight" + identifier).offset().left;
	 	for(var i = 0; i < tableNum; i++) {
	 		$("#tableRight" + identifier + i).offset(rightTableoffs);
	 		rightTableoffs.left = rightTableoffs.left + $("#tableRight" + identifier + i).outerWidth(true);
	 	}
	 	/*生成左列固定块*/
	 	$("#pageContainer" + identifier).after("<div id=\"" + "fixColumn" + identifier + "\"></div>");
	 	$("#fixColumn" + identifier).append($("#tableLeft" + identifier).clone(true));
	 	$("#fixColumn" + identifier).css({
	 		"width": $("#tableLeft" + identifier).width() + 1 + "px",
	 		"height": parseInt(visHeight) - 17 + "px",
	 		"overflow": "hidden",
	 		"position": "relative",
	 		"z-index": "50"
	 	});
	 	//增加一个隐藏框，拉长左框，使其与右边保持一致
	 	$("#fixColumn" + identifier).append("<div id=\"hiddenDiv" + identifier + "\">&nbsp;&nbsp;&nbsp;&nbsp;</div>")
	 	$("#hiddenDiv" + identifier).css({
	 		"width": $("#fixColumn" + identifier).css("width"),
	 		"height": $("#containeRight" + identifier).width() - $("#fixColumn" + identifier).width() + "px"
	 	});

	 	$("#fixColumn" + identifier).offset($("#pageContainer" + identifier).offset());
	 	/*生成左列固定块结束*/
	 	$("#fixColumn" + identifier).after("<div id=\"" + "fixTitle" + identifier + "\"  class=\"containeRight\"></div>");
	 	for(var i = 0; i < tableNum; i++) {
	 		$("#fixTitle" + identifier).append($("#tableRight" + identifier + i).clone(true));
	 	}
	 	$("#fixTitle" + identifier).offset($("#containeRight" + identifier).offset());
	 	$("#fixTitle" + identifier).css({
	 		"width": $("#pageContainer" + identifier).outerWidth() - $("#tableLeft" + identifier).outerWidth() - 17 + "px",
	 		"height": $("#tableRight" + identifier + "0 thead").outerHeight() + "px！important",
	 		"overflow": "hidden",
	 		"position": "relative",
	 		"z-index": "50",
	 		"min-width": "830px"
	 	});

	 	//控制滚动
	 	$("#pageContainer" + identifier).scroll(function() {
	 			$("#fixColumn" + identifier).scrollTop($("#pageContainer" + identifier).scrollTop());
	 			$("#fixTitle" + identifier).scrollLeft($("#pageContainer" + identifier).scrollLeft());
	 		})
	 		//     $("#fixTitle"+identifier).append("<div id=\"blankDiv"+identifier+"\">&nbsp;&nbsp;&nbsp;&nbsp;&nbsp;</div>");
	 		//     $("#blankDiv"+identifier).css({
	 		//     		"width":$("#fixColumn").outerWidth(true),
	 		//     		$("#containeRight"+identifier).outerWidth(true)-$("#fixTitle"+identifier).outerWidth(true)+'px',
	 		//     		"height":$("#tableRight"+identifier+"0 thead").outerHeight()+"px",
	 		//     		"position":"relative"
	 		//     });
	 	var blankDivOff = new Object();
	 	blankDivOff.top = $("#fixTitle" + identifier).offset().top;
	 	blankDivOff.left = $("#fixTitle" + identifier).offset().left + $("#fixTitle" + identifier).outerWidth(true);
	 	$("#blankDiv" + identifier).offset(blankDivOff);
	 	//右上小块固定开始
	 	$("#fixTitle" + identifier).after("<div id=\"" + "fixDB" + identifier + "\"></div>");
	 	$("#fixDB" + identifier).append($("#tableLeft" + identifier).clone(true));
	 	//位置
	 	var fixDBOffset = new Object();
	 	fixDBOffset.left = $("#fixColumn" + identifier).offset().left;
	 	fixDBOffset.top = $("#fixColumn" + identifier).offset().top;
	 	$("#fixDB" + identifier).offset(fixDBOffset);
	 	//css设置
	 	$("#fixDB" + identifier).css({
	 		"width": $("#tableLeft" + identifier).css("width"),
	 		"height": $("#tableRight" + identifier + "0 thead").outerHeight() + "px",
	 		"position": "relative",
	 		"overflow": "hidden",
	 		"z-index": "60"
	 	})
	 	$("#fixDBTable" + identifier).css({
	 		"width": $("#tableLeft" + identifier).outerWidth() + "px"
	 	})
	 }

	 function tablesFixed_resize(identifier) {
	 	$("#hiddenDiv" + identifier).css({
	 		"visibility": "hidden",
	 		"width": $("#fixColumn" + identifier).css("width"),
	 		"height": $("#containeRight" + identifier).width() - $("#fixColumn" + identifier).width() * 2 + "px"
	 	});
	 	$("#fixTitle" + identifier).css({
	 		"width": $("#pageContainer" + identifier).outerWidth() - $("#tableLeft" + identifier).outerWidth() - 17 + "px",
	 		"height": $("#tableRight" + identifier + "0 thead").outerHeight() + "px",
	 		"overflow": "hidden",
	 		"position": "relative",
	 		"z-index": "50",
	 		"min-width": "830px"

	 	});
	 	if($("#fixTitle" + identifier).length > 0 && $("#fixDB" + identifier).length > 0) {
	 		var flexOffset = new Object();
	 		flexOffset.top = $("#fixDB" + identifier).offset().top;
	 		flexOffset.left = $("#fixDB" + identifier).offset().left + $("#fixDB" + identifier).outerWidth();
	 		$("#fixTitle" + identifier).offset(flexOffset);
	 	}
	 }
	 //功能:表格中的mousedown事件触发该函数,只有在鼠标左键被点击的时候,初始化选中矩形的上下左右四个角的坐标等一系列处理
	 //输入:事件,事件对应的dom对象
	 function deal_mousedown(e, obj, tableName) {
	 	if(e.button == 0) {
	 		var col_click = parseInt(obj.parent("td").attr("name"));
	 		var row_click = parseInt(obj.parent("td").parent("tr").attr("name"));
	 		var first_edit_col = find_first_edit(tableName, row_click);
	 		right = col_click - first_edit_col; //以鼠标为起点的矩形的右上角坐标（坐标值为name属性值）
	 		if(!first_edit_col){
	 			find_first_editqc(tableName, row_click);
	 		}
	 		top_min = row_click;
	 		bottom = row_click;
	 		left = col_click - first_edit_col;
	 		$(this).focus().select();
	 		obj.addClass("entrance");
	 		var e = arguments.callee.caller.arguments[0] || event;
	 		if(e && e.stopPropagation) {
	 			e.stopPropagation();
	 		} else if(window.event) {
	 			window.event.cancelBubble = true;
	 		}
	 		if($(".selected_oi").length > 0) {
	 			$(".selected_oi").each(function() {
	 				obj.removeClass("selected_oi");
	 			})
	 		}
	 		flag_down = 1;

	 	}
	 }
	 //功能:在表格中的表格元素的mousemove事件触发函数,给所有鼠标划过的表格元素加上selected_io,给第二次鼠标划过的表格元素去掉selected_io,并保证选中元素呈现矩形
	 //输入:鼠标划过事件的表格元素dom对象
	 function deal_mousemove(obj, tableName) {
	 	if(!flag_down) {
	 		return;
	 	}
	 	var col = parseInt(obj.attr("name").replace(/\s*/g, ""));
	 	var row = parseInt(obj.parent("tr").attr("name").replace(/\s*/g, ""));
	 	//	var now_row_length=data_doorinvent.data[row].tr.length;
	 	//	var pre_row_length=data_doorinvent.data[row-1].tr.length;

	 	var first_edit_col = find_first_edit(tableName, row);
	 	if(first_edit_col == null) {
	 		return;
	 	}
	 	var col_comp = col - first_edit_col;
	 	if(col_comp < 0) { //当前划过元素是不可编辑元素
	 		return;
	 	}
	 	//	var chayi=now_row_length-pre_row_length;
	 	if(top_min > row) {
	 		top_min = row;
	 	}
	 	if(bottom < row) {
	 		bottom = row;
	 	}
	 	if(right < col_comp) {
	 		right = col_comp;
	 	}
	 	if(left > col_comp) {
	 		left = col_comp;
	 	}
	 	var tableName_origin = tableName;
	 	if(tableName == "detailtable_otherin") {
	 		tableName = "." + tableName;
	 	} else {
	 		tableName = "#" + tableName + " table tbody";
	 	}
	 	for(var i = top_min; i <= bottom; i++) {
	 		var first_edit_col = find_first_edit(tableName_origin, i);
	 		if(!first_edit_col){
	 			first_edit_col=11;
	 			if(!left){
	 			left=0;
	 			}
	 			if(!right){
	 			right = 0;
	 			}

	 		}
	 		for(var j = left + first_edit_col; j <= right + first_edit_col; j++) {
	 			var temp = j + 1;
	 			if(($(tableName + ' tr[name=' + i + '] td:nth-child(' + temp + ')').children("input").length > 0) || ($(tableName + ' tr[name=' + i + '] td:nth-child(' + temp + ')').children("select").length > 0)) {
	 				$(tableName + ' tr[name=' + i + '] td:nth-child(' + temp + ')').addClass("selected_oi");
	 			}

	 		}
	 	}
	 	for(var i = top_min; i <= bottom; i++) {
	 		var first_edit_col = find_first_edit(tableName_origin, i);
	 		for(var j = col_comp + first_edit_col + 1; j <= right + first_edit_col; j++) {
	 			var temp = j + 1;
	 			$(tableName + ' tr[name=' + i + '] td:nth-child(' + temp + ')').removeClass("selected_oi");
	 		}
	 	} // for
	 	right = col_comp;
	 	for(var i = row + 1; i <= bottom; i++) {
	 		var first_edit_col = find_first_edit(tableName_origin, i);
	 		for(var j = left + first_edit_col; j <= right + first_edit_col; j++) {
	 			var temp = j + 1;
	 			$(tableName + ' tr[name=' + i + '] td:nth-child(' + temp + ')').removeClass("selected_oi");
	 		}
	 	} // for
	 	bottom = row;

	 }
	 //功能:在放开鼠标点击的时候,解除mousemove事件的绑定,选中结束
	 //输入:感应放开鼠标的表格元素的dom对象
	 function deal_mouseup(obj) {
	 	obj.parent("td").unbind("mousemove");
	 	flag_down = 0;
	 }
	 //	功能:粘贴事件触发该函数,给选中的表格元素分配粘贴的值
	 //	输入:粘贴事件对应的dom元素
	 //	输出:无
	 function pasteV(obj, tableName) {
		 var td_class=obj.parent().attr('class');
		 var arr_class=td_class.split(" ");
		 var rowcode = parseInt(obj.parent().parent().attr("name"));
		 var colcode = parseInt(obj.parent().attr("name"));

		 //如果是门或整机销售成本
		 if($.inArray("door",arr_class)>0){
			 //获取同列门配的值
			 var bro=$("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode+1 )+ "]").children("td[name=" + colcode + "]").children("input").val();
			 var sum=parseFloat(obj.val())+parseFloat(bro);
			 sum=sum.toFixed(2);
			 //处理销售成本的显示和json
			 $("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode-1 )+ "]").children("td[name=" + colcode + "]").html(sum);
			 data_inputds.data[rowcode-1].tr[colcode].value=sum;
		 }
		 //如果是门配或配件销售成本
		 if($.inArray("parts",arr_class)>0){
			 //获取同列正门
			 var bro=$("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode-1) + "]").children("td[name=" + colcode + "]").children("input").val();
			 var sum=parseFloat(obj.val())+parseFloat(bro);
			 sum=sum.toFixed(2);
			 $("#rendrTable_inputds table tbody").children("tr[name=" + (rowcode-2 )+ "]").children("td[name=" + colcode + "]").html(sum);
			 data_inputds.data[rowcode-2].tr[colcode].value=sum;
		 }
		var old_val=obj.val()//倒计时之前的值是粘贴之前的值
	 	setTimeout(function() {
	 		if(obj.val()) {
	 			var pastedV = obj.val();
	 			var pasted_arr = pastedV.split(/\s+/g);
	 			var selected_col = right - left + 1;
	 			var row_len = parseInt(pasted_arr.length / selected_col);
	 			var pastedi = 0;
	 			tableName_origin = tableName;
	 			if(tableName == "detailtable_otherin") {
	 				tableName = "." + tableName;
	 			} else {
	 				tableName = "#" + tableName + " table tbody";
	 			}
	 			for(var findi = top_min; findi < bottom + 1; findi++) {
	 				var first_edit_col = find_first_edit(tableName_origin, findi);
	 				for(var j = left + first_edit_col; j < right + 1 + first_edit_col; j++) {
	 					var findj = j + 1;
	 					if($(tableName + '  tr[name=' + findi + '] td:nth-child(' + findj + ')').children("input").length > 0) {
	 						var classname = $(tableName + '  tr[name=' + findi + '] td:nth-child(' + findj + ')').attr("class");
	 						var class_arr = classname.split(" ");
	 						for(var i = 0; i < class_arr.length; i++) {
	 							if(class_arr[i] == 'selected_oi') {
	 								//如果是浮点数或整数，则赋值否则停止粘贴，是第一个就还原
	 								var int_replace_res=pasted_arr[pastedi].replace(/^-?[1-9]\d*|0$/,"");
	 								if(int_replace_res){
	 									var float_replace_res=pasted_arr[pastedi].replace(/^-?([1-9]\d*\.\d*|0\.\d*[1-9]\d*|0?\.0+|0)$/,"");
	 									if(float_replace_res){
	 										if(pastedi==0){
	 											obj.val(old_val);
	 										}
	 										alertMsg.info("粘贴数据中包含不合法数据");
	 										return;//结束循环，结束粘贴
	 									}
	 								}
	 								$(tableName + ' tr[name=' + findi + '] td:nth-child(' + findj + ')').children("input").val(pasted_arr[pastedi]);
	 							}
	 						}
	 						pastedi++;
	 					}
	 					if($(tableName + '  tr[name=' + findi + '] td:nth-child(' + findj + ')').children("select").length > 0) {

	 						var classname = $(tableName + '  tr[name=' + findi + '] td:nth-child(' + findj + ')').attr("class");
	 						var class_arr = classname.split(" ");
	 						for(var i = 0; i < class_arr.length; i++) {
	 							if(class_arr[i] == 'selected_oi') {
	 								if(pasted_arr[pastedi] == "防盗门") {
	 									var val = 0;
	 								}

	 								if(pasted_arr[pastedi] == "数码产品") {
	 									var val = 1;
	 								}
	 								if(pasted_arr[pastedi] == "门配产品") {
	 									var val = 2;
	 								}

	 								$(tableName + ' tr[name=' + findi + '] td:nth-child(' + findj + ')').children("select").val(val);
	 							}
	 						}
	 						pastedi++;
	 					}

	 				}
	 				//				alert(bottom);
	 			} // for 外层
	 		} //if
	 	}, 100);
	 }
	 //	功能:在点击页面的时候,取消表格元素的选中状态
	 function body_click_unselect() {
	 	if($(".selected_oi").length > 0) {
	 		$(".selected_oi").each(function() {
	 			//		$("#posted_val").blur();
	 			$(this).removeClass("selected_oi");
	 		})
	 		$(".entrance").each(function() {
	 			//		$("#posted_val").blur();
	 			$(this).removeClass("entrance");

	 		})
	 	}
	 	$(".answer").css("display","none");
	 }
	 //功能:找到改行第一个可编辑表格单元的枞坐标
	 //输入:表格名,行数
	 //输出:纵坐标
	 function find_first_edit(tableName, row) {
	 	if(tableName == "detailtable_otherin") {
	 		tableName = "." + tableName;
	 	} else {
	 		tableName = "#" + tableName + " table tbody";
	 	}
	 	var i = 1;
	 	while($(tableName + ' tr[name=' + row + '] td:nth-child(' + i + ')').length > 0) {
	 		if($(tableName + ' tr[name=' + row + '] td:nth-child(' + i + ')').children("input").length > 0) {
	 			return parseInt($(tableName + ' tr[name=' + row + '] td:nth-child(' + i + ')').attr("name"));
	 		}
	 		i++
	 	}
	 	return null;
	 }
	 //防盗门期初表专用
	 function find_first_editqc(tableName, row) {
	 	if(tableName == "detailtable_otherin") {
	 		tableName = "." + tableName;
	 	} else {
	 		tableName = "#" + tableName + " table tbody";
	 	}
	 	var i = 1;
	 	while($(tableName + ' tr[name=' + row + '] td:nth-child(' + i + ')').length > 0) {
	 		if($(tableName + ' tr[name=' + row + '] td:nth-child(' + i + ')').children("input").length > 0) {
	 			return 11;
	 		}
	 		i++
	 	}
	 	return null;
	 }
	 //功能:双击表格单元,选中整行
	 //输入:表格名称,双击事件对应dom对象
	 function selected_line(tableName, obj) {
	 	var col_click = parseInt(obj.attr("name"));
	 	var row_click = parseInt(obj.parent("tr").attr("name"));
	 	var row_origin = row_click;
	 	var first_edit_col = find_first_edit(tableName, row_origin);
	 	right = col_click - first_edit_col; //以鼠标为起点的矩形的右上角坐标（坐标值为name属性值）
	 	top_min = row_click;
	 	left = col_click - first_edit_col;
	 	var rolli = row_origin;
	 	var tableName_origin = tableName;
	 	if(tableName == "detailtable_otherin") {
	 		tableName = "." + tableName;
	 	} else {
	 		tableName = "#" + tableName + " table tbody";
	 	}
	 	var rollj = col_click + 1;
	 	while(($(tableName + ' tr[name=' + rolli + '] td:nth-child(' + rollj + ')').length > 0) || ($(tableName + ' tr[name=' + rolli + ']').children("td").length == 1)) {
	 		if($(tableName + ' tr[name=' + rolli + '] td:nth-child(' + rollj + ')').children("input").length > 0) {
	 			$(tableName + ' tr[name=' + rolli + '] td:nth-child(' + rollj + ')').addClass("selected_oi");
	 		}
	 		rolli = rolli + 1;
	 		rollj = left + find_first_edit(tableName_origin, rolli) + 1;
	 	} //while;
	 	bottom = rolli;
	 }

	 //验证数据合法性
	 function isDataValid(data) {
		 if(data != null && data != "" && data != "undefined"  && data != "null" && data != null && data != "0.00" && data != "0") {
			 return true;
		 } else {
			 return false;
		 }
	 }
