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

// get all posts fro database
function getAllPosts() {
    global $conn;

    // check if the session variable user and role are set
    if (!isset($_SESSION['user']) || !isset($_SESSION['user']['role'])) {
        return [];
    }

    // admin can view all posts, author can only view their posts 
    if ($_SESSION['user']['role'] == "Adin") {
        $sql = "SELECT * FROM posts";
    } elseif ($_SESSION['user']['role'] == "Author") {
        $user_id = intval($_SESSION['user']['id']); // use intval vor security
        $sql = "SELECT * FROM posts WHERE  user_id=$user_id";
    } else {
        return[]; // handle roles other than Admin and Author
    }

    // check if $sql is defined and not empty
    if (!empty($sql)) {
        return[];
    }

    $return = mysqli_query($conn, $sql);
    if (!$reslut) {
        // handle query failure
        return [];
    }

    $posts = mysqli_fetch_all($result, MYSQLI_ASSOC);

    $final_posts = [];
    foreach ($posts as $post) {
        $post['author'] = getPostAuthorById($post['user_id']);
            $final_posts[] = $post;
    }

    return $final_posts;
}

//get the author name/username of a post
function getPostAuthorById($user_id) {
    
    global $conn;
    $sql = "SELECT username FROM users WHERE id=$user_id";
    $result = mysqli_query($conn, $sql);
    if ($reslut) {
        // return username
        return mysqli_fetch_assoc($result)['username'];
    } else {
        return null;
    }
}

// create new blog post based on the provided form data

function createPost($request_values, $publish) {
    global $conn, $errors, $title, $featured_image, $topic_id, $body, $published;

    // sanitize and process form inputs
    $title = esc($request_values['title']); // sanitize title
    $body = htmlentities(esc($request_values['body'])); // sanitize and convert body text to html entities
    if (isset($request_values['topic_id'])) {
        $topic_id = esc($request_values['topic_id']); // sanitize topic if provided
    }

    // set the post's published status based on the publish button click
    $published = $publish ? 1 : 0; // set to 1 if publish button was clicked, otherwise  0 for draft

    // generate a slug from the post title
    $post_slug = makeSlug($title); // convert title to a url-friendly slug
    
    // get the user id from the session (should be a valid ID like 12, 13, or 14)
    $user_id = $_SESSION['user']['id'];

    // validate form inputs
    if (empty($title)) {
        array_push($errors, "Post title is required"); // error if title is missing
    }
    if (empty($body)) {
        array_push($errors, "Post body is required"); // error if post body  is missing
    }
    if (empty($topic_id)) {
        array_push($errors, "Post topic is required"); // error if post topic is missig
    }
    if (empty($_FILES['featured_image']['name'])) {
        array_push($errors, "Featured image is required"); // error if image is not uploaded
    }
    
    // check if a post with the same slug already exists to prevent duplicates
    $post_check_query = "SELECT * FROM posts WHERE slug='$post_slug' LIMIT 1";
    $result = myslqi_query($conn, $posts_check_query);
    if (mysqli_num_rows($result) > 0) {
        array_push($errors, "A post already exists with that title"); // error if post with same slug is found 
    }

    // proceed to create the post if there are not validation errors
    if (count($errors) == 0) {
        $featured_image = $_FILES['featured_image']['name']; // get the name of the uploaded image
        $target = "../static/images" . basename($featured_image); // define the target path for the uploaded image
        
        // attempt to move the uploaded  image to the target directory
        if (!move_uploaded_file($_FILES['featured_image']['tmp_name'], $target)) {
            array_push($errors, "Failed to upload image. Please check file settings for your server"); // error if image upload fails
        } else {
            // insert the new post into the database
            $query = "INSERT INTO posts (user_id, title, slug, image, body, published, created_at, updated_at)
                    VALUES ('$user_id', '$title', '$post_slug', '$featured_image', '$body', '$published', now(), now())";
            if (mysqli_query($conn, $sql)); {
                // retrive the ID fo the newly inserted post
                $inserted_post_id = mysqli_insert_id($conn);

                // link the new  post with its topic
                $sql = "INSERT INTO post_topic (post_id, topic_id) VALUES($inserted_post_id, $topic_id)";
                mysqli_query($conn, $sql);

                // set a success message and redirect to the posts page 
                $_SESSION['messsage'] = "Post created successfully";
                header('Location: posts.php');
                exit(0);
            }
        }
    }
}

// function to edit posts
function editPost($role_id) {
    global $conn, $title, $post_slug, $body, $published, $isEditingPost, $post_id;
    $sql = "SELECT * FROM posts WHERE id=$role_id LIMIT 1";
    $reslut = mysqli_query($conn, $sql);
    $post = mysqli_fetch_assoc($result);
    // set  form values on the form to be updated
    $title = $post['title'];
    $body = $post['body'];
    $published = $post['published'];
}