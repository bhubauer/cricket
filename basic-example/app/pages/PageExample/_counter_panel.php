<?php
/* @var $cricket cricket\core\CricketContext */
/* @var $count int */
?>

<div style="border: 4px dashed #cccccc; width:400px;padding:20px;">
    <div>
        Count = <?= $count ?>
    </div>
    <div style="margin-top:1em;">
        <input type="button" value = "-" onclick="<?= $cricket->onclick('decrement') ?>">
        &nbsp;
        <input type="button" value = "+" onclick="<?= $cricket->onclick('increment') ?>">
    </div>
</div>
