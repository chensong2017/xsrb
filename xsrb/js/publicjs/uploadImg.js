// 店铺图标上传
jQuery(function() {
      var  uploader;
    // 初始化Web Uploader
    uploader = WebUploader.create({
        // 自动上传。
        auto: true,
        // swf文件路径
         swf: 'js/Uploader.swf',
        // 文件接收服务端。
        //server: 'http://chaogetest.wsy.me/upload/uploadfile.php',
        // 设置可以上传相同的图片
        duplicate: 'true',

        server:"http://192.111.111.178/xsrb/code/index.php/home/SPZMXlist/loadingExcelQc/types/0",

        // 选择文件的按钮。可选。
        // 内部根据当前运行是创建，可能是input元素，也可能是flash.
        pick: '#icon_select',
        // 只允许选择文件，可选。
    });
    // 当有文件添加进来的时候
    uploader.on( 'fileQueued', function( file ) {
        //删除子元素，保证只有一个图片上传
        $("#icon").html("")
        //$("#icon").html("").text(file.name);
        var $li = $(
                '<div id="' + file.id + '" class="icon-img">'
                + file.name
                + '<i class="cancel" onclick="delIcon('+"'"+file.id+"'"+')">删除</i>'
                + '</div>'
            ),
            $img = $li.find('img');

        $("#icon").append( $li );
    });

    // 文件上传成功，给item添加成功class, 用样式标记上传成功。
    uploader.on( 'uploadSuccess', function( file,response ) {
        if(response.resultcode==0){
            alert("上传成功");
        }else{
            alert("上传失败");
        }
    });
    function delIcon(id){
        $("div#"+id).remove();
        $("#shop_icon").val("");
    }
});
