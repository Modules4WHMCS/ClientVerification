

$(document).ready(function()
{

    $("#clientverification_loader").mask("Loading.........");

    $(window).bind('resize', resizeGridPanel).trigger('resize');
	$("#maintabs").tabs({
        active:0,
        select: function(event, tab) /* <=1.8*/
        {
        	$(window).trigger('resize');
        },
        create:function(event, ui) 
        {
            $(window).trigger('resize');
        },
        activate: function(event, ui) /* >=1.9*/
        {
            switch(ui.newPanel[0].id){

                case "users":{
                    $("#usersGrid").trigger( 'reloadGrid' );
                    break;
                }
                case "paymentgw":{
                    $("#paymentgwGrid").trigger('reloadGrid');
                    break;
                }
                case "settings":{
                    loadSettings();
                    break;
                }
            }

            $(window).trigger('resize');
        }
    });


    initUsersPanel();
    initPaymentGWPanel();

    $("#savesettings").click(saveSettings);



    if($("#clientverification_loader").isMasked()){
        $("#clientverification_loader").unmask();
        $("#clientverification_loader").hide();
        $("#clientverification_ctrl_panel").toggle();

    }

    $(window).trigger('resize');

});

function saveSettings()
{
    $("#settings").mask("Processing....");
    var params = Array();
    params.croncmd = $("#croncmd").val();
    params.doctypes = $("#doctypes").val();
    params.usersdocroot = $("#usersdocroot").val();
    params.adminusername = $("#adminusername").val();
    params.maxuploadfilesize = $("#maxuploadfilesize").val();
    params.docsextensions = $("#docsextensions").val();

    ajaxCall('saveSettings',function (status, json) {
        switch (status) {
            case 'success': {
            }
            case 'error':
            case 'done': {
                $("#settings").unmask();
                break;
            }
        }
    },params,"POST",true);
}

function loadSettings()
{
    $("#settings").mask("Loading....");
    ajaxCall('getSettings',function(status,json){
        switch(status) {
            case 'success': {

                if(json == null){return;}
                if(json.croncmd) {
                    $("#croncmd").val(json.croncmd);
                }
                if(json.adminusername){
                    $("#adminusername").val(json.adminusername);
                }
                if(json.usersdocroot){
                    $("#usersdocroot").val(json.usersdocroot);
                }
                if(json.doctypes){
                    $("#doctypes").val(json.doctypes);
                }
                if(json.maxuploadfilesize){
                    $("#maxuploadfilesize").val(json.maxuploadfilesize);
                }
                if(json.docsextensions){
                    $("#docsextensions").val(json.docsextensions);
                }
            }
            case 'error':
            case 'done': {
                $("#settings").unmask();
                break;
            }
        }
    },null,null,true);

}

