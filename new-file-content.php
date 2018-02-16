<?php

include('userClass.php');
$userClass = new userClass();
$userDetails=$userClass->userDetails($_SESSION['uid']);

$userDetails=$userClass->userDetails($_SESSION['uid']);
$newFile=$userClass->newFile($_SESSION['uid']);
$getAllUser=$userClass->getAllUser();
$getUsersExceptSession =$userClass->getAllUserExceptFromID($_SESSION['uid']);
$getAllDepartment=$userClass->getAllDepartment();
$emailDirectory=$userClass->emailDirectory();

$errors = [];

if($_POST){
    // if($_FILES['attachment']['size'] == 0) {
    //     $errors[count($errors)] = 'Attachement is required';
    // }
    if (!$_POST['fts_subject']) {
        $errors[count($errors)] = 'Subject is required';
    }
    if (!$_POST['fts_detailInfo']) {
        $errors[count($errors)] = 'Detail is required';
    }
    if (!$_POST['fts_priority']) {
        $errors[count($errors)] = 'Priorty is required';
    }
    if (!$_POST['fts_department']) {
        $errors[count($errors)] = 'Department is required';
    }
    if (!$_POST['fts_approval']) {
        $errors[count($errors)] = 'Tentative Approval Cycle is required';
    }

    if(count($errors) <= 0) {
        $userClass->sendFile($_POST);
    }
}

?>

<?php if($errors): ?>
  <div class="row">
      <div class="col-md-7">
          <div class="alert alert-danger">
            <?php foreach($errors as $error): ?>
                <div><?php echo $error; ?></div>
            <?php endforeach; ?>
          </div>
      </div>
  </div>
<?php endif; ?>

  <div class="row">
      <div class="col-md-7">
        <h5>File Details</h5>

         <form action="new-file.php" method="post" id="fileFormId" enctype="multipart/form-data">
            <input type="hidden" name="recepientId" id="recepientId">
            <input type="hidden" name="fileAction" id="fileAction" value="save">

            <!-- Reference Number -->
            <div class="form-group">
                <!-- <p for="referenceNo" class="col-sm-2 text-left">Reference No:</p> -->
                <label for="referenceNo">Reference No</label>
                <input type="" class="form-control" id="inputReferenceNo" placeholder="Reference Number" name="fts_referenceId" value = "<?php echo $newFile; ?>" readonly>
            </div>

              <!-- Date -->
              <div class="form-group">
                <label for="dated">Dated</label>
                <input type="" class="form-control"  placeholder="Date"  name="fts_dateInitiated" value= "<?php echo $dateTime = (new \DateTime())->format('Y-m-d H:i:s'); ?>" readonly>
              </div>

              <!-- Subject -->
              <div class="form-group">
                <label for="subject">Subject *</label>
                <input type="" class="form-control" id="subject" placeholder="Subject" name="fts_subject" required>
              </div>

              <!-- Detail -->
              <div class="form-group">
                <label for="details">Detail *</label>
                <textarea class="form-control" rows="3" placeholder="Details" name="fts_detailInfo" required></textarea>
              </div>

              <!-- Priority -->
              <div class="form-group">
                <label for="details">Priority *</label>
                <select class="form-control" name="fts_priority" required>
                  <option>Normal</option>
                  <option>Urgent</option>
                  <option>High</option>
                </select>
              </div>

              <!-- Departments -->
              <div class="form-group">
                <label for="department">Department *</label>
                <!-- <input type="" class="form-control" id="dated" placeholder="Department" name="fts_department"> -->
                <select class="form-control" name="fts_department" required>
                <?php foreach ($getAllDepartment as $key): ?>
                      <option value="<?php echo $key["id"] ?>"><?php echo $key["name"] ?></option>
                <?php endforeach; ?>
              </select>
              </div>


              <!-- Initiator -->
              <div class="form-group">
                <label for="initiator">Initiator *</label>
                <input type="" class="form-control"  placeholder="Initiator" name="fts_initiatorName" value = "<?php echo $userDetails->FirstName . ' ' .$userDetails->LastName; ?>" readonly>
              </div>

              <!-- Attachment -->
              <div class="form-group">
                <label for="attachment">Attachment *</label>
                <input type="file" id="exampleInputFile" name="attachment">
                  <!-- <p class="help-block">Example block-level help text here.</p> -->
              </div>


              <!-- Tentative Approval Cycle -->
              <div class="form-group">
                <label for="approvalCycle">Tentative Approval Cycle *</label>
                <!-- <input type="" class="form-control" id="dated" placeholder="Tentative approval cycle"> -->
                <select class="form-control" name="fts_approval" required>
                <?php foreach ($getUsersExceptSession as $key): ?>
                    <option value="<?php echo $key["uid"] ?>"><?php echo $key["FirstName"] . ' ' . $key["LastName"] ?></option>
                <?php endforeach; ?>
                 </select>
              </div>


              <!-- Comments -->
              <!-- maybe jquery here to automate when typing comments to show in history -->
              <div class="form-group">
                <label for="comments">Comments</label>
                <textarea class="form-control" rows="3" placeholder="Comments" name="fts_comments"></textarea>
                <!-- <button id="newComment" type="button" class="btn btn-default">Add Comment</button> -->
              </div>

              <!-- Add Comment -->
              <div class="form-group float-right">
                <input type="submit" class="btn btn-primary" value="save" name="saveForward">
                <button type="button" class="btn btn-default" id="btnForward_popup">Forward</button>
                &nbsp; or <a href="<?php echo BASE_URL ?>/index.php" role="button" class="btn btn-default">Cancel</a>
              </div>

            <!-- Add content to the popup -->
            <div id="forward_popup" style="background: #fff; padding:20px; width:500px; min-height:300px; ">
              <!-- email -->
              <div class="form-group">
                <label for="emailTo">Directory Email List</label>

                <select class="form-control" name="fts_email_to" id="recepientIdList">
                <?php foreach ($emailDirectory as $key): ?>
                    <option value="<?php echo $key["uid"] ?>"><?php echo $key["FirstName"] . ' ' . $key["LastName"] ?></option>
                <?php endforeach; ?>
              	</select>
              </div>

              <!-- Add an optional button to close the popup -->
              <button class="btn btn-default" id="forward_popup_send">Send</button>
              <button class="btn btn-default" id="forward_popup_close">Cancel</button>
            </div>

           </form>
      </div>
      <div class="col-md-5">
      </div>
  </div>
