<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

?>
<div id="analytics-content" style="width:100%; padding: 20px 5%;">
    <h2 id="reportHeader"><?php p($l->t('Share Review')); ?></h2>
    <h3 id="reportSubHeader" hidden></h3>
    <div id="reportPlaceholder"></div>
    <div id="tableSeparatorContainer"></div>
    <table id="tableContainer"></table>
    <div id="noDataContainer" hidden>
        <?php p($l->t('No data found')); ?>
    </div>
</div>
<div id="analytics-loading" style="width:100%; padding: 100px 5%;" hidden>
    <div style="text-align:center; padding-top:100px" class="get-metadata icon-loading"></div>
</div>
