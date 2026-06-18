<?
	$id = "xml";
	$enlace = $_GET['xml'];
	header ("Content-Disposition: attachment; filename=".$enlace." ");
	header ("Content-Type: application/octet-stream");
	header ("Content-Length: ".filesize($enlace));
	readfile($enlace);

?>