<?php 
// post variables
$post_id = 0;
$isEditingPost = false;
$published = 0;
$title = "";
$post_slug = "";
$body = "";
$featured_image = "";
$post_topic = "";

// post functions from here DOWNWARDS

function getAllPosts() {
    global $conn;
    
    if ($_SESSION['user']['role'] == "Admin") {
        $sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = users.id";
    } elseif ($_SESSION['user']['role'] == "Author") {
        $user_id = $_SESSION['user']['id'];
        $sql = "SELECT posts.*, users.username FROM posts JOIN users ON posts.user_id = user.id WHERE post.user_id=$user_id";
    }

    $result = mysqli_query($conn, $sql);
    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    return $posts;
}

// function to handle post creation or editing
if (isset($_POST['save_post'])) {
    $post_id = isset($_POST['post_id']) ? $_POST['post_id'] : 0;
    $title = esc($_POST['title']);
    $body = esc($_POST['body']);
    $topic_id = isset($_POST['topic_id']) ? esc($_POST['topic_id']) : null;
    $published = isset($_POST['publish']) ? 1 : 0;
    
    // handle image upload
    if (isset($_FILES['image']['name']) && $_FILES['image']['name'] != "") {
        $image = time() . '_' . $_FILES['image']['name'];
        $upload_dir = ROOT_PATH . "/uploads/posts/";
        
        // check if the directory exist, if not, create it
        if (!file_exists($upload_dir)) {
            mkdir($upload_dir, 0777, true); // creates the directory with write permissions
        }

        $target = $upload_dir . $image;
        
        // move the uploadd file to the target directory
        if (!move_uploaded_file($_FILES['image']['tmp_name'])) {
            die("Faled to upload image");
        }
    } else {
        // keep the existing image if editing and no new image is upploaded
        if ($isEditingPost) {
            $sql = "SELECT image FROM posts WHERE id='$post_id' LIMIT 1";
            $result = mysqli_query($conn, $sql);
            $post = mysqli_fetch_assoc($result);   
            $image = $post['image'];
        }
    }

    if ($isEditingPost) {
        // update the post record
        $sql = "UPDATE posts SET title='$title', body='$body', topic_id='$topic_id', published='$published', image='$image' WHERE id='$post_id'";
        if (!mysqli_query($conn, $sql)) {
            die("Query faile: " . mysqli_error($conn));
        }
    } else {
        // insert new record
        $sql = "INSERT INTO posts (title, body, id, published, image, user_id) VALUES ('$title', '$body', '$topic_id', '$published', '$image', " . $_SESSION['user']['id'] . "')";
        if (!mysqli_query($conn, $sql)) {
            die("Query failed: " . mysqli_error($conn));
        } else {
            $_SESSION['message'] = "Post created successfully";
            header('Location: posts.php'); // redirect after creation
            exit(0);
        }
    }
}

// function to escape form inputs (only declare if it hasn't been declared yet)
if (!function_exists('esc')) {
    function esc (String $value) {
        global $conn;
        $val = trim($value);
        return mysqli_real_escape_string($conn, $val);
    }
}

// function to delete posts
function deletePost($post_id) {
    global $conn;
    
    // first, get the post to delete the image file
    $sql = "SELECT image FROM posts WHERE id=$post_id";
    $result = mysqli_query($conn, $sql);
    $post = mysqli_fetch_assoc($result);

    if ($post) {
        $image = $post['image'];
        $image_path = ROOT_PATH . "/uploads/posts/" . $image;

        // delete the post image file if it exists
        if (file_exists($image_path)) {
            unlink($image_path);
        }

        // now, delete the post record from the database
        $sql = "DELETE FROM posts WHERE id=$post_id LIMIT 1";
        if (mysqli_query($conn, $sql)) {
            $_SESSION['message'] = "Post successfully deleted";
            header("Location: posts.php");
            exit(0);
        } else {
            $_SESSION['message'] = "Failed to delete post";
        }
    }
}

