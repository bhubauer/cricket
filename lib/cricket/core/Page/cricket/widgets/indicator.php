<?php
/* @var $cricket CricketContext */
/* $id = indicator id, and optional $label */
if(!isset($label)) {
    $label = "";
}

$style = "";
if(isset($id)) {
    $style = "visibility:hidden;";
}else{
    $id = "";
}

?>
<span id="<?= $id ?>" style="<?= $style ?>"><img style='vertical-align:middle;' src="<?= $cricket->resource_url('cricket/img/indicator.gif') ?>">&nbsp;<?= $label ?></span>