<?php

include('config.php');
include('session.php');
include('userClass.php');

if (!function_exists('getDB')) {
  include('DB.php');
}

$userClass = new userClass();

// default cat
$reportsCat = (isset($_GET['cat'])) ? $_GET['cat'] : 'basic-info';


function reportsByDefault() {
    try {
      $db = getDB();
      $query = "SELECT `reference_id`, (SELECT `history_status` FROM `fts_history` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1) as status, `subject`, `details`, `priorityLevel`, CONCAT(FirstName, ' ', LastName) as initiator, (SELECT `name` FROM `fts_department` WHERE `fts_department`.`id` = `department_id`) as department,
      (SELECT CONCAT(FirstName, ' ', LastName) as name FROM `users` JOIN `fts_history` ON `history_from` = `uid` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1) as at_the_desk_of, (SELECT CONCAT(FirstName, ' ', LastName) as name FROM `users` WHERE `uid` = `user_id_approval`) as user_approval
      FROM `file_record`
      LEFT JOIN `users`
      ON `file_record`.`initiator_user_id` = `uid`";
      $reports = $db->prepare($query);
      $reports->execute();
      $data = $reports->fetchAll();

      if ($data) {
        return $data;
      } else {
        return [];
      }
    } catch(PDOException $e) {
      return '{"error":{"text":' . $e->getMessage() . '}}';
    }
}

function reportsByIndicator() {
    try {
      if(isset($_SESSION['uid'])){
        $db = getDB();
        $query = "SELECT `reference_id`, (SELECT `history_status` FROM `fts_history` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1) as status, `subject`, `details`, `priorityLevel`, CONCAT(FirstName, ' ', LastName) as initiator, (SELECT `name` FROM `fts_department` WHERE `fts_department`.`id` = `department_id`) as department,
        (SELECT CONCAT(FirstName, ' ', LastName) as name FROM `users` JOIN `fts_history` ON `history_from` = `uid` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1) as at_the_desk_of, (SELECT CONCAT(FirstName, ' ', LastName) as name FROM `users` WHERE `uid` = `user_id_approval`) as user_approval
        FROM `file_record`
        LEFT JOIN `users`
        ON `file_record`.`initiator_user_id` = `uid` 
        WHERE `file_record`.`initiator_user_id`=:indicator_id";
        $reports = $db->prepare($query);
        $reports->bindParam('indicator_id', $_SESSION['uid']);
        $reports->execute();
        $data = $reports->fetchAll();

        if ($data) {
          return $data;
        } else {
          return [];
        }
      } else {
        return [];
      }
    } catch(PDOException $e) {
      return '{"error":{"text":' . $e->getMessage() . '}}';
    }
}

function reportsByDesk() {
    try {
      if(isset($_SESSION['uid'])){
        $db = getDB();
        $query = "SELECT `reference_id`, (SELECT `history_status` FROM `fts_history` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1) as status, `subject`, `details`, `priorityLevel`, CONCAT(FirstName, ' ', LastName) as initiator, (SELECT `name` FROM `fts_department` WHERE `fts_department`.`id` = `department_id`) as department,
        (SELECT CONCAT(FirstName, ' ', LastName) FROM `users` JOIN `fts_history` ON `history_from` = `uid` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1) as at_the_desk_of, (SELECT CONCAT(FirstName, ' ', LastName) as name FROM `users` WHERE `uid` = `user_id_approval`) as user_approval
        FROM `file_record`
        LEFT JOIN `users`
        ON `file_record`.`initiator_user_id` = `uid` WHERE (SELECT `history_from` FROM `users` JOIN `fts_history` ON `history_from` = `uid` WHERE `history_reference_id` = `reference_id` ORDER BY `history_date_time` DESC LIMIT 1)=:user_id_parameter";
        $reports = $db->prepare($query);
        $reports->bindParam('user_id_parameter', $_SESSION['uid'], PDO::PARAM_STR);
        $reports->execute();
        $data = $reports->fetchAll();

        if ($data) {
          return $data;
        } else {
          return [];
        }
      } else {
        return [];
      }
    } catch(PDOException $e) {
      return '{"error":{"text":' . $e->getMessage() . '}}';
    }
}


/* AJAX check  */
if(!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest') {
    $validCat = ['basic-info', 'initiator', 'desk'];
    $dataObj = [];

    if(!$reportsCat || !in_array($reportsCat, $validCat)) { exit(); }


    if($reportsCat == 'basic-info') {
        $dataObj['data'] = reportsByDefault();
    } else if($reportsCat == 'initiator') {
        $dataObj['data'] = reportsByIndicator();
    } else if($reportsCat == 'desk') {
        $dataObj['data'] = reportsByDesk();
    }

    // data tables config
    // $dataObj['draw'] = 1;
    $dataObj['recordsTotal'] = count($dataObj['data']);
    $dataObj['recordsFiltered'] = count($dataObj['data']);

    header('Content-Type: application/json');
    echo json_encode($dataObj); exit();
}

if($reportsCat == 'basic-info') {
	$tblCols = ['Reference ID', 'Status', 'Subject', 'Details', 'Priorty Level', 'Initiator', 'Department', 'At the desk of', 'User Approval'];
} else if($reportsCat == 'initiator') {
  $tblCols = ['Reference ID', 'Status', 'Subject', 'Details', 'Priorty Level', 'Initiator', 'Department', 'At the desk of', 'User Approval'];
} else if($reportsCat == 'desk') {
  $tblCols = ['Reference ID', 'Status', 'Subject', 'Details', 'Priorty Level', 'Initiator', 'Department', 'At the desk of', 'User Approval'];
}

?>

<!DOCTYPE html>
<html lang="en">
  <head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <!-- The above 3 meta tags *must* come first in the head; any other head content must come *after* these tags -->
    <title><?php echo SITE_NAME; ?> - Reports</title>

    <!-- Bootstrap -->
    <link href="css/bootstrap.min.css" rel="stylesheet">
    <link href="src/dashboard.css" rel="stylesheet">

    <link rel="stylesheet" type="text/css" href="vendor/DataTables/datatables.min.css"/>

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
          <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pb-2 mb-3 border-bottom">
            <h1 class="h2">Reports</h1>
          </div>
          <table class="table" id="tbl_reports">
            <thead>
              <tr>
            <?php foreach($tblCols as $col): ?>
              <th scope="col"><?php echo $col; ?></th>
            <?php endforeach; ?>
              </tr>
            </thead>
            <tbody>
              <tr>
                <th>&nbsp;</th>
              </tr>
            </tbody>
          </table>
        </main>
      </div>
    </div>

    <!-- jQuery (necessary for Bootstrap's JavaScript plugins) -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <!-- Include all compiled plugins (below), or include individual files as needed -->
    <script src="js/bootstrap.min.js"></script>
    <script type="text/javascript" src="vendor/DataTables/datatables.min.js"></script>

    <script>
    $(document).ready(function() {
        $('#tbl_reports').DataTable({
            "ajax": "<?php echo BASE_URL.'/reports.php?cat='.$reportsCat ?>",
        });
    });
    </script>
  </body>
</html>
