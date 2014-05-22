<?php
/* @var $cricket cricket\core\CricketContext */
/* @var $tpl cricket\core\TemplateInheritanceContext */
?>

<p>
    <?= $question ?>
</p>
<div style="margin-top:1em;text-align:right;">
    <?php
        $indicatorID = $cricket->componentID('ind_submit');
        echo $cricket->indicator($indicatorID);
    ?>
    <?php foreach($buttons as $index => /* @var $b ConfirmationButton */ $b): ?>
        <input type="button" value="<?= $b->label ?>" onclick="<?= $cricket->call_attr('button',array('index' => $index),$indicatorID) ?>">&nbsp;
    <?php endforeach; ?>
</div>

