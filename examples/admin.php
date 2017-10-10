<?php

include "../vendor/autoload.php";

use SocApi\SocApi;

$db = mysqli_connect("localhost", "root", "", "test");

$sapi = new SocApi($db);

$statuses = [
	SocApi::STATUS_WAITING => "Wait for moderation",
	SocApi::STATUS_CONFIRMED => "Confirmed",
	SocApi::STATUS_UNCONFIRMED => "Unconfirmed"
]
?>
<!DOCTYPE html>
<html>
    <head>
        <meta charset="UTF-8" />
        <title>SocApi administrate</title>
        <link rel="stylesheet" href="css/admin.css" />
    </head>
    <body>
        <div class='container'>
            <button onclick='update.call(this)'>Load</button><br />
            <?php
            foreach ($statuses as $status => $title)
            {
                echo "<label><input type='checkbox' class='showType' value='$status' /> $title</label><br />";
            }

            $posts = $sapi->getManyPosts();
            foreach($posts as $post)
            {
                ?>
                <div class='post' data-status='<?=$post->status?>' data-ai='<?=$post->ai?>' style='display: none;'>
                    <img src='<?=$post->image?>' alt='' />
                    <h4>
                        <a href='<?=$post->author_url?>' target='_blank'>
                            <?=$post->username?>
                        </a>
                    </h4>
                    <p>
                        <?=$post->info?>
                    </p>
                    <select class='stateChanger'>
                        <?php
                        foreach ($statuses as $status => $title)
                        {
                            echo "<option value='$status' " . ($post->status==$i++ ? "selected" : "") . " />$title</option>";
                        }
                        ?>
                    </select>
                </div>
                <?php
            }
            ?>
        </div>
        <script src='js/admin.js'></script>
    </body>
</html>
<?php
$db->close();
