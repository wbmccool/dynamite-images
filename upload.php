<?php
    require_once 'google/appengine/api/cloud_storage/CloudStorageTools.php';
    use google\appengine\api\cloud_storage\CloudStorageTools;

    $app_host = array_pop(@explode("~", $_SERVER["APPLICATION_ID"])) . ".appspot.com";
    $options = [ 'gs_bucket_name' => $app_host ];
    $upload_url = CloudStorageTools::createUploadUrl('/upload.php', $options);

if(count($_FILES)){
    $gs_name = $_FILES['uploaded_files']['tmp_name'];
    move_uploaded_file($gs_name, 'gs://' . $app_host  .'/' . $_FILES['uploaded_files']['name']);
    header("Location: /edit.html?img=" . $_FILES['uploaded_files']['name']);

}
?>
<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <title>Upload an Image</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="description" content="">
    <meta name="author" content="">
    <link href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.2/css/bootstrap.min.css" rel="stylesheet">
    <link href="/css/styles.css" rel="stylesheet">
  <body>
  <div id="main" class="container container-fluid">
      <div class="row">
        <div id="upload-form-col" class="col-md-6">
            <h1 class="small-h1">Blue State Digital Dynamic Images</h1>
            <h2>Chose a file to upload</h2>
            <form action="<?php echo $upload_url?>" enctype="multipart/form-data" method="post" style="padding:1em;">
                <div class="form-group overflow-hidden">
                    <div class="input-group">
                       <input type="file" name="uploaded_files" size="40" accept="image/*">
                       <input type="submit" value="Upload" class="btn btn-primary">
                   </div>
                </div>
            </form>
        </div>
    </div>
    </div>
  </body>
</html>