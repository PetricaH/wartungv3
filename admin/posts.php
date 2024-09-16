<?php  
include('../config.php');
include(ROOT_PATH . '/admin/includes/admin_functions.php');
include(ROOT_PATH . '/admin/includes/post_functions.php');
include(ROOT_PATH . '/admin/includes/head_section.php');

// initialize variables
$isEditingPost = false;
$title = $body = '';
$published = false;
$post_id = 0;

// check if an edit action is required
if (isset($_GET['edit-post'])) {
    $post_id = intval($_GET['edit-post']); // ensure post_id is an integer
    if ($post_id > 0) {
        editPost($post_id);
        $isEditingPost = true;
    } else {
        die("Invalid post ID");
    }
}

// get all posts for display
$posts = getAllPosts();
?>
<title>Admin | Manage Posts</title>
</head>
<body>
    <!-- Admin navbar -->
    <?php inlcude(ROOT_PATH . '/admin/includes/navbar.php'); ?>
    <div class="container content">
        <!-- left side menu -->
        <?php include(ROOT_PATH . '/admin/includes/menu.php'); ?>
        
        <!-- display notification messages -->
        <?php include(ROOT_PATH . '/includes/messages.php'); ?>

        <!-- midddle form to create and edit posts -->
        <?php if ($isEditingPost): ?>
            <div class="action create-post-div">
                <h1 class="page-title">Edit Post</h1>
                <form method="post" enctype="multipart/form-data" action="<?php echo BASE_URL . 'admin/create_post.php'; ?>">
                    <!-- validation errors for the form  -->
                    <?php include(ROOT_PATH . '/includes/errors.php'); ?>
                    
                    <!-- if editing post, the ID is required to identify that post -->
                    <input type="hidden" name="post_id" value="<?php echo htmlspecialchars($post_id); ?>">
                    <input type="text" name="title" value="<?php echo htmlspecialchars($title); ?>" placeholder="Title" required>
                    <input type="file" name="image">
                    <textarea name="body" id="body" cols="30" rows="10"><?php echo htmlspecialchars($body); ?></textarea>

                    <?php $topics = getAllTopics(); ?>
                    <select name="topic_id">
                        <option value="" selected disabled>Choose Topic</option>
                        <?php foreach ($topics as $topic): ?>
                            <option value="<?php echo htmlspecialchars($topic['id']); ?>" <?php if (isset($topic_id) && $topic_id == $topic['id']) echo 'selected'; ?>>
                                <?php echo htmlspecialchars($topic['name']); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>

                    <!-- only admin users can view the publish input field -->
                    <?php if ($_SESSION['user']['role'] == "Admin"): ?>
                        <label for="publish">
                            Publish
                            <input type="checkbox" value="1" name="publish" <?php if ($published) echo 'checked'; ?>>
                        </label>
                    <?php endif; ?>
                </form>
            </div>
    </div>
</body>