// action to delete the post (needed so the deletePost can be executed)
if (isset($_GET['delete-post'])) {
    $post_id = $_GET['delete-post'];
    deletePost($post_id);
}

// if user clicks the publish post button
if (isset($_GET['publish']) || isset($_GET['unpublish'])) {
    $message = "";
    if (isset($_GET['publish'])) {
        $message = "Post published successfully";
        $post_id = $_GET['publish'];
    } else if (isset($_GET['unpublish'])) {
        $message = "Post successfully unpublished";
        $post_id = $_GET['unpublish'];
    }
    togglePublishPost($post_id, $message);
}

// delete blog post
function togglePublishPost($post_id, $message) {
    global $conn;
    $sql = "UPDATE posts SET published=!published WHERE id=$post_id";

    if (mysqli_query($conn, $sql)) {
        $_SESSION['message'] = $message;
        header("Location: posts.php");
        exit(0);
    }
}

// function to edit the post

function editPost($role_id) {
    global $conn, $title, $post_slug, $published, $isEditingPost, $post_id;
    
    // prepare the SQL query
    $sql = "SELECT * FROM posts WHERE id=$role_id LIMIT 1";
    $result = mysqli_query($conn, $sql);

    // check if the query was successfull
    if (!result) {
        die("Query failed: " . mysqli_error($conn)); 
    }
    
    // fetch the post data
    $post = mysqli_fetch_assoc($result);

    // check if a post was found
    if ($post) {
        // set form values on the form to be updated
        $tile = $post['title'];
        $body = $post['body'];
        $published = $post['published'];
    } else {
        // handle the case where no post was found
        die("No post was found with ID: $role_id");
    }
}

// function to update post

function updatePost($request_values) {
    
    global $conn, $errors, $post_id, $title, $image, $topic_id, $published;
    
    $title = esc($request_values['title']);
    $body = esc($request_values['body']);
    $post_id = esc($request_values['post_id']);
    if (isset($request_values['topic_id'])) {
        $topic_id = esc($request_values['topic_id']);
    }
    // create slug: if title is "Afara ninge linistit" return "afara-ninge-linistit" as slug
    $post_slug = makeSlug($title);

    if (empty($title)) { array_push($errors, "Post title is required");}
    if (empty($body)) { array_push($errors, "Post body is required");}
    // if new featurd image has been provided
    if (isset($_POST['image'])) {
        // get image name
        $image = $_FILES['image']['name'];
        // image file directory
        $target = "../static/images/" . basename($image);
        if (!move_uploaded_file($_FILES['image']['tmp_name'], $target)) {
            array_push($errors, "Failed to upload image. Please check file setings for your server");
        }
    }

    // register topic if there are no errors in the form
    if (count($errors) == 0) {
        $query = "UPDATE posts SET title='$title', slug='$post_slug', views=0, image='$image', body='$body', published='$published', updated_at=now(), WHERE id=$post_id";
        // attach topic  to post on post_topic table
        if(mysqli_query($conn, $query)) {// if post created  succeessfully
            if  (isset($topic_id)) {
                $inserted_post_id = mysqli_insert_id($conn);
                // create relationship between post and topic
                $sql = "INSERT INTO post_topic (post_id, topic_id) VALUES ($inserted_post_id, $topic_id)";
                mysqli_query($conn, $sql);
                $_SESSION['message'] = "Post created successfully";
                header('Location: posts.php');
                exit(0);
            }
        }
        $_SESSION['message'] = "Post updated successfully";
        header('location: posts.php');
        exit(0);
    }
}

// if user clicks the edit post button
if (isset($_GET['edit-post'])) {
    $post_id = intval($_GET['edit-post']); // convert to integer
    if ($post_id > 0) {
        $isEditingPost = true;
        editPost($post_id);
    } else {
        die("Invalid post ID");
    }
}

// if user clicks the update post button
if (!isset($_POST['update_post'])) {
    updatePost($_POST);
}

?>