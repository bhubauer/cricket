
<?php
/* @var $thisTable Table */
/* @var $cricket CricketContext */

/*
 * DESC PARAMS:
    height - [optional] height in pixels.  makes a fixed height scrollable table.
    highlightRows - [optional] if should highlight rows and call action_select.  default false.
    useIDinRowClickIndicator - [optional] if true action_select activates "ind_select_row_{$id}", otherwise just "ind_select_row" default false
    multiSort - [optional] if true, table supports multi sort feature.  default false
    row_attributes - [optional] either an array of attributes to set on the table row, or a function($item,$proposedRowID,$index) returns additional attributes to add to the <tr>  if it returns and "id", that is used as a prefix
    create_row_id - [optional] function($item,$row_id_prefix,$currentID) returns row ID
    items - [required] items for table

    $tr - if this variable is defined, its a function $tr($item,$index,$cricket,$rowID,$rowClass,$row_attrs,$columns)
    This *should* be in the desc
 * 
 * empty_content - [optional] function.  if table contains no items, a single spanned row is created and this functio is called to provide content

col params:
	cssClass - [optional]  css class for <td>
	allowClick - [optional] if highlightRows is active, setting this to false with default that for this <td> default true
	width - [required] width of <td>  percent or px
	td - [required] function($item,$index,$cricket) outputs <td> contents


 */
?>


<?php
    global $INLINE;

    $thisTable = $cricket->getComponent();

    $height = $thisTable->scrollingHeight;
    if(empty($thisTable->pageSize) && $height === null) {
        $height = isset($desc['height']) ? $desc['height'] : null;
    }

    $highlightRows = isset($desc['highlightRows']) ? $desc['highlightRows'] : false;
    $useIDinRowClickIndicator = isset($desc['useIDinRowClickIndicator']) ? $desc['useIDinRowClickIndicator'] : false;
	$multiSort = isset($desc['multiSort']) ? $desc['multiSort'] : false;
    $style = $height !== null ? "" : "style='overflow-x:visible;overflow-y:visible;'";
    
    $emptyContent = isset($desc['empty_content']) ? $desc['empty_content'] : null;
    
?>

