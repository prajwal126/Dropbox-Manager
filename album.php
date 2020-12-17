<?php

// display all errors on the browser
error_reporting(E_ALL);
ini_set('display_errors','On');
require_once 'demo-lib.php';
demo_init(); // this just enables nicer output

// if there are many files in your Dropbox it can take some time, so disable the max. execution time
set_time_limit( 0 );

require_once 'DropboxClient.php';

/** you have to create an app at @see https://www.dropbox.com/developers/apps and enter details below: */
/** @noinspection SpellCheckingInspection */
$dropbox = new DropboxClient( array(
	'app_key' => "sn85r0zox6cpt0y",      // Put your Dropbox API key here
	'app_secret' => "xfuf8g0lmnpc007",   // Put your Dropbox API secret here
	'app_full_access' => false,
) );

$return_url = "https://" . $_SERVER['HTTP_HOST'] . $_SERVER['SCRIPT_NAME'] . "?auth_redirect=1";

// first, try to load existing access token
$bearer_token = demo_token_load( "bearer" );

if ( $bearer_token ) {
	$dropbox->SetBearerToken( $bearer_token );
} elseif ( ! empty( $_GET['auth_redirect'] ) ) // are we coming from dropbox's auth page?
{
	// get & store bearer token
	$bearer_token = $dropbox->GetBearerToken( null, $return_url );
	demo_store_token( $bearer_token, "bearer" );
} elseif ( ! $dropbox->IsAuthorized() ) {
	// redirect user to Dropbox auth page
	$auth_url = $dropbox->BuildAuthorizeUrl( $return_url );
	die( "Authentication required. <a href='$auth_url'>Continue.</a>" );
}

?>

<?php
if(isset($_POST["submit"])) {
    $check = $_FILES["fileToUpload"]["name"];
    $result = $dropbox->UploadFile($_FILES["fileToUpload"]["tmp_name"], $check);
}
echo '<center>';
?>
<!DOCTYPE html>
<html>
<head>
<style>
.button {
    display: block;
    width: 100px;
    height: 15px;
    background: #4E9CAF;
    padding: 6px;
    text-align: center;
    border-radius: 5px;
    color: white;
    font-weight: bold;
    line-height: 15px;
    text-decoration:none;
    margin-top:10px;
}
.h2{
	color: DodgerBlue;
}

.new4 {
  border: 1px solid darkblue;
}
.body{
	background:lightblue;
}
</style>
</head>
<body class='body'>
<h2 class='h2'> Dropbox Photo Album </h2>
<form action="" method="post" enctype="multipart/form-data">
  <h3>Select Image to upload:</h3>
  <input type="file" accept=".jpg" name="fileToUpload" id="fileToUpload">
  <input type="submit" value="Upload Image" name="submit">
</form>
<hr class="new4"/>
</body>
</html>
<?php
echo "\n\n<h3>List of JPG files:</h3>\n";
$jpg_files = $dropbox->Search( "/", ".jpg" );
if ( empty( $jpg_files ) ) {
	echo "Nothing found.";
} else {
	echo'<ol style="list-style: decimal inside none;color:DodgerBlue">';
	for($i=0;$i<sizeof($jpg_files);$i++){
		$jpg_file = $jpg_files[$i];
		echo "<b><li>".$jpg_file->name."<a class='button' href='?down=".$jpg_file->path."' >Download</a><a class='button' href='?del=".$jpg_file->path ."' >Delete</a></li></b><br>";

	}
	echo'</ol><hr class="new4"/>';
}

echo "\n\n<h3>Image Section</h3>\n";
if(isset($_GET['down'])){
	$path = $_GET['down'];
	echo "\n\n<b>Image of $path:</b>\n";
	$img_data = $dropbox->GetLink( $path );
	$img_data=str_replace("dl=0","raw=1",$img_data);
	echo "<img src=\"$img_data\" alt=\"Generating PDF thumbnail failed!\" style=\"border: 1px solid black;\" />";
}

if(isset($_GET['del'])){
	$path = $_GET['del'];
	$dropbox->Delete( $path );
	echo("<script>location.href = 'album.php';</script>");
}
echo '</center>';
?>