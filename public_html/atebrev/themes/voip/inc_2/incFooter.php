<!-- BottomFooter -->
    <section id="bottomFooter">
         <section class="innerFooter">         
          	<aside class="boxBottom openRegu">
             <h6 class="bluShade">About us</h6>             
             <ul>
            	<li><a href="/voipcall/about-phone91.php?a=1">What Phone91 Is?</a></li>
               <li><a href="/voipcall/terms.php">Terms and conditions</a></li>
               <li><a href="/voipcall/privacy.php">Privacy Policy</a></li>
               <li><a href="/voipcall/contactus.php">Contact US</a></li>
               <li><a href="/voipcall/voip-faq.php">FAQs</a></li>
               <li><a href="/voipcall/freevoipcalls.php">Affiliates</a></li>
               <li><a href="/voipcall/voip-resellers.php">Associates</a></li>
               <li><a href="/voipcall/pay-phone91.php">Ways to Pay</a></li>
             </ul>
            </aside>
            <aside class="box">
		        <h6 class="bluShade">Download Dialers</h6>
		        <ul class="supported">
		          <li>
                        <a href="http://www.google.co.in/talk/" target="_blank">
                                <img border="0" width="60" height="28" title="" alt="" src="/images/talk.png">
                        </a>
                  </li>
		          <li>
                  	<a href="http://www.nimbuzz.com/en/get/voip-and-chat-on-pc" target="_blank">
                    	<img border="0" width="82" height="25" title="" alt="" src="/images/nimbuzz.png">
                    </a>
                    </li>
		          <li class="last">
                		<a href="http://www.vtokapp.com/" target="_blank">
                  		<img width="57" border="0" height="37" title="" alt="" src="/images/vtolk.png">
                      </a>
                   </li>		       
                </ul>
      		</aside>                     
            <aside class="feedback">
		        <!-- <div class="fb-like-box" data-href="http://www.facebook.com/phone91" data-width="292" data-show-faces="true" data-stream="true" data-header="true"> </div> -->
                <h6 class="bluShade">Feedback</h6>
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
         <div class="openRegu mar0auto f12">&copy; 2013 Phone91.com All Rights Reserved.</div>       
    </footer>    
   <!-- //Footer -->
