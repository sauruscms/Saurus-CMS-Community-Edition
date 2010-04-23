<?
# This is a sample admin-page file for the extension "sample" that ships with Saurus CMS installation.


# Set relative path to the website root (depending on where your admin-page script is located)
$webroot_path = '../../../'; # if admin-page is located in folder /extensions/sample/admin/

$class_path = $webroot_path.'classes/';

# File "check_adminpage.php" must be included at the top of the custom admin-page,
# if access is not granted to the current user then page load will be terminated and login-page is displayed.
include($webroot_path."admin/check_adminpage.php");

?><!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
   "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html>
	<head> 	
		<title><?=$site->title;?> <?= $site->cms_version;?></title>
		<meta http-equiv="Content-Type" content="text/html; charset=<?=$site->encoding;?>">
		<link rel="stylesheet" href="../../../styles/default/default_admin_page.css" media="screen">
		<script type="text/javascript" src="../../../js/admin_menu.js"></script>
		<script type="text/javascript" src="../../../js/ie_position_fix.js"></script>
		<script type="text/javascript">
			window.onload = function()
			{
				make_breadcrumb('Extensions', 'Sample Admin Page');
				new ContentBox('some_content_area_1', '40px', '0px', '50%', '0px');
				new ContentBox('some_content_area_2', '50%', '0px', '15px', '0px');
			}
			
			var ContentBox = function(id, top, right, bottom, left)
			{
				this.box = document.getElementById(id);
				this.box.style.top = top;
				this.box.style.right = right;
				this.box.style.bottom = bottom;
				this.box.style.left = left;
			}
		</script>
		<style type="text/css">
			input {
				height: 16px !important;
			}		
		</style>
	</head>
	<body>
		<div id="mainContainer">
		
			<div class="toolbarArea">
        		<form name="some_form" id="some_form" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
	    			<table cellpadding="0" cellspacing="0" width="100%">
	    				<tr>
	    					<td>
			            		<ul class="s_Buttons_container">
			            			<li><a href="#" id="button_new" class="button_new">New</a></li>
			            			<li><a href="#" id="button_edit" class="button_edit">Edit</a></li>
			            			<li><a href="#" id="button_save" class="button_save">Save</a></li>
			            			<li><a href="#" id="button_delete" class="button_delete">Delete</a></li>
			            		</ul>
	    					</td>
	    				</tr>
	    			</table>
            	</form><!-- /form filters -->
			</div><!-- / toolbarArea -->
			
			<div class="contentArea" id="some_content_area_1">
				<div class="contentAreaTitle">
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>Sample admin Page</td>
						</tr>
					</table>
				</div><!-- / contentAreaTitle -->
				<div class="contentAreaContent withTitleBar">
					Admin page's content
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			
			<div class="contentArea" id="some_content_area_2">
				<div class="contentAreaTitle">
					<table cellpadding="0" cellspacing="0" width="100%">
						<tr>
							<td>Sample area with it's own toolbar</td>
						</tr>
					</table>
				</div><!-- / contentAreaTitle -->
				
				<div class="toolbarArea">
	        		<form name="some_form_2" id="some_form_2" method="POST" action="<?=$_SERVER['PHP_SELF'];?>">
		    			<table cellpadding="0" cellspacing="0" width="100%">
		    				<tr>
		    					<td>
				            		<ul class="s_Buttons_container">
				            			<li><a href="#" id="button_new" class="button_new">New</a></li>
				            			<li><a href="#" id="button_edit" class="button_edit">Edit</a></li>
				            			<li><a href="#" id="button_save" class="button_save">Save</a></li>
				            			<li><a href="#" id="button_delete" class="button_delete">Delete</a></li>
				            		</ul>
		    					</td>
		    				</tr>
		    			</table>
	            	</form><!-- /form filters -->
				</div><!-- / toolbarArea -->
				
				<div class="contentAreaContent withTitleAndToolBar">
					Admin page's content
				</div><!-- / contentAreaContent -->
			</div><!-- / contentArea -->
			
		</div><!-- / mainContainer -->
	</body>
</html>