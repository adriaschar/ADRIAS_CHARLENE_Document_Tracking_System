<?php

if (!function_exists('getDB')) {
  include('DB.php');
}

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require 'src/Exception.php';
require 'src/PHPMailer.php';
require 'src/SMTP.php';

class userClass {
  
  // User Login
  public function userLogin($usernameEmail, $password){
    try {
      $db = getDB();
      $hash_password = md5($password); //Password encryption
      $stmt = $db->prepare("SELECT uid FROM users WHERE (username=:usernameEmail or email=:usernameEmail) AND password=:hash_password");
      $stmt->bindParam("usernameEmail", $usernameEmail, PDO::PARAM_STR);
      $stmt->bindParam("hash_password", $hash_password, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_OBJ); //User data
      $db = null;
      if ($data) {
        return $data->uid;
      }
      else {
        return false;
      }
    } catch(PDOException $e) {
      echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
  }

  // User send email to reset password
  public function userResetPassword($usernameEmail){
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM users WHERE (username=:usernameEmail or email=:usernameEmail)");
      $stmt->bindParam("usernameEmail", $usernameEmail, PDO::PARAM_STR);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_OBJ);
      $db = null;

      // Prepared the parameters for the sending of email address.
      $toEmail = $data->email;
      $toName = $data->FirstName;
      $emailSubject = 'Reset Password for '.$data->email;
      $emailBody = file_get_contents('forgot-forwardedEmail.html');
      $emailBody = str_replace("[[link]]", BASE_URL.'/change-password.php?reference=' . $data->uid, $emailBody);

      // Sends an email message to the selected user.
      $this->sendEmail($toEmail, $toName, $emailSubject, $emailBody);

      if ($data) {
        return true;
      }
      else {
        return false;
      }
    } catch(PDOException $e) {
      echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
  }

  // Change password
  function updateUserPassword($new_password, $uid){
    $hash_password = md5($new_password); //Password encryption
    $db = getDB();
    $stmt = $db->prepare('UPDATE users SET password = :hash_password WHERE uid = :uid');
    $stmt->bindParam("hash_password", $hash_password, PDO::PARAM_STR);
    $stmt->bindParam("uid", $uid, PDO::PARAM_STR);
    $stmt->execute();
    return true;
  }

  // User Registration
  public function userRegistration($username, $password, $email, $firstname, $lastname){
    try {
      $db = getDB();
      $st = $db->prepare("SELECT uid FROM users WHERE username=:username OR email=:email");
      $st->bindParam("username", $username, PDO::PARAM_STR);
      $st->bindParam("email", $email, PDO::PARAM_STR);
      $st->execute();
      $count = $st->rowCount();
      if ($count < 1) {
        $stmt = $db->prepare("INSERT INTO users(username,password,email,FirstName,LastName) VALUES (:username,:hash_password,:email,:firstname,:lastname)");
        $stmt->bindParam("username", $username, PDO::PARAM_STR);
        $hash_password = md5($password); //Password encryption
        $stmt->bindParam("hash_password", $hash_password, PDO::PARAM_STR);
        $stmt->bindParam("email", $email, PDO::PARAM_STR);
        $stmt->bindParam("firstname", $firstname, PDO::PARAM_STR);
        $stmt->bindParam("lastname", $lastname, PDO::PARAM_STR);
        $stmt->execute();
        $uid = $db->lastInsertId(); // Last inserted row id
        $db = null;
        return true;
      } else {
        $db = null;
        return false;
      }
    } catch(PDOException $e) {
      echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
  }

  // User Details
  public function userDetails($uid){
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM users WHERE uid=:uid");
      $stmt->bindParam("uid", $uid, PDO::PARAM_INT);
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_OBJ); //User data
      return $data;
    } catch(PDOException $e) {
      echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
  }

  // New file Details - reference_id
  public function newFile($uid){
    $file_id = 1;
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT reference_id FROM file_record limit 1");
      $stmt->execute();
      $data = $stmt->fetch(PDO::FETCH_OBJ);
      if ($data) {
        $file_id = ($data->reference_id) ? 1 : $data->reference_id++;
      }

      $t = time();
      $newdate = (date("Y-m-d", $t));
      $date = new DateTime();
      $timeStamp = $date->getTimestamp();
      $reference_id = $uid . '/' . $file_id . '/' . $newdate . '/' . $timeStamp;
      return $reference_id;
    } catch(Exception $e) {
      echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
  }

