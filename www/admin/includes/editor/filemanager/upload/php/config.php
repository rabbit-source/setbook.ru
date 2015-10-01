<?php 
global $Config ;

include('../../../../../../includes/configure.php');

// SECURITY: You must explicitelly enable this "uploader". 
$Config['Enabled'] = true ;

// Path to uploaded files relative to the document root.
$Config['UserFilesPath'] = DIR_WS_CATALOG . 'images/';

$Config['AllowedExtensions']['File']	= array() ;
$Config['DeniedExtensions']['File']		= array('php','php3','php4','php5','phtml','asp','aspx','ascx','jsp','cfm','cfc','pl','bat','exe','dll','reg','cgi') ;

$Config['AllowedExtensions']['Image']	= array('jpg','gif','jpeg','png') ;
$Config['DeniedExtensions']['Image']	= array() ;

$Config['AllowedExtensions']['Flash']	= array('swf','fla') ;
$Config['DeniedExtensions']['Flash']	= array() ;

?>