<?php
if(!isset($_SESSION))    session_start();
echo '<br/>';
if(isset($_SESSION['error_text'])){
      echo '<span style="color:red;">',$_SESSION['error_text'],'</span>';
      unset($_SESSION['error_text']);
}
?>
<script type="text/javascript">
    function validateEmail(email) { 
        var re = /^(([^<>()[\]\\.,;:\s@\"]+(\.[^<>()[\]\\.,;:\s@\"]+)*)|(\".+\"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
        return re.test(email);
    } 
    
    function chkEmail(){
        var email = document.getElementById('email').value;
        if(validateEmail(email)){
            document.getElementById('email_error').innerHTML = "<img src='./pics/greenchk.png'/>";
        }else if(email === ''){
            document.getElementById('email_error').innerHTML = "";
        }else{
            document.getElementById('email_error').innerHTML = "<img src='./pics/redx.png'/>";
        }   
    }
    
    function chkPass(){
        var pass = document.getElementById('pass1').value;
        var pass2 = document.getElementById('pass2').value;
        if(pass === ''){
            document.getElementById('pass1_error').innerHTML = '';
        }else if(pass.length < 6){
            document.getElementById('pass1_error').innerHTML = "<img src='./pics/redx.png'/>";
        }else{
            document.getElementById('pass1_error').innerHTML = "<img src='./pics/greenchk.png'/>";
        }
        
        if(pass2 === ''){
            document.getElementById('pass2_error').innerHTML = '';
        }else if(pass2.length < 6){
            document.getElementById('pass2_error').innerHTML = "<img src='./pics/redx.png'/>";
        }
        
        if( (pass.length > 5 && pass2.length > 5) && (pass === pass2) ) {
            document.getElementById('pass1_error').innerHTML = "<img src='./pics/greenchk.png'/>";
            document.getElementById('pass2_error').innerHTML = "<img src='./pics/greenchk.png'/>";
        }
    }
</script>

<form method="post" action="./mkuser.php">
    <table>
        <tr>
            <td align="right">E-Mail:<br/>
                <span style="font-size: .7em;">this will act as your login</span></td>
            <td><input type="email" name="email" id="email" onchange="chkEmail();" onblur="chkEmail();"/></td>
            <td><div id="email_error"/></td>
        </tr>
        <tr>
            <td align="right">Name:</td>
            <td><input type="text" name="name"/></td>
        </tr>
        <tr>
            <td align="right">Organization:</td>
            <td><input type="text" name="org"/></td>
        </tr>
        <tr>
            <td align="right">Password:</td>
            <td><input type="password" name="password" onkeyup="chkPass();" onblur="chkPass();" id="pass1"/></td>
            <td><div id="pass1_error"/></td>
        </tr>
        <tr>
            <td align="right">Confirm:</td>
            <td><input type='password' name="password2" onkeyup="chkPass();" onblur="chkPass();" id="pass2"/></td>
            <td><div id="pass2_error"/></td>
        </tr>
        <tr>
            <td align="right" colspan="2">
                <span style="font-size: .7em;">password must be at least 6 characters</span>
            </td>
            <td/>
        </tr>
    </table>
<button type="submit">Submit</button>
</form>



