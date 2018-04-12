
var docTypes;

$(document).ready(function()
{

    $("#clientverification_loader").mask("Loading.........");


    getAccountStatus();
    initUploader();



    $(window).bind('resize', resizeGridPanel).trigger('resize');


    initUsersPanel();


    ajaxCall('getDocTypes',function(status,json){
        switch(status) {
            case 'success': {
                docTypes=json;
            }
            case 'error':
            case 'done': {
                $("#settings").unmask();
                break;
            }
        }
    });


    if($("#clientverification_loader").isMasked()){
        $("#clientverification_loader").unmask();
        $("#clientverification_loader").hide();
        $("#clientverification_ctrl_panel").toggle();

    }

    $(window).trigger('resize');

});

var timeoutid=null;

function getAccountStatus()
{

    if(timeoutid){
        clearTimeout(timeoutid);
    }

    ajaxCall('isAccountVerified',function(status,json){
        switch(status) {
            case 'success': {
                if(json.is_verified === "true"){

                    $("#pageBanner>h1").html("Account Verified");
                    $("#pageBanner>h1").css("color","green");

                }
                else{
                    $("#pageBanner>h1").html("Account Not Verified");
                    $("#pageBanner>h1").css("color","red");
                }
            }
            case 'error':
            case 'done': {
                timeoutid=setTimeout(getAccountStatus,10000);
                break;
            }
        }
    });


}


function initUsersPanel() {
    $("#usersGrid").jqGrid({
        url: 'index.php?m=clientverification&f=getUserDoc',
        height: 'auto',
        autowidth: true, shrinkToFit: true,
        pager: $("#usersgridpager"),
        rowNum: 200,
        rowList: [10, 20, 30, 50, 100, 500],
        viewrecords: true,
        toppager: true,
        sortorder: "desc",
        sortname: "user",
        jsonReader: jSonReaderOptions,
        multiselect: false,
        beforeProcessing: gridBeforeProcessing,
        subGrid: false,

        colNames: ['fid', 'File Name', 'Document Type','Status','img'],
        colModel: [
            {name: 'fid', index: 'fid', hidden: true},
            {name: 'filename', jsonmap: 'filename', index: 'filename', editable: false, width:150,
                formatter:function(cellvalue, options, rowObject){
                    return '<img style="display:inline; width:200px;height:100px;" src="'+rowObject.img+'"/> '+cellvalue;
                }},
            {name: 'doc_type', align:'center',jsonmap: 'doc_type', index: 'doc_type',editable:false},
            {name: 'status', align:'center',jsonmap: 'status', index: 'status',editable:false},
            {name: 'img', align:'center',jsonmap: 'img', index: 'img',editable:false,hidden:true}

        ]
    }).navGrid("#usersgridpager",
        {cloneToTop:true,edit:false,add:false,del:true,search:false},
        // Edit options
        {},
        // Add options
        {},
        //del options
        {
            mtype: 'GET',
            url: 'index.php?m=clientverification&f=delUserDoc',
            serializeDelData:function(postdata){
            var rowdata = jQuery('#usersGrid').getRowData(postdata.id);
            return {id:postdata.id,fid:rowdata.fid};
            },

            reloadAfterSubmit: true
        }




        );

    $(window).trigger('resize');
}


function initUploader()
{
    var fileTypes=[];
    $("#uplFileDlg").plupload({
        // General settings
        runtimes : 'html5,flash,silverlight,html4',
        url : "index.php?m=clientverification&f=uploadUserDocToStorage",

        // Maximum file size
        max_file_size : '5mb',
        chunk_size: '1mb',

        // Resize images on clientside if we can
        resize : {
            width : 200,
            height : 200,
            quality : 90,
            crop: true // crop to exact dimensions
        },

        // Specify what files to browse for
        filters : [
            {title : "Image files", extensions : "jpg,gif,png"}
        ],

        // Rename files by clicking on their titles
        rename: true,
        // Sort files
        sortable: true,
        // Enable ability to drag'n'drop files onto the widget (currently only HTML5 supports that)
        dragdrop: true,
        // Views to activate
        views: {
            list: false,
            thumbs: true, // Show thumbs
            active: 'thumbs'
        },
        preinit : {
            BeforeUpload: function(up, file) {

                var doctype=$('input[name="'+file.name+'"]:checked').val();
                if(!doctype){
                    BootstrapDialog.alert("Please select document type for image "+file.name);
                    return false;
                }
                $.extend(up.settings.multipart_params, { origFileName : file.name,
                                                            fileSize:file.size,
                                                            docType: doctype});
            },
            UploadComplete: function(up,files){
                $("#usersGrid").trigger('reloadGrid');
            }
        },

        init:{
            FilesAdded:function(up,files){

                for(var key in files){
                    var parent=$(".plupload_file fieldset[id=\""+files[key].name+"\"]").parent();
                    if(parent){
                        parent.remove();
                        up.removeFile(files[key].name);
                    }
                    var inputElem='<fieldset id="'+files[key].name+'">';

                    for(var i in docTypes){
                        inputElem+='<input type="radio" value="'+docTypes[i]+'" name="'+files[key].name+'"> '+docTypes[i]+'<br>';
                    }
                    inputElem+='</fieldset>';
                    $(".plupload_file>div[title=\""+files[key].name+"\"]").parent()
                        .append(inputElem);


                }

            },
            FileUploaded: function(up, File, info)
            {
                var data = validateServerResponse(info.response);
                if(!data){
                    File.status = plupload.FAILED;
                    up.trigger('UploadProgress', File);
                }
                else{

                }
            },
            ChunkUploaded: function(up, File, info)
            {
                var data = validateServerResponse(info.response);
                if(!data){
                    File.status = plupload.FAILED;
                    up.trigger('UploadProgress', {uploader:up,file:File});
                    up.stop();
                    return false;
                }
            },
            Error: function(up, args)
            {
                BootstrapDialog.alert({message: args.message,type: BootstrapDialog.TYPE_DANGER});
            }
        },

        // Flash settings
        flash_swf_url : '../modules/addons/clientverification/js/3rdlib/plupload-3.1.2/js/Moxie.swf',

        // Silverlight settings
        silverlight_xap_url : '../modules/addons/clientverification/js/3rdlib/plupload-3.1.2/js/Moxie.xap'
    });


}

















