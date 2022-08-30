<?php

session_start();
if (isset($_SESSION['timeout']) and $_SESSION['timeout'] + 10 * 60 < time()) {
    session_destroy();
} else {
    $_SESSION['timeout'] = time();
}

if (!isset($_SESSION['userid']) or ! $_SESSION['userid']) {
    echo "Session has timed out.";
} else {
    $error = ""; //error holder
    if (isset($_GET['intid']) and $_GET['userid'] == $_SESSION['userid']) {
        if (extension_loaded('zip')) {
            $zip = new ZipArchive(); // Load zip library 
            $zip_name = time() . ".zip"; // Zip name
            if ($zip->open($zip_name, ZIPARCHIVE::CREATE) !== TRUE) {
                // Opening zip file to load files
                $error .= "* Sorry ZIP creation failed at this time<br>";
            }
            $zip->addFile('./uploads/custom_interiors/'.$_GET['intid'].'.map'); // Adding files into zip
            $zip->close();
            if (file_exists($zip_name)) {
                // push to download the zip
                header('Content-type: application/zip');
                header('Content-Disposition: attachment; filename="' . $zip_name . '"');
                readfile($zip_name);
                // remove zip file is exists in temp path
                unlink($zip_name);
            }
        } else {
            $error .= "* Error: Server doesn't have ZIP extension<br>";
        }
    } else {
        $error .= '* Access denied!<br>';
    }
    echo $error;
}