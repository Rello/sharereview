<?php
/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

?>
<div id="sharereview-content" style="width:100%; padding: 20px 5%;">
    <h2 id="reportHeader">Share Review</h2>
    <h3 id="reportSubHeader" hidden></h3>
    <div id="tableContainer" hidden>
        <table id="dataTable"></table>
        <br><br>
		<?php p($l->t('(*) indicates incorrect data. Share should be removed after evaluation.')); ?>
        <br><br>
        <input type="checkbox" id="pauseUpdate" class="checkbox"><label for="pauseUpdate"><?php p($l->t('Pause reload after deletion')); ?></label>
    </div>
    <div id="noDataContainer">
        <br><br>
		<?php p($l->t('No share found')); ?>
    </div>
    <div id="notSecuredContainer" hidden>
        <br><br>
		<?php p($l->t('The app must be restricted to at least one specific user group in the app store. This prevents accidental exposure of the shared content to all users.')); ?>
        <br><br>
        <a href="/settings/apps/enabled/sharereview"><?php p($l->t('Click here')); ?></a>
    </div>

</div>
<div id="shareReview-loading" style="width:100%; padding: 100px 5%;" hidden>
    <div style="text-align:center; padding-top:100px" class="get-metadata icon-loading"></div>
</div>
