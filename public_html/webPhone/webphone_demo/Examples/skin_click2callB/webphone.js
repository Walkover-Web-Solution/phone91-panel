    if (document.getElementById('js_not_enabled') != null)  document.getElementById('js_not_enabled').style.display = 'none';

		wp_api.attributes = { id: 'webphone', code: 'webphone.webphone.class', name: 'webphone', archive: 'webphone.jar', codebase: '.', width: 1, height: 1, alt: 'Enable or install java: http://java.com/en/download/index.jsp', MAYSCRIPT: true };

    				wp_api.parameters['JAVA_CODEBASE'] = '.';
					wp_api.parameters['MAYSCRIPT'] = true;
					wp_api.parameters['mayscript'] = 'yes';
					wp_api.parameters['scriptable'] = true;
					wp_api.parameters['jsscriptevent'] = 2;
					wp_api.parameters['autocfgsave'] = 4;
					wp_api.parameters['permissions'] = 'all-permissions';
//					wp_api.parameters['loglevel'] = 4;
					wp_api.parameters['hasincomingcall'] = true;
					//wp_api.parameters['classloader_cache'] = false;


document.write('<script src="js/jquery-1.8.3.min.js"></script>'+
'<script type="text/JavaScript" src="js/curvycorners.src.js"></script>'+
'<script type="text/JavaScript" src="js/wp_common.js"></script>'+
'<script type="text/javascript">var curvyCornersNoAutoScan = true;</script>'+

'<style type="text/css">'+
'#btn_callhangup{'+
'	height:37px; padding:0px; margin:0px; display:inline-block; cursor:pointer; float:left; text-align:center; '+
'	font-family: Arial, Verdana, Helvetica, sans-serif; font-size:16px; font-weight:bold;'+

'   border:2px solid;'+
'	border-radius: 5px;'+
'	-webkit-border-radius: 5px;'+
'	-moz-border-radius: 5px;'+
'}'+
'#btn_callhangup_inner{'+
'	height:33px; padding:0px; margin:0px; display:inline-block; cursor:pointer; text-align:center; '+
'}'+
'#info_event{'+
'	height:14px; width:100%; clear:both; display:inline-block; padding:0px; margin:0px; font-size:11px; text-align:center;'+
'}'+
'SPAN#status{'+
'	height:14px; font-size:11px; color:#333333; font-weight:bold; float:left; text-align:center; width:100%; display:inline-block; overflow:hidden;'+
'}'+
'SPAN#button_title{'+
'   float:left; clear:both; text-align:center; width:100%;'+
'}'+
'</style>'+
'<div id="btn_callhangup" onclick="wp_common.wp_RegisterCallHangup();">'+
'    <div id="btn_callhangup_inner">'+
'        <div id="info_event">'+
'            <span id="status" title="Call status"></span>'+
'        </div>'+
'        <span id="button_title"></span>'+
'    </div>'+
'</div>'+
'<span id="testtest" style="float:left; text-align:left;"></span>'+
'<script type="text/JavaScript" src="js/wp_layoutc2c.js"></script>');