  // This method gets the list of all users in the database.
  public function getAllUser(){
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM users");
      $stmt->execute();
      $data = $stmt->fetchAll();
      return $data;
    } catch(Exception $e){}
  }

  // This method gets the user details bease form uid number.
  public function getAllUserExceptFromID($userId){
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM users where uid != " . $userId);
      $stmt->execute();
      $data = $stmt->fetchAll();
      return $data;
    } catch(Exception $e){}
  }

  public function emailDirectory(){
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM users WHERE uid != " . $_SESSION['uid']);
      $stmt->execute();
      $data = $stmt->fetchAll();
      return $data;
    } catch(Exception $e) {
      echo '{"error":{"text":' . $e->getMessage() . '}}';
    }
  }

  // This method gets the list of all departments in the database.
  public function getAllDepartment(){
    try {
      $db = getDB();
      $stmt = $db->prepare("SELECT * FROM fts_department");
      $stmt->execute();
      $data = $stmt->fetchAll();
      return $data;
    } catch(Exception $e){}
  }

  // This method calles a method that saves a file being uploaded during new-file creation.
  public function uploadFile($files){
     try {
         // Undefined | Multiple Files | $_FILES Corruption Attack
         // If this request falls under any of them, treat it invalid.
         if (!isset($files['attachment']['error']) ||
                 is_array($files['attachment']['error'])) {
                 throw new Exception('Invalid parameters.');
             }
             $targetpath = 'uploads/';
             $newFileName = sha1_file($files['attachment']['tmp_name']);
             $ext = pathinfo($files['attachment']['name'], PATHINFO_EXTENSION);

             // You should also check filesize here.
            if ($files['attachment']['size'] > 1000000) {
                throw new Exception('Exceeded filesize limit.');
            }

             // TODO Check MIME Type here..

             // You should name it uniquely.
             // DO NOT USE $files['upfile']['name'] WITHOUT ANY VALIDATION !!
             // On this example, obtain safe unique name from its binary data.
             if (!move_uploaded_file(
                 $files['attachment']['tmp_name'],
                 sprintf('./'.$targetpath.'%s.%s',
                 $newFileName,
                 $ext)
            )) {
                throw new Exception('Failed to move uploaded file.');
            }

        return $targetpath.$newFileName.'.'.$ext;
    } catch(Exception $e) {
       echo $e->getMessage();
    }
  }

  // This method sends an email message to the selected user.
  public function sendEmail($toEmail, $toName, $emailSubject, $emailBody, $debug = false){
      // Create a new PHPMailer instance
      $mail = new PHPMailer;
      if ($debug) {
          $mail->SMTPDebug = $debug;
      }
      $mail->IsSMTP();
      $mail->Host = 'tls://smtp.gmail.com'; // Using the SMTP services of Google. Change this if you plan to use a different SMTP provider.
      $mail->CharSet = "UTF-8";
      $mail->Port = 587;
      $mail->SMTPSecure = 'tls';
      $mail->SMTPAuth = true;
      $mail->Username = "moa.massage.nail.art@gmail.com"; // Email address of email generator. Change this if you want to use a different email account.

      // Set who the message is to be sent from
      $mail->setFrom('notification@filetrackingsystem.com', 'File Tracking System');

      // Set an alternative reply-to address
      //  $mail->addReplyTo('replyto@example.com', 'First Last');
      // Set who the message is to be sent to

      $mail->addAddress($toEmail, $toName);
      $mail->Password = "P@ssword01"; // Password of email generator. Change this if you want to use a different email account.

      // Set the subject line
      $mail->Subject = $emailSubject;

      // Read an HTML message body from an external file, convert referenced images to embedded,
      // convert HTML into a basic plain-text alternative body
      $mail->msgHTML($emailBody, __DIR__);

      // Replace the plain text body with one created manually
      // $mail->AltBody = 'This is a plain-text message body';

      // Prints the result message
      if (!$mail->send()) {
        return ['error' => true, 'errorMessage' => $mail->ErrorInfo, 'message' => ''];
      } else {
        return ['error' => false, 'errorMessage' => '', 'message' => 'Successfully sent.'];
      }
  }

  // This method processes the saving of the file record to the database, saving of the file being uploaded, and forwarding an email to the selected user.
  public function sendFile($data){
    try {
      $db = getDB();

      // This code strip calles a method that saves a file being uploaded during new-file creation.
      // This code strip is called as optional.
      $attachmentPath = '';
      if($_FILES['attachment']['size'] > 0) {
          $attachmentPath = $this->uploadFile($_FILES);
      }

      // Saves the new-file record to the database.
      $stmt = $db->prepare("INSERT INTO file_record (reference_id, date,subject,priorityLevel,department_id,initiator_user_id,details,user_id_approval, attachment) VALUES (:reference_id, :date,:subject,:priorityLevel,:department_id,:initiator_user_id,:details,:user_id_approval, :attachment)");
      $stmt->bindParam(':reference_id', $data["fts_referenceId"]);
      $stmt->bindParam(':date', $data["fts_dateInitiated"]);
      $stmt->bindParam(':subject', $data["fts_subject"]);
      $stmt->bindParam(':priorityLevel', $data["fts_priority"]);
      $stmt->bindParam(':department_id', $data["fts_department"]);
      $stmt->bindParam(':initiator_user_id', $_SESSION['uid']); // session uid of the current user.
      $stmt->bindParam(':details', $data["fts_detailInfo"]);
      $stmt->bindParam(':user_id_approval', $data["fts_approval"]);
      $stmt->bindParam(':attachment', $attachmentPath);
      $sql1 = $stmt->execute();

      $to_user = "";

      // This code strip will be called only if the user selected the 'Forward' button during new-file creation.
      // This code strip will use the PHPMailer library to send an email message to the user.
      if ($data["recepientId"]) {
        $to_user = $data['recepientId'];

        // Get's the email address of the selected user.
        $toUserInfo = $db->prepare("SELECT email, FirstName FROM users WHERE uid = '" . $to_user . "' limit 1");
        $toUserInfo->execute();
        $toUserInfoObj = $toUserInfo->fetch(PDO::FETCH_OBJ);

        // Prepared the parameters for the sending of email address.
        $toEmail = $toUserInfoObj->email;
        $toName = $toUserInfoObj->FirstName;
        $emailSubject = 'File: '.$data["fts_referenceId"];
        $emailBody = file_get_contents('forwardedEmail.html');
        $emailBody = str_replace("[[link]]", BASE_URL.'/email-file.php?reference_id=' . $data["fts_referenceId"], $emailBody);

        // Sends an email message to the selected user.
        $this->sendEmail($toEmail, $toName, $emailSubject, $emailBody);
      }

      // Saves the comment and a history record of the transaction.
      $dateTime = (new DateTime())->format('Y-m-d H:i:s');
      $stmt2 = $db->prepare("INSERT INTO fts_history (history_from,history_to,history_date_time,history_action,history_status,history_comment,history_reference_id) VALUES (:history_from,:history_to,:history_date_time,:history_action,'open',:history_comment,:history_reference_id)");
      $stmt2->bindParam(':history_from', $_SESSION['uid']);
      $stmt2->bindParam(':history_to', $to_user);
      $stmt2->bindParam(':history_date_time', $dateTime);
      $stmt2->bindParam(':history_action', $data["fileAction"]);
      $stmt2->bindParam(':history_comment', $data["fts_comments"]);
      $stmt2->bindParam(':history_reference_id', $data["fts_referenceId"]); // session uid
      $sql2 = $stmt2->execute();

      // Prints the result message
      if ($sql1 && $sql2) {
        echo "<script type= 'text/javascript'>alert('File Successfully Saved');</script>";
      } else {
        echo "<script type= 'text/javascript'>alert('File not saved. Something went wrong.');</script>";
      }
      $dbh = null;
    }

    catch(PDOException $e) {
      echo $e->getMessage();
    }
  }
}

?>
