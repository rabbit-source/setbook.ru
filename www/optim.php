<?php
	require('includes/application_top.php');
?>

<html>
</html>
<head>
<title>OPtimization panel</title>
</head>
<body bgcolor="#ffffff">

<table border="1">
<tr bgcolor="#9acd32">
  <td><b>Request count:</b></td>
  <td><b>Total count:</b></td>
</tr>
<tr>
<?php
    global $queries_count;

	if ($_SERVER['REMOTE_ADDR']=='213.138.80.141') echo 'Yes';
	echo '<td>'.$queries_count.'</td>';
	echo '<td>'.$_SERVER['REMOTE_ADDR'].'</td>';
?>
</tr>
</table>
</body>