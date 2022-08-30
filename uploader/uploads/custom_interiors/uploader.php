<?php
if(isset($_FILES['file'])){
    $errors= array();
    $file_name = $_FILES['file']['name'];
    $file_size =$_FILES['file']['size'];
    $file_tmp =$_FILES['file']['tmp_name'];
    $file_type=$_FILES['file']['type'];   			
    if(empty($errors)==true){
        move_uploaded_file($file_tmp,$file_name);
        echo "Success";
    }else{
        print_r($errors);
    }
}
?>
<form action="" method="POST" enctype="multipart/form-data">
    <input type="file" name="file" />
    <input type="submit"/>
</form>