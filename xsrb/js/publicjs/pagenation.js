//分页插件

(function($) {
	 ms = {
		init: function(obj, args) {
			return (function() {
				ms.fillHtml(obj, args);
				ms.unbindEvent(obj, args);
				ms.bindEvent(obj, args);
			})();
		},
		//填充html
		fillHtml: function(obj, args) {
			return (function() {
				obj.empty();
				//首页
				if (args.current == 1) {
					obj.append('<span class="disabled">首页</span>');
				} else {
					obj.remove('.firstPage');
					obj.append('<a href="javascript:;" class="firstPage">首页</a>');
				}
				//上一页
				if (args.current > 1) {
					obj.append('<a href="javascript:;" class="prevPage">上一页</a>');
				} else {
					obj.remove('.prevPage');
					obj.append('<span class="disabled">上一页</span>');
				}
				//中间页码
//				if (args.current != 1 && args.current >= 4 && args.pageCount != 4) {
//					obj.append('<a href="javascript:;" class="tcdNumber">' + 1 + '</a>');
//				}
//				if (args.current - 2 > 2 && args.current <= args.pageCount && args.pageCount > 5) {
//
//					obj.append('<span>...</span>');
//				}
				var start = args.current - 2,
					end = args.current + 2;
				if ((start > 1 && args.current < 4) || args.current == 1) {
					end++;
				}
				if (args.current > args.pageCount - 4 && args.current >= args.pageCount) {
					start--;
				}
				for (; start <= end; start++) {
					if (start <= args.pageCount && start >= 1) {
						if (start != args.current) {
							obj.append('<a href="javascript:;" class="tcdNumber">' + start + '</a>');
						} else {
							obj.append('<span class="current">' + start + '</span>');
						}
					}
				}
				if (args.current + 2 < args.pageCount - 1 && args.current >= 1 && args.pageCount > 5) {
					obj.append('<span>...</span>');
				}
				if (args.current != args.pageCount && args.current < args.pageCount - 2 && args.pageCount != 4) {
					obj.append('<a href="javascript:;" class="tcdNumber">' + args.pageCount + '</a>');
				}
				//下一页
				if (args.current < args.pageCount) {
					obj.append('<a href="javascript:;" class="nextPage">下一页</a>');
				} else {
					obj.remove('.nextPage');
					obj.append('<span class="disabled">下一页</span>');
				}
				//末页
				if (args.current >= args.pageCount || args.pageCount <= 0 || args.current <= 0) {
					obj.append('<span class="disabled">末页</span>');

				} else {
					obj.remove('.lastPage');
					obj.append('<a href="javascript:;" class="lastPage">末页</a>');

				}
				var classnames=obj.parent().prevObject.selector.substring(12,20);
//				console.log(classnames);
				obj.append('<input type="text" value="" class="input_page_'+classnames+'" style="width:25px;"/><input class="go" value="GO" type="button" />');
			})();
		},

		//绑定事件
		bindEvent: function(obj, args) {
			return (function() {
				obj.on("click", "a.tcdNumber", function() {
					var current = parseInt($(this).text());
					ms.fillHtml(obj, {
						"current": current,
						"pageCount": args.pageCount
					});
					if (typeof(args.backFn) == "function") {
						args.backFn(current);
					}
				});
				//首页
				obj.on("click", "a.firstPage", function() {
					var current = parseInt(obj.children("span.current").text());
					ms.fillHtml(obj, {
						"current": 1,
						"pageCount": args.pageCount
					});
					if (typeof(args.backFn) == "function") {
						args.backFn(1);
					}
				});
				//上一页
				obj.on("click", "a.prevPage", function() {
					var current = parseInt(obj.parent().find("span.current").text());
					if (current > 1) {
						current = current - 1;
					}
					ms.fillHtml(obj, {
						"current": current,
						"pageCount": args.pageCount
					});
					if (typeof(args.backFn) == "function") {
						args.backFn(current);
					}
				});
				//下一页
				obj.on("click", "a.nextPage", function() {
					var current = parseInt(obj.parent().find("span.current").text());
					if (current < args.pageCount) {
						ms.fillHtml(obj, {
							"current": current + 1,
							"pageCount": args.pageCount
						});
					} else {
						ms.fillHtml(obj, {
							"current": current,
							"pageCount": args.pageCount
						});

					}

					if (typeof(args.backFn) == "function") {
						args.backFn(current + 1);
					}
				});
				//末页
				obj.on("click", "a.lastPage", function() {
					var current = parseInt(obj.children("span.current").text());
					ms.fillHtml(obj, {
						"current": args.pageCount,
						"pageCount": args.pageCount
					});
					if (typeof(args.backFn) == "function") {
						args.backFn(args.pageCount);
					}
				});
				obj.on("click", "input.go", function() {
					var _this=$(this);
					var current = parseInt(_this.prev().val());
					ms.fillHtml(obj, {
						"current": current,
						"pageCount": args.pageCount
					});
					
					if (typeof(args.backFn) == "function") {
						args.backFn(current);
					}
			});
			})();
		},
		unbindEvent: function(obj, args) {
			return (function() {
				obj.off();
			})();
		}
	}
})(jQuery);
	$.fn.createPage = function(options) {
		var args = $.extend({
			pageCount: 10,
			current: 1,
			backFn: function() {}
		}, options);

		ms.init(this, args);
	}