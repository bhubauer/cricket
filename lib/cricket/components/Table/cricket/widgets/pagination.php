
<?php
/* @var $cricket CricketContext */
/* @var $tpl TemplateInheritanceContext */
/*
 * Params:
 *      $page_prev -- boolean has prev page
 *      $page_next -- boolean has next page
 *      $page_start -- int start number (one based)
 *      $page_end -- int end number (inclusive)
 *      $page_total - int total number of records
 *      $page_size - int number of records per page
 *      $page_count - int number of pages
 *      $page_current - int the current page
 *      $pages - mixed[] will either be an int for the page number or an ellipsis to indicate there is a large gap of pages
 *      $adjacents - int number of pages that should surround the current page in the pagination gadget
 *      $page_record_name - label for number of records
 * 
 */

$prevIndicatorID = "id_prev_indicator_" . $cricket->getComponent()->getId();
$nextIndicatorID = "id_next_indicator_" . $cricket->getComponent()->getId();
?>

<table style="margin-top: 1em;width: 100%; border-spacing: 0;">
    <tr>
        <td style="text-align: center;">
            <div class="pagination">
                <?php if($page_prev): ?>
                    <?php $i = $tpl->widget("cricket/widgets/indicator.php",array('id' => $prevIndicatorID)); $i->end() ?>
                    <a href="<?= $cricket->call_href("prev_page", null, $prevIndicatorID) ?>">« previous</a>
                <?php else: ?>
                    <span class="disabled">« previous</span>
                <?php endif; ?>
                <?php
                foreach($pages as $page) {
                    if (is_int($page)) {
                        if ($page == $page_current) {
                            echo "<span class=\"current\">$page</span>";
                        } else {
                            $href = $cricket->call_href("goto_page", array('page' => $page), $nextIndicatorID);
                            echo "<a href=\"$href\">$page</a>";
                        }
                    } else {
                        echo $page;
                    }
                }
                ?>
                <?php if($page_next): ?>
                    <a href="<?= $cricket->call_href("next_page", null, $nextIndicatorID) ?>">next »</a>
                    &nbsp;
                    <?php $i = $tpl->widget("cricket/widgets/indicator.php",array("id" => $nextIndicatorID)); $i->end() ?>
                <?php else: ?>
                    <span class="disabled">next »</span>
                <?php endif; ?>
            </div>
            Showing <?= $page_start ?> - <?= $page_end ?> of <?= $page_total ?> <?= $page_record_name ?>
        </td>
    </tr>
</table>
