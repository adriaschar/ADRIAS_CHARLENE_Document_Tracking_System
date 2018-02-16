<?php

include('config.php');
include('session.php');

if (!function_exists('getDB')) {
  include('DB.php');
}

function getFileStatus($fileId) {
    try {
      $db = getDB();
      $file = $db->prepare("SELECT `history_status` FROM `fts_history` WHERE history_reference_id = :history_reference_id ORDER BY `history_date_time` DESC LIMIT 1");
      $file->bindParam(":history_reference_id", $fileId);
      $file->execute();
      $data = $file->fetch(PDO::FETCH_OBJ);
      return $data->history_status;
    } catch(PDOException $e) {
      echo $e->getMessage();
    }
}

if(!$_GET["reference_id"])
{
    echo 'Invalid File.'; die();
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title>View File - <?php echo $_GET["reference_id"]; ?></title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="src/dashboard.css" rel="stylesheet">
    <link href="src/main.css" rel="stylesheet">

    <!-- HTML5 shim and Respond.js for IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
      <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
      <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
  </head>
  <body>

    <?php include 'navbar.php' ?>

    <div class="container-fluid">
      <div class="row">
        <nav class="col-md-2 d-none d-md-block bg-light sidebar">
          <div class="sidebar-sticky">
            <?php include 'menu.php' ?>
          </div>
        </nav>

        <main role="main" class="col-md-9 ml-sm-auto col-lg-10 pt-3 px-4">
          <div class="">
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
                  <h1 class="h2">View File
               <?php $checkfileStatus = strtolower(getFileStatus($_GET["reference_id"])); ?>
               <?php if($checkfileStatus == 'open'): ?>
                   <button class="btn btn-default" id="btn_edit_frm_update_file">Edit</button>
               <?php endif; ?>
               </h1>
               <button class="btn <?php echo ($checkfileStatus != 'open') ? 'btn-danger' :'btn-success' ?> disabled float-right">Status: <?php echo $checkfileStatus; ?></button>
              </div>
            <?php include 'email-file-content.php' ?>
          </div>
        </main>
      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script src="src/jquery.popupoverlay.js"></script>
    <script src="js/bootstrap.min.js"></script>


    <script>
        $(document).ready(function() {

            $('#btn_edit_frm_update_file').on('click', function() {
                if ($(this).text() == 'Cancel') {
                    $(this).text('Edit');

                    // hide control buttons at the top
                    $('#frm_btns_control').hide();
                    return;
                }

                var $fields = $('#frm_update_file').find('input[type="text"], textarea, select');

                $(this).text('Cancel');

                // enalble fields
                $fields.each(function() {
                    $(this).removeAttr('readonly');
                    $(this).removeAttr('disabled');
                });

                // show attachement btn
                $('#frm_attachment_download').hide();
                $('#frm_attachment').show();

                // show control btns
                $('#frm_btns_control').show();
            });

          // Initialize the plugin
          $('#forward_popup').popup({
            onclose: function() {
              $('#fileAction').val('save')
              $('#recepientId').val('')
            },
            onopen: function() {
              $('#fileAction').val('forward')
            }
          });

          $('#btnForward_popup').click(function(e){
            e.preventDefault();
            $('#forward_popup').popup('show');
            $('#recepientIdList').trigger('change');
          })


          $('#forward_popup_close').click(function(e){
            e.preventDefault();
            $('#forward_popup').popup('hide');
          })

          $('#forward_popup_send').click(function(e){
            e.preventDefault();
            $('#frm_update_file').submit();
          })

          $('#recepientIdList').change(function() {
            $('#recepientId').val($('#recepientIdList').val());
          })

          // close file
          $('#btn_mark_closed').on('click', function() {
             $('#file_status').val('closed');
             $('#frm_update_file').submit();
          });

          // cancel file
          $('#btn_mark_cancelled').on('click', function() {
             $('#file_status').val('cancelled');
             $('#frm_update_file').submit();
          });
        });
      </script>
  </body>
</html>
