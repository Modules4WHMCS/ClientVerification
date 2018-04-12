{literal}

    <script src="https://code.jquery.com/ui/1.12.1/jquery-ui.min.js"></script>

    <link type="text/css" href="https://code.jquery.com/ui/1.12.1/themes/ui-lightness/jquery-ui.css" rel="stylesheet">

<link type="text/css" href="../modules/addons/clientverification/3rdlib/js/bootstrap3-dialog/css/bootstrap-dialog.min.css" rel="stylesheet">
<script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/bootstrap3-dialog/js/bootstrap-dialog.min.js"></script>


    <link rel="stylesheet" href="../modules/addons/clientverification/js/3rdlib/plupload-3.1.2/js/jquery.ui.plupload/css/jquery.ui.plupload.css"  type="text/css"/>
    <script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/plupload-3.1.2/js/plupload.full.min.js"></script>
    <script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/plupload-3.1.2/js/jquery.ui.plupload/jquery.ui.plupload.js"></script>



    <!-- jqGrid -->
<link rel="stylesheet" href="../modules/addons/clientverification/js/3rdlib/jqGrid/css/ui.jqgrid.css"  type="text/css"/>
<script src="../modules/addons/clientverification/js/3rdlib/jqGrid/plugins/ui.multiselect.js" type="text/javascript"></script>
<script src="../modules/addons/clientverification/js/3rdlib/jqGrid/js/i18n/grid.locale-en.js" type="text/javascript"></script>
<script src="../modules/addons/clientverification/js/3rdlib/jqGrid/js/jquery.jqgrid.src.js" type="text/javascript"></script>
<script src="../modules/addons/clientverification/js/3rdlib/jqGrid/plugins/jquery.contextmenu.js" type="text/javascript"></script>
<script src="../modules/addons/clientverification/js/3rdlib/jqGrid/js/jqmodal.js" type="text/javascript"></script>
<script src="../modules/addons/clientverification/js/3rdlib/jqGrid/js/grid.subgrid.js" type="text/javascript"> </script>


<script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/jquery-loadmask-0.4/jquery.loadmask.js"></script>
<link rel="stylesheet" href="../modules/addons/clientverification/js/3rdlib/jquery-loadmask-0.4/jquery.loadmask.css"  type="text/css"/>

<script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/bootstrap-switch/js/bootstrap-switch.js"></script>
<link rel="stylesheet" href="../modules/addons/clientverification/js/3rdlib/bootstrap-switch/css/bootstrap-switch.css"  type="text/css"/>
    <link href="https://gitcdn.github.io/bootstrap-toggle/2.2.2/css/bootstrap-toggle.min.css" rel="stylesheet">
    <script src="https://gitcdn.github.io/bootstrap-toggle/2.2.2/js/bootstrap-toggle.min.js"></script>


<link type="text/css" href="../modules/addons/clientverification/js/3rdlib/jquery-ui-1.10.1/css/cupertino/jquery-ui.css" rel="Stylesheet" />
<link type="text/css" href="../modules/addons/clientverification/css/common.css" rel="Stylesheet" />


    <script type="text/javascript" src="../modules/addons/clientverification/js/common.js"></script>
<script type="text/javascript" src="../modules/addons/clientverification/js/clientui.js"></script>

<script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/Notify.js"></script>

    <script type="text/javascript" src="https://www.gstatic.com/charts/loader.js"></script>

    <link href="../modules/addons/clientverification/js/3rdlib/jquery-ui-iconfont/jquery-ui-1.12.icon-font.css" rel="stylesheet" type="text/css" />


    <link type="text/css" href="../modules/addons/clientverification/js/3rdlib/colorbox/colorbox.css" rel="stylesheet">
    <script type="text/javascript" src="../modules/addons/clientverification/js/3rdlib/colorbox/jquery.colorbox.js"></script>



{/literal}





<div id="clientverification_loader" style="height:500px;">&nbsp;</div>
<div id="clientverification_ctrl_panel" style="display:none;font-size:smaller;">

    <div id="pageBanner" style="text-align: center;"><h1></h1></div>


<div id="mainpanel" style="padding:0;width:100%;">



    <div id="uplFileDlg"></div>
    <div id="users" style="width:100%;padding:0;">
        <table style="width:100%;" id="usersGrid"></table>
        <div id="usersgridpager"></div>
    </div>



</div>




<div style="display:none;" id="uploadDlg">

</div>





    <div class="container" style="display:none;" id="doc_img_panel_template_root">
        <div class="panel-group">


        </div>
    </div>



    <div style="display:none;white-space:unset !importante;" id="doc_img_panel_template" class="panel-primary">
        <div  class="panel-heading">

            <h4 class="panel-title">
                <a data-toggle="collapse" href="#collapse1">Driver License</a>
            </h4>

        </div>
        <div class="panel-collapse collapse" id="collapse1">
        <div class="panel-body" id="docimg">
            <div class="row">
                <div class="col-sm-7" id="docImgCol">
                    <img id="doc_img" src="../modules/addons/clientverification/Intime 2.jpg" href="../modules/addons/clientverification/Intime 2.jpg">
                </div>

                <div class="col-sm-3 ">

                </div>
                <div class="col-sm-2">
                    <span class="pull-right">
                        <button type="button" class="btn btn-success float-right">Accept</button>
                        <button type="button" class="btn btn-warning float-right">Reject</button>
                        <button type="button" class="btn btn-danger float-right">Delete</button>
                        </span></div>

            </div>
        </div>
        </div>
    </div>



</div>



