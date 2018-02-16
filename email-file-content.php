<?php

include('userClass.php');

$userClass = new userClass();

$getAllDepartment=$userClass->getAllDepartment();
$getAllUser=$userClass->getAllUser();
$getUsersExceptSession =$userClass->getAllUserExceptFromID($_SESSION['uid']);
$emailDirectory=$userClass->emailDirectory();

function getFile($id)
{
    try {
     //   echo $id;
        $db = getDB();
     //   echo "SELECT * FROM file_record where reference_id == '".$id."' limit 1";
        $stmt = $db->prepare("SELECT * FROM file_record where reference_id = '".$id."' limit 1");
        $stmt->execute();
        $data = $stmt->fetch(PDO::FETCH_OBJ);

       // var_dump($data);

      return $data;
      } catch (Exception $e) {

      }
}

function getAllHistory($id)
{
    try {
       //   echo $id;
       $db = getDB();

       $stmt = $db->prepare("SELECT * FROM `fts_history` INNER JOIN users ON users.uid = fts_history.history_from WHERE history_reference_id = '".$id."' ORDER BY `history_date_time` DESC");
       $stmt->execute();
       $data = $stmt->fetchAll();

     return $data;
    } catch (Exception $e) {

    }
}

function updateFile($data)
{
    $userClass = new userClass(); //

    try {

        $db = getDB();

        // Update file
        $file = $db->prepare("UPDATE file_record SET date = :date, subject = :subject, priorityLevel = :priorityLevel, department_id = :department_id, initiator_user_id = :initiator_user_id, details = :details, user_id_approval = :user_id_approval WHERE reference_id = :reference_id");
        $file->bindParam(':reference_id', $data["fts_referenceId"]);
        $file->bindParam(':date', $data["fts_dateInitiated"]);
        $file->bindParam(':subject', $data["fts_subject"]);
        $file->bindParam(':priorityLevel', $data["fts_priority"]);
        $file->bindParam(':department_id', $data["fts_department"]);
        $file->bindParam(':initiator_user_id', $_SESSION['uid']); // session uid
        $file->bindParam(':details', $data["fts_detailInfo"]);
        $file->bindParam(':user_id_approval', $data["fts_approval"]);
        $isFileUpdated = $file->execute();

        // optional file upload
        if($_FILES['attachment']['size'] > 0) {
            $attachmentPath = $userClass->uploadFile($_FILES);

            $attachement = $db->prepare("UPDATE file_record SET attachment = :attachment WHERE reference_id = :reference_id");
            $attachement->bindParam(':attachment', $attachmentPath);
            $isFileUploaded = $attachement->execute();
        }

        $to_user = "";
        if ($data["recepientId"]) {
          $to_user = $data['recepientId'];
        }

        // history
        $dateTime = (new DateTime())->format('Y-m-d H:i:s');
        $history = $db->prepare("INSERT INTO fts_history (history_from,history_to,history_date_time,history_action,history_status,history_comment,history_reference_id) VALUES (:history_from,:history_to,:history_date_time,:history_action, :history_status,:history_comment,:history_reference_id)");
        $history->bindParam(':history_from', $_SESSION['uid']);
        $history->bindParam(':history_to', $to_user);
        $history->bindParam(':history_date_time', $dateTime);
        $history->bindParam(':history_action', $data["fileAction"]);
        $history->bindParam(':history_status', $data["file_status"]);
        $history->bindParam(':history_comment', $data["fts_comments"]);
        $history->bindParam(':history_reference_id', $data["fts_referenceId"]); // session uid
        $isHistoryUpdated = $history->execute();

        // Forward email if we have a recipient
        if ($to_user) {
            $toUserInfo = $db->prepare("SELECT email, FirstName FROM users WHERE uid = '" . $to_user . "' limit 1");
            $toUserInfo->execute();
            $toUserInfoObj = $toUserInfo->fetch(PDO::FETCH_OBJ);

            $toEmail = $toUserInfoObj->email;
            $toName = $toUserInfoObj->FirstName;
            $emailSubject = 'File: '.$data["fts_referenceId"];
            $emailBody = file_get_contents('forwardedEmail.html');
            $emailBody = str_replace("[[link]]", BASE_URL.'/email-file.php?reference_id=' . $data["fts_referenceId"], $emailBody);

            // Send Email
        	$userClass->sendEmail($toEmail, $toName, $emailSubject, $emailBody);
        }



        if ($isFileUpdated && $isHistoryUpdated) {
            echo "<script type= 'text/javascript'>alert('Record Successfully Saved'); location.href = '".BASE_URL."/email-file.php?reference_id=".$_GET['reference_id']."&saved=1'; </script>";
        }

    } catch(PDOException $e) {
      echo $e->getMessage();
    }
}