<div class="table-fixed">
    <div class="table-head" <?= $style ?>>
        <table class="list">
            <tr>
                <?php
                    foreach($columns as $col) {
                        $sortBy = isset($col['sort']) ? $col['sort'] : null;
                        $onClick = "";
                        $sortClass = "";

                        if($sortBy !== null) {
                            $sortUrl = $thisTable->getActionUrl("sort");
							if ($multiSort)
                            	$onClick = "onclick = 'jQuery(this).addClass(\"sort_pending\");cricket_ajax(\"" . $sortUrl . "\",{sort:\"" . $sortBy . "\", shift:event.shiftKey},\"ind_sort\");'";
							else 
								$onClick = "onclick = 'jQuery(this).addClass(\"sort_pending\");cricket_ajax(\"" . $sortUrl . "\",{sort:\"" . $sortBy . "\", shift:false},\"ind_sort\");'";
								
							
							$sortClass = '';
							$sortCounter = 1;
                            $currentSort = $thisTable->getSort();
							while ($currentSort) {
								if ($currentSort->column == $sortBy) {
									$sortClass = ($currentSort->desc) ? "sort_desc" : "sort_asc";
									break;
								}
								$currentSort = $currentSort->nextSort;
								$sortCounter +=1 ;
							}
                        }
						
						$sortCounter = ($sortClass) ? $sortCounter : '';

                        if (isset($th)) {
                            $th($sortClass, $col, $onClick);
                        } else {
                        	$showSortCounter = ($multiSort) ? "<div style='float:right;font-size:75%'>$sortCounter</div>" : '';
                            echo "<th class = '" . $sortClass . "' width='" . $col['width'] . "' " . $onClick . ">" . $col['header'] . "$showSortCounter</th>";
                        }
                    }
                ?>
            </tr>
        </table>
    </div>
    <?php
        $style = $height === null ? "overflow-x:visible;overflow-y:visible;" : "position:relative;height: " . $height . "px;";
    ?>
    <div class="table-body" style="<?= $style ?>" >
        <table class="list">
            <?php
                $desc = array_change_key_case($desc);
                
                $row_attrs_func = false;
                $row_attrs = false;
                
                if (array_key_exists('row_attributes', $desc)) {
                    if (is_callable($desc['row_attributes'])) {
                        $row_attrs_func = $desc['row_attributes'];
                    } else if (is_array($desc['row_attributes'])) {
                        $row_attrs = array_change_key_case($desc['row_attributes']);
                    }
                }
                
                $createRowIDFunc = isset($desc['create_row_id']) ? $desc['create_row_id'] : function($item,$inPrefix,$itemID) { return $inPrefix . "_" . $itemID;};
                
                if(count($desc['items']) == 0 && $emptyContent != null) {
                    echo "<tr><td  colspan = '{$INLINE(count($columns))}'>";
                    $emptyContent();
                    echo "</td></tr>";
                }else{
                    $index = 0;
                    foreach($desc['items'] as $item) {
                        $currentID = null;
                        if(is_object($item)) {
                            if(isset($item->id)) {
                                $currentID = $item->id;
                            }else{
                                if(method_exists($item, "id")) {
                                    $currentID = $item->id();
                                } else {
                                    if(method_exists($item, "getId")) {
                                        $currentID = $item->getId();
                                    }
                                }
                            }
                        }else{
                            if(isset($item['id'])) {
                                $currentID = $item['id'];
                            }
                        }

                        if($currentID === null) {
                            $currentID = $index;
                        }


                        if ($row_attrs_func) {
                            $proposedRowID = "row_" . $thisTable->getId() . "_" . $currentID;
                            $row_attrs = array_change_key_case($row_attrs_func($item,$proposedRowID,$index));
                        }

                        if ( ! empty($row_attrs)) {
                            $row_classes = array_key_exists('class', $row_attrs) ? (is_array($row_attrs['class']) ? $row_attrs['class'] : array($row_attrs['class'])) : array();
                            $row_id_base = array_key_exists('id', $row_attrs) ? $row_attrs['id'] : 'row_';
                        } else {
                            $row_classes = array();
                            $row_id_base = 'row_';
                        }

                        $rowID = $createRowIDFunc($item,$row_id_base . $thisTable->getId(),$currentID);
                        if($index % 2 == 0) {
                            $row_classes[] = "alt";
                        }

                        $rowClass = implode(" ", $row_classes);

                        $attrs = "";

                        if ( ! empty($row_attrs)) {
                            foreach($row_attrs as $attr => $value) {
                                if (($attr == 'id') || ($attr == 'class')) {
                                    continue;
                                }

                                if ($attrs != "") {
                                    $attrs .= " ";
                                }

                                $attrs .= "{$attr}=\"{$value}\"";
                            }
                        }

                        if(isset($tr)) {
                            $tr($item,$index,$cricket,$rowID,$rowClass,$row_attrs,$columns);
                        }else{
                            echo "<tr id='$rowID' class='$rowClass' {$attrs}>";
                            foreach($columns as $col) {
                                $cssClass = isset($col['cssClass']) ? $col['cssClass'] : "";

                                $onClick = "";
                                if($highlightRows) {
                                    $allowClick = isset($col['allowClick']) ? $col['allowClick'] : true;
                                    if($allowClick) {
                                        $selectRowUrl = $thisTable->getActionUrl("select");
                                        $onClick = "onclick='cricket_click_row(\"" . $thisTable->getId() . "\",\"" . $selectRowUrl . "\",\"" . $rowID . "\",\"" . $currentID . "\",\"" . $useIDinRowClickIndicator . "\")'";
                                    }
                                }

                                $tdAttribs = "class='" . $cssClass . "' width='" . $col['width'] . "' " . $onClick;

                                if(isset($col['td2'])) {
                                    $col['td2']($item,$index,$tdAttribs);
                                }else{
                                    echo "<td $tdAttribs>";
                                    $col['td']($item,$index,$cricket);
                                    echo "</td>";
                                }
                            }

                            echo "</tr>";
                        }

                        $index ++;
                    }
                }
            ?>
        </table>
    </div>
</div>
<?php
    if(!empty($thisTable->pageSize)) {
        /* @var $r RequestContext */
        $r = $cricket->getRequest();
        $pageTotal = $r->getAttribute("pageTotal");
        $pageStart = $r->getAttribute("pageStart");
        $pageEnd = $r->getAttribute("pageEnd");
        $pageNext = $r->getAttribute("pageNext");
        $pagePrev = $r->getAttribute("pagePrev");
        $pageSize = $r->getAttribute("pageSize");
        $pageCount = $r->getAttribute("pageCount");
        $pageCurrent = $r->getAttribute("pageCurrent");
        $pages = $r->getAttribute("pages");
        $adjacents = $r->getAttribute("adjacents");
        
        $pager = $tpl->widget("cricket/widgets/pagination.php",array(
            'page_prev' => $pagePrev,
            'page_next' => $pageNext,
            'page_start' => $pageStart,
            'page_end' => $pageEnd,
            'page_total' => $pageTotal,
            'page_size' => $pageSize,
            'page_count' => $pageCount,
            'page_current' => $pageCurrent,
            'pages' => $pages,
            'adjacents' => $adjacents,
            'page_record_name' => $thisTable->getRecordLabel($pageTotal),
        ));
        $pager->end();
    }
?>