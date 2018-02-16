<?php

include('config.php');
include('session.php');

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php echo SITE_NAME; ?> - New File</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="src/dashboard.css" rel="stylesheet">

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
              <h1 class="h2">New File</h1>
              </div>
            <?php include 'new-file-content.php' ?>
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
            $('#fileFormId').submit();
          })

          $('#recepientIdList').change(function() {
            $('#recepientId').val($('#recepientIdList').val());
          })
        });
      </script>
  </body>
</html>
