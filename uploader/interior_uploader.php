<!DOCTYPE HTML>
<html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>OwlGaming File Uploader</title>
        <script type="text/javascript" src="../uploader/js/jquery-1.10.2.min.js"></script>
        <script type="text/javascript" src="../uploader/js/jquery.form.min.js"></script>

        <script type="text/javascript">
            $(document).ready(function() {
                var options = {
                    target: '#output', // target element(s) to be updated with server response 
                    beforeSubmit: beforeSubmit, // pre-submit callback 
                    success: afterSuccess, // post-submit callback 
                    uploadProgress: OnProgress, //upload progress callback 
                    resetForm: true        // reset the form after successful submit 
                };

                $('#MyUploadForm').submit(function() {
                    $(this).ajaxSubmit(options);
                    // always return false to prevent standard browser submit and page navigation 
                    return false;
                });
                $("#process_btn").hide();

        //function after succesful file upload (when server response)
                function afterSuccess()
                {
                    $('#submit-btn').show(); //hide submit button
                    $('#submit-btn').val('Re-upload');
                    $('#process_btn').show();
                    $('#loading-img').hide(); //hide submit button
                    $('#progressbox').delay(1000).fadeOut(); //hide progress bar

                }

        //function to check file size before uploading.
                function beforeSubmit() {
                    //check whether browser fully supports all File API
                    if (window.File && window.FileReader && window.FileList && window.Blob)
                    {

                        if (!$('#FileInput').val()) //check empty input filed
                        {
                            $("#output").html("Please select a file first!");
                            return false
                        }

                        var fsize = $('#FileInput')[0].files[0].size; //get file size
                        var ftype = $('#FileInput')[0].files[0].type; // get file type


                        //allow file types 
                        /*switch (ftype)
                        {
                            case 'image/png':
                            case 'image/gif':
                            case 'image/jpeg':
                            case 'image/pjpeg':
                            case 'text/plain':
                            case 'text/html':
                            case 'application/x-zip-compressed':
                            case 'application/pdf':
                            case 'application/msword':
                            case 'application/vnd.ms-excel':
                            case 'video/mp4':
                                break;
                            default:
                                $("#output").html("<b>" + ftype + "</b> Unsupported file type!");
                                return false
                        }*/

                        //Allowed file size is less than 5 MB (1048576)
                        if (fsize > 100000)
                        {
                            $("#output").html("<b>" + bytesToSize(fsize) + "</b> Too big file! <br />File is too big, it should be less than 100kB");
                            return false
                        }

                        $('#submit-btn').hide(); //hide submit button
                        $('#loading-img').show(); //hide submit button
                        $("#output").html("");
                        $("#process_btn").hide();
                    }
                    else
                    {
                        //Output error to older unsupported browsers that doesn't support HTML5 File API
                        $("#output").html("Please upgrade your browser, because your current browser lacks some new features we need!");
                        return false;
                    }
                }

        //progress bar function
                function OnProgress(event, position, total, percentComplete)
                {
                    //Progress bar
                    $("#process_btn").hide();
                    $('#progressbox').show();
                    $('#progressbar').width(percentComplete + '%') //update progressbar percent complete
                    $('#statustxt').html(percentComplete + '%'); //update status text
                    if (percentComplete > 50)
                    {
                        $('#statustxt').css('color', '#000'); //change status text to white after 50%
                    }
                }

        //function to format bites bit.ly/19yoIPO
                function bytesToSize(bytes) {
                    var sizes = ['Bytes', 'KB', 'MB', 'GB', 'TB'];
                    if (bytes == 0)
                        return '0 Bytes';
                    var i = parseInt(Math.floor(Math.log(bytes) / Math.log(1024)));
                    return Math.round(bytes / Math.pow(1024, i), 2) + ' ' + sizes[i];
                }

            });

        </script>
        <link href="../uploader/style/style.css" rel="stylesheet" type="text/css">
    </head>
    <body>
        <div id="upload-wrapper">
            <div align="center">
                <h3>Interior Uploader</h3>
                <form action="../uploader/interior_uploader_processor.php" method="post" enctype="multipart/form-data" id="MyUploadForm">
                    <input type="hidden" name="interiorid" value="<?php echo $intid; ?>"/>
                    <input name="FileInput" id="FileInput" type="file" accept=".map"/>
                    <input type="submit"  id="submit-btn" value="Upload" /><input type="button" id="process_btn" value="Process Interior" onClick="return ajax_start_process_int(<?php echo $intid; ?>, <?php echo $charid; ?>);"/>
                    <img src="../uploader/images/ajax-loader.gif" id="loading-img" style="display:none;" alt="Please Wait"/>
                </form>
                <div id="progressbox" ><div id="progressbar"></div ><div id="statustxt">0%</div></div>
                <div id="output"></div>
            </div>
        </div>

    </body>
</html>