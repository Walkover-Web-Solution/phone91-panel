<?phpinclude_once(_MANAGE_PATH_."contact.php");?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Transitional//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-transitional.dtd"><html xmlns="http://www.w3.org/1999/xhtml"><head><meta https-equiv="Content-Type" content="text/html; charset=utf-8" /><meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no"><meta name="keywords" content="<?php echo $contactMeta_mKeyword;?>" /><meta name="description" content="<?php echo $contactMeta_mDescription;?>" /><title><?php echo $contactMeta_title;?></title><?php include_once(_THEME_PATH_.'/inc/head.php'); ?></head><body><?php include_once(_THEME_PATH_.'/inc/header.php'); ?><div id="banner-wrap" class="pr">    <!--banner conditional code-->    <div id="all-banner" class="banner" style="background-image: url('<?php echo _MANAGE_PATH_.'/'.$welcomeImage;?>')">    <!--[if !IE]>    <svg xmlns="http://www.w3.org/2000/svg" id="svgroot" viewBox="" width="100%" height="450">    <defs>     <filter id="filtersPicture">       <feComposite result="inputTo_38" in="SourceGraphic" in2="SourceGraphic" operator="arithmetic" k1="0" k2="1" k3="0" k4="0" />       <feColorMatrix id="filter_38" type="saturate" values="0" data-filterid="38" />    </filter>    </defs>    <image filter="url(&quot;#filtersPicture&quot;)" x="0" y="0" width="100%" height="450px" xmlns:xlink="http://www.w3.org/1999/xlink" xlink:href="http://localhost/msg91-panel-template/images/banner-1.jpg" />    </svg>    <![endif]-->    <!--banner conditional code end here-->    <div id="all-banner-wrap"  class="home-banner-wrap">        <div class="wrapper alC clear">            <h1><?php echo $contactbannerDetail_heading; ?></h1>            <h2 class="ligt"><?php echo $contactbannerDetail_subHeading ?></h2>            <a class="btn orange alC bgTrns mrR3" href="javascript:void(0);">Login</a>            <a class="btn blue alC bgTrns" href="<?php echo $contactbannerDetail_link;?>"><?php echo $contactbannerDetail_text?></a>        </div>    </div>    </div></div><!--//Banner--><div id="container" class="wrapper contact">	<h3 class="clear  pr"><p><span></span></p>Contact</h3>    <div class="clear">    	<div class="col-2"><?php if($gMapEmbededCode != "") { ?>        	<div class="pdR3" style="height:430px">            <iframe width="100%" height="100%" frameborder="0" scrolling="no" marginheight="0" marginwidth="0" src="<?php echo $gMapEmbededCode; ?>">            </iframe>            </div> <?php } ?>        </div><!--//Col2-->        <div class="col-2">        	<div class="pdL3">            	<div id="form">                    <h4>Feel free to Drop us a Line</h4>                                        <form>                        <input type="text" placeholder="Name (Required)"/>                        <input type="email" placeholder="Email (Required)"/>                        <input type="text" placeholder="Subject"/>                        <textarea placeholder="Message"></textarea>                        <input class="blue btn  bgTrns" id="submit" type="button" value="Submit"/>                    </form>                </div>            </div>        </div><!--//Col2-->	</div><!--//Clear div--></div><!--//Container--><?php include_once(_THEME_PATH_.'/inc/footer.php'); ?></body></html>