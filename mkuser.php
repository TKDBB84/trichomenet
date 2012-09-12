<?php
if(!isset($_SESSION))    session_start();
include_once 'Bcrypt.php';
include_once 'connection.php';

$email = '';
$pass1 = '';
$pass2 = '';
$error = false;
$error_text = '';
$new_user_id = -1;

if(!isset($_POST['email']) || empty($_POST['email'])){
    $error = true;  //no email defined!!
    $error_text .= 'You Must Enter An Email Address<br/>';
}else{
    $email = $_POST['email'];
}
if(!isset($_POST['password']) || empty($_POST['password'])){
    $error = true; //no password
    $error_text .= 'You Must Enter A Password<br/>';
}else{
    $pass1 = $_POST['password'];
}
if(!isset($_POST['password2']) || empty($_POST['password2'])){
    $error = true; //no confirmaiton
    $error_text .= 'You Must Confirm Your Password<br/>';
}else{
    $pass2 = $_POST['password2'];
}
if(validEmail($email) === 1){
    $error = true;  //invalid email
    $error_text .= 'Your Email Address Does Not Appear Valid<br/>';
}
if($pass1 !== $pass2){
    $error = true;  //password mismatch
    $error_text .= 'Your Password And Conformation Dont Match<br/>';
}

if(strlen($pass1) < 6){
    $error = true;  //password too short
    $error_text .= 'Your Password Must Be At Least 6 Characters<br/>';
}

if(!$error){
    $bcrypt = new Bcrypt(15);
    $hash = $bcrypt->hash($pass1);
    if(!$bcrypt->verify($pass1, $hash)){
        die('encryption failure !?!?');
    }
    if(isset($_POST['name']))
        $name = $_POST['name'];
    else
        $name = 'Anonymous';
    if(isset($_POST['org']))
        $org = $_POST['org'];
    else
        $org = 'Unknown';
    
    $stmt_check_address = $pdo_dbh->prepare("SELECT 1 FROM `users` WHERE `email`= :email LIMIT 1");
    $stmt_check_address->bindValue(':email',$email,PDO::PARAM_STR);
    $stmt_check_address->execute() or die($stmt_check_address->queryString.'<br/><br/>'.var_dump($stmt_check_address->errorInfo()));
    $result = $stmt_check_address->fetchAll(PDO::FETCH_ASSOC);
    if(count($result) > 0){
        $error = true;
        $error_text .= 'Email Address Is Already Registered<br/>';
    }
    
    $tmp_id = -1;
    if(!$error){
        $stmt_add_user = $pdo_dbh->prepare("INSERT INTO `users` (`email`,`name`,`password`,`org`) VALUES (:email,:username,:pass,:org)");
        $stmt_add_user->bindValue(':email',$email,PDO::PARAM_STR);
        $stmt_add_user->bindValue(':username',$name,PDO::PARAM_STR);
        $stmt_add_user->bindValue(':pass',$hash,PDO::PARAM_STR);
        $stmt_add_user->bindValue(':org',$org,PDO::PARAM_STR);
        $entry_error = $stmt_add_user->execute() or die($stmt_add_user->queryString.'<br/><br/>'.var_dump($stmt_add_user->errorInfo()));
        $tmp_id = $pdo_dbh->lastInsertId();
        
        if(!$entry_error || $tmp_id <= 0){
            $error = true;
            $error_text .= 'There was an Error Setting Up Your User Account, Please Try Again<br/>';
        }else{
            $new_user_id = $tmp_id;
        }
    }
}

if($error){
    $_SESSION['error_text'] = $error_text;
    //die(var_dump($_SESSION));
    header('Location: ./register.php');
    //echo '<head><meta http-equiv="REFRESH" content="0;url=./register.php"></head>';
}else{
    $_SESSION['user_id'] = $new_user_id;
    header('Location: ./addGenotypes.php');
}

?>


<?php
/**
Validate an email address.
Provide email address (raw input)
Returns true if the email address has the email 
address format and the domain exists.
*/
function validEmail($email)
{
   $isValid = true;
   $atIndex = strrpos($email, "@");
   if (is_bool($atIndex) && !$atIndex)
   {
      $isValid = false;
   }
   else
   {
      $domain = substr($email, $atIndex+1);
      $local = substr($email, 0, $atIndex);
      $localLen = strlen($local);
      $domainLen = strlen($domain);
      if ($localLen < 1 || $localLen > 64)
      {
         // local part length exceeded
         $isValid = false;
      }
      else if ($domainLen < 1 || $domainLen > 255)
      {
         // domain part length exceeded
         $isValid = false;
      }
      else if ($local[0] == '.' || $local[$localLen-1] == '.')
      {
         // local part starts or ends with '.'
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $local))
      {
         // local part has two consecutive dots
         $isValid = false;
      }
      else if (!preg_match('/^[A-Za-z0-9\\-\\.]+$/', $domain))
      {
         // character not valid in domain part
         $isValid = false;
      }
      else if (preg_match('/\\.\\./', $domain))
      {
         // domain part has two consecutive dots
         $isValid = false;
      }
      else if
(!preg_match('/^(\\\\.|[A-Za-z0-9!#%&`_=\\/$\'*+?^{}|~.-])+$/',
                 str_replace("\\\\","",$local)))
      {
         // character not valid in local part unless 
         // local part is quoted
         if (!preg_match('/^"(\\\\"|[^"])+"$/',
             str_replace("\\\\","",$local)))
         {
            $isValid = false;
         }
      }
      if ($isValid && !(checkdnsrr($domain,"MX") || checkdnsrr($domain,"A")))
      {
         // domain not found in DNS
         $isValid = false;
      }
   }
   return $isValid;
}
?>