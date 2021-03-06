<?php

include('config.php');
include("session.php");

include('userClass.php');
$userClass = new userClass();

$errorMsgReg='';
$errorMsgLogin='';
$successMsg='';

if (isset($_GET['reference']))
{
    if (!empty($_POST['forgotSubmit']))
	{
	    $password = $_POST['password'];

	    if(strlen(trim($password))>1)
	    {
	        $ID=$userClass->updateUserPassword($password, $_GET['reference']);
	        
	        if($ID)
	        {
	        	$errorMsgLogin = '';
	            $successMsg='';
	            header('Location: index.php');
				exit();
	        }
	    } else {
	    	$errorMsgLogin="Please fill out all the forms before you continue.";
	    	$successMsg='';
	    }
	}

} else {
	header('Location: index.php');
	exit();
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="utf-8">
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<title>ADRIAS</title>

	<!-- Global stylesheets -->
	<link href="https://fonts.googleapis.com/css?family=Roboto:400,300,100,500,700,900" rel="stylesheet" type="text/css">
	<link href="assets/css/icons/icomoon/styles.css" rel="stylesheet" type="text/css">
	<link href="assets/css/bootstrap.css" rel="stylesheet" type="text/css">
	<link href="assets/css/core.css" rel="stylesheet" type="text/css">
	<link href="assets/css/components.css" rel="stylesheet" type="text/css">
	<link href="assets/css/colors.css" rel="stylesheet" type="text/css">
	<!-- /global stylesheets -->

	<!-- Core JS files -->
	<script type="text/javascript" src="assets/js/plugins/loaders/pace.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/jquery.min.js"></script>
	<script type="text/javascript" src="assets/js/core/libraries/bootstrap.min.js"></script>
	<script type="text/javascript" src="assets/js/plugins/loaders/blockui.min.js"></script>
	<!-- /core JS files -->

	<!-- Theme JS files -->
	<script type="text/javascript" src="assets/js/plugins/forms/styling/uniform.min.js"></script>
	<script type="text/javascript" src="assets/js/core/app.js"></script>
	<script type="text/javascript" src="assets/js/pages/login.js"></script>
	<script type="text/javascript" src="assets/js/plugins/ui/ripple.min.js"></script>
	<!-- /theme JS files -->

</head>

<body class="login-container">

	<!-- Page container -->
	<div class="page-container">

		<!-- Page content -->
		<div class="page-content">

			<!-- Main content -->
			<div class="content-wrapper">

				<!-- Content area -->
				<div class="content">

					<br/><br/>

					<!-- Advanced login -->
					<form method="post" action="change-password.php?reference=<?php if(isset($_GET['reference'])){ echo $_GET['reference']; } ?>">
						<div class="panel panel-body login-form">
							<div class="text-center">
								<h5 class="content-group">Change password <small class="display-block">Enter your new password</small></h5>
							</div>

							<?php if($errorMsgLogin): ?>
				            <div class="alert bg-danger-400 alert-styled-left">
								<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
								<span class="text-semibold">Error!</span> <?php echo $errorMsgLogin; ?>
						    </div>
				            <?php endif; ?>

				            <?php if($successMsg): ?>
				            <div class="alert bg-success-800 alert-styled-left">
								<button type="button" class="close" data-dismiss="alert"><span>×</span><span class="sr-only">Close</span></button>
								<span class="text-semibold">Success!</span> <?php echo $successMsg; ?>
						    </div>
				            <?php endif; ?>

							<div class="form-group has-feedback has-feedback-left">
								<input type="password" name="password" class="form-control" placeholder="New password">
								<div class="form-control-feedback">
									<i class="icon-lock2 text-muted"></i>
								</div>
							</div>

							<div class="form-group">
								<button type="submit" name="forgotSubmit" value="forgotSubmit" class="btn bg-grey-700 btn-block">Change password <i class="icon-arrow-right14 position-right"></i></button>
							</div>
							<span class="help-block text-center no-margin">By continuing, you're confirming that you've read our <a href="">Terms &amp; Conditions</a> and <a href="">Cookie Policy</a></span>
						</div>
					</form>
					<!-- /advanced login -->


					<!-- Footer -->
					<div class="footer text-muted text-center">
						&copy; 2018. <a href="">File Tracker System</a> by <a href="https://www.facebook.com/charlene.serundo" target="_blank">Adrias, Charlene</a>
					</div>
					<!-- /footer -->

				</div>
				<!-- /content area -->

			</div>
			<!-- /main content -->

		</div>
		<!-- /page content -->

	</div>
	<!-- /page container -->

</body>
</html>
