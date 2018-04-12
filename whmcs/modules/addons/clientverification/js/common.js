

jSonReaderOptions = {
		root: "response.rows",
        repeatitems : false, 
        page:"response.page",
        total:"response.total",
        records:"response.records"
};


jQuery.extend(jQuery.jgrid.defaults,{
	reloadAfterSubmit:true,
    loadonce: false,
	datatype: "json", 
	height: 'auto', 
	width:'100%',
	//autowidth: true,autoHeight: true,
	shrinkToFit:true,scrollOffset:0,
	gridview: true,
	rowNum:20, 
	rowList:[10,20,30,100,200],
	toppager:true,
	pginput:false,
	viewrecords: true, 
	sortorder: "desc",
	jsonReader:jSonReaderOptions,
	multiselect: false
});


if(typeof console === "undefined" || typeof console.log === "undefined"){
	console = {};
	console.log = function(msg){}
}


function sprintf(format, etc) 
{
    var arg = arguments;
    var i = 1;
    return format.replace(/%((%)|s)/g, function (m) { return m[2] || arg[i++]; });
}



function resizeGridPanel()
{ 
	var grid = $('.ui-jqgrid-btable');
    var mainPanelWidth = $('#mainpanel').width()-5;
    if(grid){
        grid.each(function(index) {
        	var gridParentWidth = mainPanelWidth;
        	var gridId = $(this).attr('id'); 
            if(gridId.indexOf('-tsub') != -1){
            	var parentGridId=gridId.split('-tsub');
            	parentGridId.pop();
            	parentGridId=parentGridId.join("-tsub");
            	gridParentWidth = $('#'+parentGridId).width();
           }
            
           $('#' + gridId).setGridWidth(gridParentWidth,true);
           var groupHeaders = $('#' + gridId).jqGrid("getGridParam", "groupHeader");
            if (groupHeaders != null) {
            
            	$('#' + gridId).jqGrid('destroyGroupHeader');
            	$('#' + gridId).jqGrid('setGroupHeaders',groupHeaders);
            }           
        });
    }
}


function gridBeforeProcessing(data, status, xhr)
{
    if(typeof data == 'undefined' || data == null){       
        console.log("Empty response from server");
    }
    else if(typeof data.status != 'undefined'){
        if(data.status === 'error'){
        	BootstrapDialog.alert({
                title: 'ERROR',
                message: data.msg,
                type: BootstrapDialog.TYPE_DANGER
                
            });

        }
        else if(data.status === 'ok' && (typeof data.msg !== 'undefined')){
        	BootstrapDialog.alert(data.msg);

        }
    
       
    if(typeof data.response != 'undefined'){
        
        gridBeforeProcessing(data.response, status, xhr);
    }
}
}

function gridAfterFormSubmit(response, postdata)
{
    try{
        var JSONObj = $.parseJSON(response.responseText);
    }catch(e){alert('exception');}
                                    
    if(!JSONObj){return [false,'Empty response from server'];}
    if(JSONObj.status === 'error'){return [false,JSONObj.msg];}
    return [true]; 
}

function validateServerResponse(rawData)
{

    var data = null;
    if(typeof rawData === 'object'){
        data = rawData;
    }
    else{
        try{
            data = $.parseJSON(rawData);

        }catch(e)
        {
            
            if(!rawData)

                    console.log("Empty response from server");
            else{

                
                if(rawData.match(/action="dologin.php"/g))
                    document.location.replace("/");
            }
        }
    }
    
    if(!data){

        console.log("Empty response from server");
    }
    else if(data.status === 'error'){
    	$.notify(data.msg, "error");

    }
    else if(data.status === 'ok' && (typeof data.msg !== 'undefined')){
    	BootstrapDialog.alert(data.msg);

    }
    else if(data.status !== 'ok'){

        console.log("Unknown response from server");
        console.log(data.response);
    }
    else{
        if(typeof data.response !== 'undefined')
            return data.response; 
        return true;
    }
    
    return false;
}

function gridPagerAfterSubmit(response,postdata)
{

	$(this).trigger("reloadGrid");
    validateServerResponse(response.responseText);
    return [true];
}


function customvalue(elem, operation, value) 
{
    if (operation === 'get') {
        return $(elem).val();
    } else if (operation === 'set') {
      
        $(elem).val(value);
    }
}






function getBackEndUrl(cmdArgs,cmd,bIsAddon)
{
	cmdArgs = cmdArgs || new Array();
	//var bIsAddon = false;
	var data = {};
	data['modop'] = "custom";

    	//bIsAddon = true;

    var userid = $("#userid").val();
    var url = document.URL.substring(0,document.URL.lastIndexOf('/'))+'/';
    if(bIsAddon){
    	url += 'addonmodules.php?module=clientverification';
    	data['f'] = cmd;
    }
    else if(userid){
    	
    	if(whmcsversion.indexOf("5.") >= 0){
    		url += 'clientsservices.php';
    	}
    	else{
    		url += 'clientshosting.php';
    	}
    	
    	data['userid'] = $("#userid").val();
    	data['ac'] = cmd;
    	data['id'] = $("#serviceid").val();
    }
    else{
    	url += 'index.php?m=clientverification';
    	data['f'] = cmd;
    }
        
    var token=$('input[name="token"]').val();
    if(token){
    	data['token']=token;
    }
    
    for(var arg in cmdArgs){
        data[arg] = cmdArgs[arg];
   }
   data['id'] = $("#serviceid").val();
   

   return {reqData:data,url:url};
}



function ajaxCall(cmd,callback,cmdArgs,reqType,bIsAddon)
{

	
    reqType = reqType || 'GET';
    cmdArgs = cmdArgs || new Array();
    var data = getBackEndUrl(cmdArgs,cmd,bIsAddon);
    
    $.ajax({
    	url: data['url'],
        type: reqType,
        data: data['reqData'],
        success: function(html)
        {
        	var json = validateServerResponse(html);

            if(callback) callback('success',json,html);
        },
        error: function(jqXHR,textStatus,errorThrown)
        {
        	console.error(textStatus,errorThrown,jqXHR);

        	
            if(callback){
                callback('error','',jqXHR,textStatus,errorThrown);
            }
        }
    }).done(function(){
                if(callback) callback('done','');
            });
}


function timestamp()
{
	if (!Date.now) {
	    Date.now = function() { return new Date().getTime(); }
	}
	return Math.floor(Date.now() / 1000);
}

