<!-- BottomFooter -->
    <section id="bottomFooter">
         <section class="innerFooter">         
          	<aside class="boxBottom openRegu">
             <h6 class="bluShade">Contact us</h6>             
             <ul>
               <li><strong> Email :</strong> <?php echo _CONTACT_EMAIL_; ?></li>
               <li><strong> Phone :</strong>  <?php echo _CONTACT_PHONE_NO_; ?> </li>
<!--               <li><strong> MSN :</strong> diva@hostnaoft.com</li>
               <li><strong> Gtalk :</strong> diva@hostnaoft.com</li>
              <li><strong> Skype:</strong> diva12w</li>-->
              
             </ul>
            </aside>
            <aside class="box">
		        <h6 class="bluShade">Download Dialers</h6>
		        <ul class="supported">
		          <li>
                        <a href="../gtalkSetup/googletalk-setup.rar" target="_blank">
                                <img border="0" width="60" height="28" title="" alt="" src="/images/talk.png">
                        </a>
                  </li>
		          <li>
                  	<a href="http://www.skype.com/en/download-skype" target="_blank">
                    	<img border="0" width="82" height="28" title="" alt="" src="/images/skype.png">
                    </a>
                    </li>
		          <li class="last">
                		<a href="http://www.vtokapp.com/" target="_blank">
                  		<img title="" alt="" src="/images/vtolk.png">
                      </a>
                   </li>		       
                </ul>
      		</aside>                     
            <aside class="feedback">
		        <!-- <div class="fb-like-box" data-href="http://www.facebook.com/phone91" data-width="292" data-show-faces="true" data-stream="true" data-header="true"> </div> -->
                <h6 class="bluShade">Feedback / Query</h6>
                 <form name="Feedback" method="" action="" id="feedBackForm">
                        <input type="text" id="user_email" name="email" placeholder="Your Email"/>
                        <div id="error"></div>
                        <input type="text" name="Number" id="user_number" placeholder="Your Username/Number"/>
                        <div id="error"></div>
                      <textarea onfocus="if(this.value==this.defaultValue)this.value='';" onblur="if(this.value=='')this.value=this.defaultValue;" id="msg" cols="10" rows="5" placeholder="Your Feedback"></textarea>
                      <div id="error"></div>
                      <input type="button" title="Submit" value="Submit" id="sbmitFeedback" onclick="feedback_submit(<?php echo $_SESSION['captcha'];?>)"/>
		       		<div id="error"></div>
                 </form>
            </aside>
         </section>        
    </section>    
   <!-- //BottomFooter -->

    <!-- Footer -->
    <footer id="footer">
         <div class="openRegu mar0auto f12">&copy; 2015 Phone91.com All Rights Reserved.</div>       
    </footer>    .
    
    <script type="text/javascript">
var __lc = {};
__lc.license = 3096252;
__lc.group = 2;

(function() {
	var lc = document.createElement('script'); lc.type = 'text/javascript'; lc.async = true;
	lc.src = ('https:' == document.location.protocol ? 'https://' : 'http://') + 'cdn.livechatinc.com/tracking.js';
	var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(lc, s);
})();
</script> 
   <!-- //Footer -->
