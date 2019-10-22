<?php

// check current php version to ensure it meets 2300's requirements
function check_php_version()
{
  if (version_compare(phpversion(), '7.0', '<')) {
    define(VERSION_MESSAGE, "PHP version 7.0 or higher is required for 2300. Make sure you have installed PHP 7 on your computer and have set the correct PHP path in VS Code.");
    echo VERSION_MESSAGE;
    throw VERSION_MESSAGE;
  }
}
check_php_version();

function config_php_errors()
{
  ini_set('display_startup_errors', 1);
  ini_set('display_errors', 0);
  error_reporting(E_ALL);
}
config_php_errors();

// open connection to database
function open_or_init_sqlite_db($db_filename, $init_sql_filename)
{
  if (!file_exists($db_filename)) {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    if (file_exists($init_sql_filename)) {
      $db_init_sql = file_get_contents($init_sql_filename);
      try {
        $result = $db->exec($db_init_sql);
        if ($result) {
          return $db;
        }
      } catch (PDOException $exception) {
        // If we had an error, then the DB did not initialize properly,
        // so let's delete it!
        unlink($db_filename);
        throw $exception;
      }
    } else {
      unlink($db_filename);
    }
  } else {
    $db = new PDO('sqlite:' . $db_filename);
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    return $db;
  }
  return NULL;
}

function open_sqlite_db($db_filename)
{
  $db = new PDO('sqlite:' . $db_filename);
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
  return $db;
}

function exec_sql_query($db, $sql, $params = array())
{
  $query = $db->prepare($sql);
  if ($query and $query->execute($params)) {
    return $query;
  }
  return NULL;
}

$current_file = basename($_SERVER['PHP_SELF']);

$db = open_or_init_sqlite_db("secure/gallery.sqlite", "secure/init.sql");

//Login and Logout
// (Professor Kyle Harms) Info 2300 - Lab 8 init.php
// I am using Professor's Harms' Lab 8 code as a reference to implement the following portion.

define('SESSION_COOKIE_DURATION', 60 * 60 * 1);

$sess_messages = array();

//My interpretation: This function checks username and password and if both are valid, generates a timed session and adds it to the database. If invalid, this function returns an error. Checks the username by checking if user exists  in database. If the results of the query are not NULL, then a user must exist and then the function proceeds to check password. If the password matches the init.sql hashed password, function creates a new session and adds it to the database.
function log_in($username, $password)
{
  global $current_user;
  global $sess_messages;
  global $db;

  if (isset($username) && isset($password)) {

    $query = "SELECT * FROM users WHERE username = :username;";
    $params  = array(':username' => $username);

    $result = exec_sql_query($db, $query, $params)->fetchAll();

    if ($result) {
      $account  = $result[0];

      //Does the password match the hashed password in init.sql?
      if (password_verify($password, $account['password'])) {
        $sess = session_create_id();

        //Add the session to the database.
        $query = "INSERT INTO sessions (user_id, session) VALUES (:user_id, :session);";

        $params  =  array(':user_id' => $account['id'], ':session' => $sess);

        $result = exec_sql_query($db, $query, $params);

        if ($result) {

          setcookie("session",  $sess, time() + SESSION_COOKIE_DURATION);

          $current_user = $account;
          return  $current_user;
        } else {
          //Error message if query results returned NULL.
          array_push($sess_messages, "Failed Login Attempt.");
        }
      } else {
        //Error message is password was not verified.
        array_push($sess_messages, "Invalid username/password.");
      }
    } else {

      //Error message if username is not verified.
      array_push($sess_messages, "Invalid username/password.");
    }
  } else {
    //Error message if usernmae and password are not set.
    array_push($sess_messages, "Invalid username/password.");
  }
  $current_user = NULL;
  return NULL;
}


//This function searches for a given user in the database and returns that user's record (otherwise returning NULL).
function find_user($user_id)
{
  global $db;

  $query = "SELECT * FROM users WHERE id = :user_id;";
  $params = array(':user_id' => $user_id);

  //Return the results of our query  which searches for a user.
  $result = exec_sql_query($db, $query, $params)->fetchAll();

  //A user must exist if the query returned a non-NULL result.
  if ($result) {
    return $result[0];
  }
  return NULL;
}

//This function checks to see whether or not a user has previously logged in (perhaps through a different device). If yes, then it finds the session that was already created and returns it. If it does not find the session, function returns NULL. This function is called in the next function.
function find_session($session)
{
  global $db;
  if (isset($session)){
    $query = "SELECT * FROM sessions  WHERE session = :session;";
    $params = array(':session' => $session);

    $result = exec_sql_query($db, $query, $params)->fetchAll();

  //A session must exist if the query returns a non-NULL result.
  if ($result) {
    return $result[0];
  }
}
  return NULL;
}

//This function extends a pre-existing session if there is a new login from the same user. It extends the time of the cookie duration if it successfully finds a session. This function finds a previous session by calling the function we declared above (which finds a session).
function  session_login()
{
  global $db;
  global $current_user;

  if (isset($_COOKIE["session"])) {
    $sess = $_COOKIE["session"];

    $session_result = find_session($sess);

    //If a session exists, then find the user corresponding to this pre-existing session and make it the current user.
    if (isset($session_result)) {
      $current_user = find_user($session_result['user_id']);

      //Add time to the session.
      setcookie("session", $sess, time() +  SESSION_COOKIE_DURATION);
      return  $current_user;
    }
  }
  $current_user = NULL;
  return NULL;
}

//This function returns true if the user is logged in (meaning that $currentuser is not NULL) and returns false is the user is not logged in.
function is_user_logged_in()
{
  global $current_user;
  return ($current_user != null);
}

//This logout function ends the cookie and sets the current user var to NULL.
function log_out()
{
  global $current_user;

  setcookie('session', '', time() - SESSION_COOKIE_DURATION);
  $current_user = NULL;
}

//Trim username and password and log the user in when the Login button is pressed. Check if a session already exists. If yes, continue that session. If not, then log the user in and create a session.
if (isset($_POST['login']) && isset($_POST['username']) && isset($_POST['password'])) {
  $username  = trim($_POST['username']);
  $password  = trim($_POST['password']);

  log_in($username, $password);
} else {
  session_login();
}

//
if (isset($current_user) && (isset($_GET['logout']) || isset($POST['logout']))) {
  log_out();
}

?>
