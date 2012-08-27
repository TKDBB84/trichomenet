<?php
if(!isset($_SESSION))    session_start();
include_once 'connection2.php';

$ERRORS = array();
if(isset($_POST)){
    $username = $_POST['new_username'];
    $email = $_POST['new_email'];
    $newPassword = ($_POST['new_pass1'] === $_POST['new_pass2'])?$_POST['new_pass1']:-1;
    if($newPassword == -1)
        $ERRORS['password'] = 'Password Does Not Match Confirmation<br/>';
    }
    if(strlen($newPassword) < 6){
        $ERRORS['password'] .= 'Password Must Be At Least 6 Characters<br/>';
    }
    if(!check_email_address(stripslashes($email))){
        $ERRORS['email'] = 'Please Enter A Valid E-Mail Address<br/>';
    }
    $sql_check_un = $pdo_dbh->prepare('select exists(select 1 from users where username=:un) as username;');
    $sql_check_un->bindValue(':un', $username, PDO::PARAM_STR);
    $sql_check_un->execute() or die($sql_check_un->queryString.'<br/><br/>'.var_dump($sql_check_un->errorInfo()));
    $row = $sql_check_un->fetch(PDO::FETCH_ASSOC);
    if($row['username'] === 1){
        $ERRORS['username'] = 'Username Is Already Taken<br/>';
    }
    if(empty($ERRORS)){
        $password_hash = hash_hmac("sha1",$newPassword,$this_sites_salt);
        $sql_make_user = $pdo_dbh->prepare('INSERT INTO `users` (`username`,`email`,`password`) VALUES (:un,:email,:pass);');
        $sql_make_user->bindValue(':un', $username, PDO::PARAM_STR);
        $sql_make_user->bindValue(':email', $email, PDO::PARAM_STR);
        $sql_make_user->bindValue(':pass', $password_hash, PDO::PARAM_STR);
        $sql_make_user->execute() or die($sql_make_user->queryString.'<br/><br/>'.var_dump($sql_make_user->errorInfo()));
    }
?>
<br/><br/>
<form action="<?php echo htmlentities($_SERVER['PHP_SELF']); ?>" method='POST'>
Username: <input type="text" name="new_username"/><br/>
E-Mail: <input type="email" name="new_email"/><br/>
<br/>
Password: <input type="password" name="new_pass1"/><br/>
Confirm: <input type="password" name="new_pass2"/><br/>
</form>

<?php
function check_email_address($email) {
  // First, we check that there's one @ symbol, 
  // and that the lengths are right.
  if (!ereg("^[^@]{1,64}@[^@]{1,255}$", $email)) {
    // Email invalid because wrong number of characters 
    // in one section or wrong number of @ symbols.
    return false;
  }
  // Split it into sections to make life easier
  $email_array = explode("@", $email);
  $local_array = explode(".", $email_array[0]);
  for ($i = 0; $i < sizeof($local_array); $i++) {
    if
(!ereg("^(([A-Za-z0-9!#$%&'*+/=?^_`{|}~-][A-Za-z0-9!#$%&
↪'*+/=?^_`{|}~\.-]{0,63})|(\"[^(\\|\")]{0,62}\"))$",
$local_array[$i])) {
      return false;
    }
  }
  // Check if domain is IP. If not, 
  // it should be valid domain name
  if (!ereg("^\[?[0-9\.]+\]?$", $email_array[1])) {
    $domain_array = explode(".", $email_array[1]);
    if (sizeof($domain_array) < 2) {
        return false; // Not enough parts to domain
    }
    for ($i = 0; $i < sizeof($domain_array); $i++) {
      if
(!ereg("^(([A-Za-z0-9][A-Za-z0-9-]{0,61}[A-Za-z0-9])|
↪([A-Za-z0-9]+))$",
$domain_array[$i])) {
        return false;
      }
    }
  }
  return true;
} 
?>