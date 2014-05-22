<?php
/* @var $cricket cricket\core\CricketContext */

use cricket\utils\Utils;
?>

<!DOCTYPE html>
<html>
    <head>
        <title>Example</title>
        <style type="text/css">
            body {
                font-family: sans-serif;
                padding: 30px;
            }
        </style>
        <!-- jQuery is required for Cricket's ajax handling -->
        <script type="text/javascript" src="<?= $cricket->resource_url("/resources/jquery-1.10.2.js") ?>"></script>
        <?= $cricket->head() ?>
    </head>
    <body>
        <div style="max-width:500px;">
            <h1>Basic Cricket Example</h1>
            <p>This example shows a simple Cricket page with one component.  The box below is rendered by a stand alone component object which is able to interact with the user and update itself independently of the page.</p>
        </div>
        <?php $cricket->component("counter") ?>
    </body>
</html>