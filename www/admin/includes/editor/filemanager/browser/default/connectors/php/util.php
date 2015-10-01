<?php
function RemoveFromStart( $sourceString, $charToRemove )
{
	$sPattern = '|^' . $charToRemove . '+|' ;
	return preg_replace( $sPattern, '', $sourceString ) ;
}

function RemoveFromEnd( $sourceString, $charToRemove )
{
	$sPattern = '|' . $charToRemove . '+$|' ;
	return preg_replace( $sPattern, '', $sourceString ) ;
}

function ConvertToXmlAttribute( $value )
{
#	return utf8_encode( htmlspecialchars( $value ) ) ;
	return unicode_russian( htmlspecialchars( $value) ) ;
}

 function unicode_russian($str) {
     $encode = "";
//    1025 = "&#1025;";
//    1105 = "&#1105;";

     for ($ii=0;$ii<strlen($str);$ii++) {
         $xchr=substr($str,$ii,1);
         if (ord($xchr)>191) {
             $xchr=ord($xchr)+848;
             $xchr="&#" . $xchr . ";";
         }
         if(ord($xchr) == 168) {
//            $xchr = "&#1025";
               $xchr = "&#1025;"; //!!!!!!!!!!!!!!!!!!!!!!!
         }
         if(ord($xchr) == 184) {
//            $xchr = "&#1105";
               $xchr = "&#1105;"; //!!!!!!!!!!!!!!!!!!!!!!
         }
         $encode=$encode . $xchr;
   }
     return $encode;
}
?>