if (!getFile($_GET['reference_id'])) {
    echo 'Invalid reference id.'; exit();
}


if (count($_POST)) {

    $errors = [];

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

    if (isset($errors) && count($errors) <= 0) {
        updateFile($_POST);
    }

}

$data = getFile($_GET["reference_id"]);
$historyData = getAllHistory($_GET["reference_id"]);
// var_dump($data);


?>
<?php if(isset($errors) && $errors): ?>
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
        <div class="col-md-6 left">
            <div class="col-md-10">
                    <form action="email-file.php?reference_id=<?php echo $_GET['reference_id'] ?>"
                        method="post" id="frm_update_file" enctype="multipart/form-data">
                            <input type="hidden" name="recepientId" id="recepientId">
                            <input type="hidden" name="fileAction" id="fileAction" value="save">
                            <input type="hidden" name="file_status" id="file_status" value="open">

                            <!-- Reference Number -->
                            <div class="form-group">
                                <!-- <p for="referenceNo" class="col-sm-2 text-left">Reference No:</p> -->
                                <label for="referenceNo">Reference No</label>
                                <input type="" class="form-control" id="inputReferenceNo" placeholder="Reference Number" name="fts_referenceId" value="<?php echo $data->reference_id; ?> " readonly>
                            </div>

                            <!-- Date -->
                            <div class="form-group">
                                <label for="dated">Dated</label>
                                <input type="" class="form-control" id="date" placeholder="Date" name="fts_dateInitiated" value="<?php echo $data->date; ?>" readonly>
                            </div>

                            <!-- Subject -->
                            <div class="form-group">
                                <label for="subject">Subject</label>
                                <input type="text" class="form-control" id="subject" placeholder="Subject" name="fts_subject" value="<?php echo $data->subject; ?>" readonly required>
                            </div>

                            <!-- Detail -->
                            <div class="form-group">
                                <label for="details">Detail</label>
                                <textarea class="form-control" rows="3" placeholder="Details" name="fts_detailInfo" required readonly><?php echo $data->details; ?></textarea>
                            </div>

                            <!-- Priority -->
                            <div class="form-group">
                                <label for="details">Priority</label>
                                <?php
                                    $priority = ["Normal","Urgent","High"];
                                ?>
                                <select class="form-control" name="fts_priority" required readonly disabled>
                                    <?php
                                        foreach($priority as $value){ ?>
                                            <?php if($value == $data->priorityLevel) {  ?>
                                                <option selected value="<?php echo $value; ?>"><?php echo $value; ?></option>
                                            <?php } else { ?>
                                                <option  value="<?php echo $value; ?>"><?php echo $value; ?></option>
                                            <?php } ?>
                                        <?php } ?>
                                </select>
                            </div>

                            <!-- Departments -->
                            <div class="form-group">
                                <label for="department">Department</label>
                                <!-- <input type="" class="form-control" id="dated" placeholder="Department" name="fts_department"> -->
                                <select class="form-control" name="fts_department" required readonly disabled>
                                    <?php foreach($getAllDepartment as $key) { ?>
                                    <?php
                                        if($key["id"] == $data->department_id) {
                                        ?>
                                            <option selected value="<?php echo $key["id"] ?>  "><?php echo $key["name"] ?></option>
                                        <?php } else { ?>
                                              <option   value="<?php echo $key["id"] ?>  "><?php echo $key["name"] ?></option>
                                        <?php } ?>
                                    <?php } ?>
                                </select>
                            </div>


                            <!-- Initiator -->
                            <div class="form-group">
                                <label for="initiator">Initiator</label>

                                <?php foreach ($getAllUser as $key): ?>
                                    <?php if($key["uid"] == $data->initiator_user_id ): ?>
                                        <input  class="form-control" id="" placeholder="Initiator" name="fts_initiatorName" value="<?php echo  $key["FirstName"] . ' ' . $key["LastName"];  ?>" readonly>
                                    <?php endif; ?>
                                 <?php endforeach; ?>
                            </div>

                            <div class="form-group">
                                <label for="atTheDeskOf">At the Desk of:</label>
                                <?php foreach ($getAllUser as $key) :?>
                                    <?php if($key["uid"] == $data->user_id_approval ) : ?>
                                    	<input class="form-control"   value="<?php echo $key["FirstName"] . ' ' . $key["LastName"] ?>" readonly>
                                    <?php endif; ?>
                                <?php endforeach; ?>
                            </div>


                            <!-- Attachment -->
                            <div class="form-group">
                                <label for="attachment">Attachment</label>
                                <a href="<?php echo $data->attachment ?>" id="frm_attachment_download" target="_blank" class="btn btn-secondary btn-sm" role="button">Download</a>
                                <input type="file" id="frm_attachment" name="attachment" style="display: none">
                            </div>


                            <!-- Tentative Approval Cycle -->
                            <div class="form-group">
                                <label for="approvalCycle">Tentative Approval Cycle</label>
                                <!-- <input type="" class="form-control" id="dated" placeholder="Tentative approval cycle"> -->
                                <select class="form-control" name="fts_approval" required readonly disabled>
                                    <?php foreach ($getUsersExceptSession as $key) :?>
                                    <?php if($key["uid"] == $data->user_id_approval ) : ?>
                                        <option selected value="<?php echo $key["uid"] ?>  "><?php echo $key["FirstName"] . ' ' . $key["LastName"] ?></option>
                                    <?php else: ?>
                                        <option  value="<?php echo $key["uid"] ?>  "><?php echo $key["FirstName"] . ' ' . $key["LastName"] ?></option>
                                    <?php endif; ?>
                                    <?php endforeach; ?>
                                </select>
                            </div>


                            <!-- Comments -->
                            <!-- maybe jquery here to automate when typing comments to show in history -->
                            <div class="form-group">
                                <label for="comments">Comments</label>
                                <textarea class="form-control" rows="3" placeholder="Comments" name="fts_comments" readonly></textarea>
                            </div>

                            <div class="form-group">
                                <p>Created on: <?php echo $data->date; ?></p>
                            </div>


                            <div id="frm_btns_control" class="form-group float-right" style="display: none;">
                              <input type="submit" class="btn btn-primary" value="Save" name="saveForward">
                              <button type="button" class="btn btn-default" id="btnForward_popup">Forward</button>
                              <button type="button" class="btn btn-dark" id="btn_mark_closed">Mark as closed</button>
                              <button type="button" class="btn btn-warning" id="btn_mark_cancelled">Mark as Cancelled</button>
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

        </div>
        <div class="col-md-6 right">
            <h5>History</h5>
            <!-- Loop through here to dispaly all the history -->
            <div class="history-wrapper">
            <?php if($historyData): ?>
                <?php foreach($historyData as $key): ?>
                 <?php

                     $history_userToName = $userClass->userDetails($key["history_to"]);
                     $history_userToName = ($history_userToName) ? $history_userToName->FirstName. ' '.$history_userToName->LastName : '';

                 ?>
                 <div class="">
                     <p><strong>From</strong>: <?php echo $key["FirstName"] . ' ' . $key["LastName"] ?></p>
                     <p><strong>To</strong>: <?php echo $history_userToName ?></p>
                     <p><strong>Date</strong>: <?php echo $key["history_date_time"] ?></p>
                     <p><strong>Action</strong>: <?php echo $key["history_action"] ?></p>
                     <p><strong>Comments</strong>: <?php echo $key["history_comment"] ?></p>
                     <hr>
                 </div>
                <?php endforeach; ?>
            <?php endif; ?>
            </div>
        </div>
    </div>
