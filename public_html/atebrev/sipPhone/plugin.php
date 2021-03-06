<html>
<head>
    <style type="text/css">
    </style>

	<script type="text/javascript" src="utils.js"></script>
    <script type="text/JavaScript">
    
    	/* return the core object */
        function getCore(){
            return document.getElementById('core');
        }
        
        // Remove the core object
       	function unload(){
			var core = getCore();
			delete core;
        }
        
        // Reload the test
       	function reload(){
       		unload();
       		window.location.reload();
		}
        
        /* Main function */
        function load(){
			var config = getConfig();
			var browserDetection;
			var core;
			
			navigator.plugins.refresh(false);
			
			/* Detection of the system information : OS/Architecture/Browser */
			browserDetection = browserDetect();
			updateStatus('browser',"OS : " + browserDetection.os + " / Browser : " + browserDetection.browser );
			// Find the correct plugin file
			setPluginLink(config,browserDetection);
                        core = getCore();
console.log(core);
			// Detection of the plugin
			var ret = detect(config,core);
                        
			if(ret === 0){ // The plugin is not installed or outdated
				updateStatus('plugin',"Plugin : Not installed or outdated");
				// Donwload the plugin
                                console.log(config.file.description);  
                                //return false;
                                window.open(config.file.description, '_self');	
			} else { // The plugin is installed
				updateStatus('plugin',"Plugin : Installed");
				loadCore();
                                window.open("call.php", '_self');
			}
        }
        
		function loadCore(){
			var core = getCore();
			/* init the linphoneCore object */
			core.init();
            
            /* Display logs information in the console */
            core.logHandler = function(level, message) {
            	window.console.log(message);
            }
            /* Start main loop for receiving notifications and doing background linphonecore work */
            core.iterateEnabled = true;
		}
    </script>
</head>
<body onload="load()">
	<object id="core" type="application/x-linphone-web" width="0" height="0">
	 	<param name="onload" value='loadCore'>
	</object>
	<p id="browser"></p>
	<p id="plugin">Detection of the plugin ...</p>
	<input type="button" OnClick="reload()" value="Reload">
</html>