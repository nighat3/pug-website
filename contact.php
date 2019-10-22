<!-- Used Lab2 in INFO 2300 as a reference. The Lab is created by Professor Kyle Harms.--->

<?php
include("includes/init.php");

$name = '';
$email = '';
$message = '';

if (isset($_POST['submit'])) {

    $submit = TRUE;
    $hidden = "name_error hidden";
    $hidden2 = "email_error hidden";
    $hidden3 = "message_error hidden";

    $name = $_POST['contact_name'];
    $email = $_POST['contact_email'];
    $message = $_POST['contact_message'];

    if (trim($name) == '') {
      $submit = FALSE;
      $hidden = "name_error";

    }

    if (trim($email) == ''){
      $submit = FALSE;
      $hidden2 = "email_error";

    }

    if (trim($message) == ''){
      $submit = FALSE;
      $hidden3 = "message_error";

    }

}else{
    $hidden = "form_error hidden";
    $hidden2 = "email_error hidden";
    $hidden3 = "message_error hidden";

}
?>

<!DOCTYPE html>

<html>
<?php include("includes/head.php");?>

<body>
    <?php include("includes/header.php");?>

    <div id="form-content">
        <h2 id = "form-title" class="about-titles"> Reach Out to Us! </h2>

        <?php
      if ( isset($submit) && $submit ) { ?>

        <h3 id = "thanks-message">Thank you, <?php echo htmlspecialchars($name);?>! Your Response has been Recorded.</h4>
        <ul>
                <li class = "form-submit">Name:  <?php echo( $name);?></li>
                <li class = "form-submit"> Email:  <?php echo( $email);?></li>
                <li class = "form-submit">Message: <?php echo($message);?></li>
        </ul>

        <?php } else { ?>

        <p>Please share any questions, concerns, or suggestions with us using this form.</p>

        <form id="contact-form" method="post" action="contact.php">

            <p class="<?php echo $hidden;?>">Please provide your name.</p>
            <p>
                <label for="name_field">Name*:</label>
                <input id="name_field" type="text" name='contact_name' value="<?php echo $name; ?>" />
            </p>

            <p class="<?php echo $hidden2;?>">Please provide your email.</p>
            <p>
                <label for="email_field">Email*:</label>
                <input id="email_field" type="text" name='contact_email' value="<?php echo $email; ?>" />
            </p>

            <p>
                <label for="about_user">About You:</label>
                <select name="about-user">
                    <option value="current">Current Pug Parent</option>
                    <option value="future">Future Pug Parent</option>
                    <option value="browsing">Just Browsing</option>
                </select>
            </p>

            <p>
                <label for="user-review">Did you find this website useful?</label>
                <select name="user-review">
                    <option value="current">Very Useful!</option>
                    <option value="future">Somewhat Useful</option>
                    <option value="browsing">Not Useful</option>
                    <option value="browsing">N/A</option>
                </select>
            </p>

            <p class="<?php echo $hidden3;?>">Please write us a message!</p>
            <p>
                <label for="message_field">Message*:</label>
                <textarea id="message_field" type="text" name="contact_message"> <?php echo $message; ?></textarea>
            </p>
            <p id = "submit-button">
            <input type="submit" name='submit' value="SUBMIT" />
        </p>
        </form>

        <?php } ?>

    </div>

    <?php include("includes/footer.php");?>


</body>

</html>
