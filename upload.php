<?php
    require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
    use google\appengine\api\cloud_storage\CloudStorageTools;

    $app_host = array_pop(@explode("~", $_SERVER["APPLICATION_ID"])) . ".appspot.com";
    $options = [ 'gs_bucket_name' => $app_host ];
    $upload_url = CloudStorageTools::createUploadUrl('/upload.php', $options);

if(count($_FILES)) {
    $gs_name = $_FILES['uploaded_files']['tmp_name'];
    move_uploaded_file($gs_name, 'gs://' . $app_host  .'/' . $_FILES['uploaded_files']['name']);
    header("Location: /edit.html?img=" . $_FILES['uploaded_files']['name']);
    
}
?>
<form action="<?php echo $upload_url?>" enctype="multipart/form-data" method="post" style="padding:1em;">
    Images to upload: <br>
   <input type="file" name="uploaded_files" size="40" accept="image/*"> 
   <input type="submit" value="Upload" class="btn btn-primary">
</form>
