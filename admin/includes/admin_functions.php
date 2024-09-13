<?php

// admin user variables
use function PHPSTORM_META\type;

$admin_id = 0;
$isEditingUser = false;
$username = "";
$role = "";
$email = "";
// general variables
$errors = [];

// topic variables

$topic_id = 0;
$isEditingTopic = false;
$topic_name = "";

// admin user actions 

// if user clicks the create admin button
if (isset($_POST['create_admin'])) {
    createAdmin($_POST);
}

// if user clicks the edit admin button
if (isset($_GET['edit-admin'])) {
    $isEditingUser = true;
    $admin_id = $_GET['edit-admin'];
    editAdmin($admin_id);
} 

// if user clicks the update admin button

if (isset($_POST['update_admin'])) {
    updateAdmin($_POST);
}

// if user clicks the delete  admin button

if (isset($_GET['delete-admin'])) {
    $admin_id = $_GET['delete-admin'];
    deleteAdmin($admin_id);
}

// TOPIC ACTIONS FROM HERE DOWNWARDS

// if user clicks the create topic button 
if (isset($_POST['create_topic'])) {
    createTopic($_POST);
}

// if user clicks the edit topic button

if (isset($_GET['edit-topic'])) {
    $isEditingTopic = true;
    $topic_id = $_GET['edit-topic'];
    editTopic($topic_id);
}

// if user clicks the updae topic button

if (isset($_POST['update_topic'])) {
    updateTopic($_POST);
}

// if user clicks the delete topic button

if (isset($_GET['delete-topic'])) {
    $topic_id = $_GET['delete-topic'];
    deleteTopic($topic_id);
}

// ADMIN USERS FUNCTION FROM HERE DOWNWARDS

function createAdmin($request_values) {
    global $conn, $errors, $role, $username, $email;
    $username = esc($request_values['username']);
    $email = esc($request_values['email']);
    $password = esc($request_values['password']);
    $passwordConfirmation = esc($request_values['passwordConfirmation']);

    if(isset($request_values['role'])) {
        $role = esc($request_values['role']);
    }

    // form validation: ensure that the form is correctly filled
    if (empty($username)) {array_push($errors, "username field cannot be empty");}
    if (empty($email)) {array_push($errors, "email field cannot be empty");}
    if (empty($role)) {array_push($errors, "role is required for admin users");}
    if (empty($password)) {array_push($errors, "password is required");}
    if ($password != $passwordConfirmation) array_push($errors, "the two passwords do not match");
    // ensure that no user is registered twice
    // the email and usernames should be unique
    $user_check_query = "SELECT * FROM users WHERE username='$username' OR email='$email' LIMIT 1";
    $result = mysqli_query($conn, $user_check_query);
    $user = mysqli_fetch_assoc($result);
    if ($user) {
        if ($user['username'] === $username) {
            array_push($errors, "username already exists");
        }
        if ($user['email'] === $email) {
            array_push($errors, "email already exists");
        }
    }

    // register user if there are not errors in the form
    if (count($errors) == 0) {
        $password = md5($password); // encript the password before saving in the database
        $query = "INSERT INTO users (username, email, role, password, created_at, updated_at)
                    VALUES('$username', '$email', '$role', '$password', now(), now())";
        mysqli_query($conn, $query);

        $_SESSION['message'] = "admin user created successfully";
        header('Location: users.php');
        exit(0);
    }
} 

// delete admin user function
function deleteAdmin($admin_id) {
    global $conn;
    $sql = "DELETE FROM users WHERE id=$admin_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "user successfully deleted";
        header('Location: users.php');
        exit(0);
    }
}

// edit admin function
function editAdmin($admin_id){
    global $conn, $username, $role, $isEditingUser, $admin_id, $email;

    $sql = "SELECT * FROM users WHERE id=$admin_id LIMIT 1";
    $result = mysqli_query($conn, $sql);
    $admin = mysqli_fetch_assoc($result);

    // set the form values ($username and $email) on the form to be updated 
    $username = $admin['username'];
    $email = $admin['email'];
}

// receives admin requests from form and updates in database  
function updateAdmin($request_values) {
    global $conn, $errors, $role, $username, $isEditingUser, $admin_id, $email;
    // get id of the admin to be updated
    $admin_id = $request_values['admin_id'];
    // set edit state to false
    $isEditingUser = false;
}

// returns all the admin users and their corresponding roles
function getAdminUsers() {
    global $conn, $roles;
    $sql = "SELECT * FROM users WHERE role IS NOT NULL";
    $result = mysqli_query($conn, $sql);
    $users = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $users;
}

// escape from the submitted value, hence, preventing SQL injection
function est(String $value) {
    // bring the global db connect object into function
    global $conn;
    // remove the empty space surrounding the string
    $val = trim($value);
    $val = mysqli_real_escape_string($conn, $value);
    return $val;
}

// receives a string like 'Some Sample String'
// and returns 'some-sample-string'
function makeSlug(String $string) {
    $string = strtolower($string);
    $slug = preg_replace('/[^A-Za-z0-9-]+/', '-', $string);
    return $slug;
}

// TOPIC FUNCTIONS FROM HERE DOWNWARDS 

// get all topics from DB
function getAllTopics() {
    global $conn;
    $sql = "SELECT * FROM topics";
    $result = mysqli_query($conn, $sql);
    $topics = myslqi_fetch_all($result, MYSQLI_ASSOC);
    return $topics;
}

// function to create topic
function createTopic() {
    global $conn, $errors, $topic_name;
    $topic_name = esc($request_values['topic_name']);
    // create slug: if topic is "life advice", return "life-advice" as slug
    $topic_slug = makeSlug($topic_name);
    // validate form
    if (empty($topic_name)) {
        array_push($errors, "topic already exists");
    }
    // register topicc if there are no errors in the form
    if (count($errors) == 0) {
        $query = "INSERT INTO topics (name, slug) VALUE('$topic_name', '$topic_slug')";
        mysqli_query($conn, $query);

        $_SESSION['message'] = "topic created successfully";
        header('location: topics.php');
        exit(0);
    }
}

// takes topic id as parameter
// fetches the topic from database
// sets topci fields on the form editing
function editTopic($topic_id) {
    global $conn, $topic_name, $isEditingTopic, $topic_id;
    $sql = mysqli_querry($conn, $sql);
    $topic = mysqli_fetch_assoc($result);
    // set form values ($topic_name) on the form to be updated
    $topic_name = $topic['name'];
}

// function to update topic
function updateTopic($request_values) {
    global $conn, $topic_name, $topic_id;
    $topic_name = esc($request_values['topic_name']);
    $topic_id = esc($request_values['topic_id']);
    // create slug: if topic "life-advice" as slug
    $topic_slug = makeSlug($topic_name);
    // validate form
    if (empty($topic_name)) {
        array_push($errors, "topic name required");
    }
    // register topic if there are no errors in the form
    if (count($errors) === 0) {
        $query = "UPDATE topics SET name='$topic_name', slug='$topic_slug' WHERE id=$topic_id";
        mysqli_query($conn, $query);

        $_SESSION['message'] = "topic updated successfully";
        header('Location: topics.php');
        exit(0);
    }
}

// function to delete topic
function deleteTopic($topic_id) {
    global $conn;
    $sql = "DELETE FROM topics WHERE id=$topic_id";
    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = "topic successfully deleted";
        header('Location: topics.php');
        exit(0);
    }
}

?>