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
                <?php p($l->t('(*) indicates incorrect data. Share should be removed after evaluation.')); ?>
        <br>
        <br>
        <div id="tableActions" style="margin-bottom:10px;">
            <button id="deleteSelectedShares" class="button"><?php p($l->t('Delete selected')); ?></button>
            <br><br>
            <input type="checkbox" id="pauseUpdate" class="checkbox"><label for="pauseUpdate"><?php p($l->t('Pause reload after deletion')); ?></label>
        </div>
    </div>
    <div id="noDataContainer">
        <br><br>
                <?php p($l->t('No share found')); ?>
    </div>
    <div id="loadingContainer" hidden>
        <div class="icon-loading"></div>
        <br><br>
                <?php p($l->t('Shares are being retrieved, please wait …')); ?>
    </div>
    <div id="notSecuredContainer" hidden>
        <br><br>
                <?php p($l->t('The app must be restricted to at least one specific user group in the app store. This prevents accidental exposure of the shared content to all users.')); ?>
        <br><br>
        <a href="/settings/apps/enabled/sharereview"><?php p($l->t('Click here')); ?></a>
    </div>

    <div id="exportContainer" hidden>
        <h4><?php p($l->t('On demand report')); ?></h4>
        <p>
            <button id="exportCsv" class="button"><?php p($l->t('CSV')); ?></button>
            <button id="exportPdf" class="button"><?php p($l->t('PDF')); ?></button>
        </p>
        <br><br>
        <h4><?php p($l->t('Scheduled report')); ?></h4>
        <p>
            <label for="defaultFolder"><?php p($l->t('Default folder')); ?></label>
            <input type="text" id="defaultFolder" readonly>
        </p>
        <p>
            <label for="scheduleSelect"><?php p($l->t('Schedule')); ?></label>
            <select id="scheduleSelect">
                <option value="none"><?php p($l->t('None')); ?></option>
                <option value="daily"><?php p($l->t('Daily')); ?></option>
                <option value="weekly"><?php p($l->t('Weekly')); ?></option>
                <option value="monthly"><?php p($l->t('Monthly')); ?></option>
            </select>
        </p>
        <p>
            <label for="typeSelect"><?php p($l->t('Format')); ?></label>
            <select id="typeSelect">
                <option value="pdf"><?php p($l->t('PDF')); ?></option>
                <option value="csv"><?php p($l->t('CSV')); ?></option>
            </select>
        </p>
        <p>
            <button id="saveSettings" class="button"><?php p($l->t('Save')); ?></button>
        </p>
    </div>

</div>
<div id="shareReview-loading" style="width:100%; padding: 100px 5%;" hidden>
    <div style="text-align:center; padding-top:100px" class="get-metadata icon-loading"></div>
</div>