function initUsersPanel() {
    $("#usersGrid").jqGrid({
        url: 'addonmodules.php?module=clientverification&f=getUsers',
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
        subGrid: true,
        loadComplete:function(){
            $("td[aria-describedby=usersGrid_verified]").each(function(){
                var inputEl=$(this).find('input[type=checkbox]');
                inputEl.bootstrapToggle({on:'YES',off:'NO'});
                inputEl.bootstrapToggle(inputEl.prop( "checked" )?"on":"off").change(function(){
                    var params = {};
                    var rowID=$($(this).parent().parent().parent()).attr('id');
                    params.uuid = $("#usersGrid").jqGrid ('getCell', rowID, 'uuid');
                    params.value=$(this).prop('checked');
                    ajaxCall('saveUser',null,params,null,true);
                });
            });

        },
        colNames: ['','id', 'User', 'Verified'],
        colModel: [
            {name: '', index: '', hidden: true},
            {name: 'uuid', index: 'uuid', hidden: true},
            {name: 'user', jsonmap: 'user', index: 'user', editable: false, width:150},
            {name: 'verified', align:'center',jsonmap: 'verified', index: 'verified',editable:true,
                editrules: {edithidden:true},formatter: "checkbox",formatoptions: { disabled: false},
                edittype:'checkbox',editoptions: { value:"yes:no"}
            }

        ],
        subGridRowExpanded: function(subgrid_id, row_id)
        {
            var parentGrid = subgrid_id.split("-")[0]+"-tsub";
            var subgrid_table_id, pager_id;
            subgrid_table_id = subgrid_id+"-tsub";
            pager_id = "p_"+subgrid_table_id;
            var jid = $("#blcheckerGrid").jqGrid ('getCell', row_id, 'jid');



            var uuid = $("#usersGrid").jqGrid ('getCell', row_id, 'uuid');
            var params = Array();
            params.uuid = uuid;
            $("#subgrid_id").mask("Loading....");
            ajaxCall('getUserDocs',function(status,json){
                switch(status) {
                    case 'success': {
                        var rootPanel = subgrid_id+"rootpanel";

                        var imgPanelTemplateRoot = $("#doc_img_panel_template_root").clone();
                        imgPanelTemplateRoot.appendTo("#"+subgrid_id);
                        imgPanelTemplateRoot.attr("id",rootPanel);
                        imgPanelTemplateRoot.show();

                        for(var i=0;i<json.length;i++) {
                            var imgPanelTemplate = $("#doc_img_panel_template").clone();
                            var panelID="newid"+i;
                            imgPanelTemplate.attr("id",panelID );

                            imgPanelTemplate.appendTo("#"+rootPanel+">div" );
                            $("#"+panelID).find('.panel-title>a').attr("href","#collapse"+i);
                            $("#"+panelID).find('.panel-title>a').html(json[i].doc_type);
                            $("#"+panelID).find('.panel-title>b').append(json[i].status);

                            $("#"+panelID).find("div.panel-collapse").attr("id","collapse"+i);


                            $("#"+panelID).find("div.panel-body img").attr("id","doc_img"+i);
                            $("#"+panelID).find("div.panel-body img").attr("src",json[i].img);
                            $("#"+panelID).find("div.panel-body img").attr("href",json[i].img);

                            $("#"+panelID).find("#acceptControlBtnGroup").attr("fid",json[i].fid);

                            $("#"+panelID).find("div.panel-body img").colorbox({
                                'photo':true});
                            imgPanelTemplate.show();



                        }


                        $("#acceptControlBtnGroup>button").on("click",function(){
                            var parentPanel = $( this ).parent().parent().parent().parent().parent().parent();
                            var params = Array();
                            params.status = $( this ).text();
                            params.fid = $( this ).parent().attr("fid");
                            parentPanel.mask("Loading....");
                            ajaxCall('setUserDocStatus',function(status,json){
                                switch(status) {
                                    case 'success': {

                                        if(json.status == "deleted"){
                                            parentPanel.remove();
                                        }
                                        else{
                                            var titles=parentPanel.find('.panel-title>b').text().split("-");
                                            parentPanel.find('.panel-title>b').html(titles[0]+" - "+json.status);
                                        }

                                    }
                                    case 'error':
                                    case 'done': {
                                        parentPanel.unmask();
                                        break;
                                    }
                                }
                            },params,null,true);



                        });


                        $(window).trigger('resize');

                    }
                    case 'error':
                    case 'done': {
                        $("#subgrid_id").unmask();
                        break;
                    }
                }
            },params,null,true);

        }


    }).navGrid("#usersgridpager",
        {cloneToTop:true,edit:false,add:false,del:false,search:false});

    $(window).trigger('resize');
}




function initPaymentGWPanel() {
    $("#paymentgwGrid").jqGrid({
        url: 'addonmodules.php?module=clientverification&f=GetActiveGateways',
        height: 'auto',
        autowidth: true, shrinkToFit: true,
        pager: $("#paymentgwpager"),
        rowNum: 200,
        rowList: [10, 20, 30, 50, 100, 500],
        viewrecords: true,
        toppager: true,
        sortorder: "desc",
        sortname: "name",
        jsonReader: jSonReaderOptions,
        multiselect: false,
        beforeProcessing: gridBeforeProcessing,
        loadComplete:function(){
            $("td[aria-describedby=paymentgwGrid_verified]").each(function(){
                var inputEl=$(this).find('input[type=checkbox]');
                inputEl.bootstrapToggle({on:'YES',off:'NO'});
                inputEl.bootstrapToggle(inputEl.prop( "checked" )?"on":"off").change(function(){
                    var params = {};
                    var rowID=$($(this).parent().parent().parent()).attr('id');
                    params.gateway = $("#paymentgwGrid").jqGrid ('getCell', rowID, 'gateway');
                    params.value=$(this).prop('checked');
                    ajaxCall('saveGatewayOpt',null,params,null,true);
                });
            });
        },
        colNames: ['','Gateway', 'Verification Require'],
        colModel: [
            {name: '', index: '', hidden: true},
            {name: 'gateway', align:'center',jsonmap: 'gateway', index: 'gateway', editable: false, width:150},
            {name: 'verified', align:'center',jsonmap: 'verified', index: 'verified',editable:true,
                editrules: {edithidden:true},formatter: "checkbox",formatoptions: { disabled: false},
                edittype:'checkbox',editoptions: { value:"yes:no"}
            }
        ]
    }).navGrid("#paymentgwpager",
        {cloneToTop:true,edit:false,add:false,del:false,search:false});
}















