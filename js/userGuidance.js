/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/** global: OCA */
/** global: OCP */
/** global: OC */
'use strict';

if (!OCA.ShareReview) {
    /**
     * @namespace
     */
    OCA.ShareReview = {};
}


 /* @namespace OCA.ShareReview.Notification
 */
OCA.ShareReview.Notification = {
    draggedItem: null,

    info: function (header, text, guidance) {
        document.body.insertAdjacentHTML('beforeend',
            '<div id="analyticsDialogOverlay" class="analyticsDialogDim"></div>'
            + '<div id="analyticsDialogContainer" class="analyticsDialog">'
            + '<a class="analyticsDialogClose" id="analyticsDialogBtnClose"></a>'
            + '<h2 class="analyticsDialogHeader" id="analyticsDialogHeader" style="display:flex;margin-right:30px;">'
            + header
            + '</h2>'
            + '<span id="analyticsDialogGuidance" class="userGuidance"></span><br><br>'
            + '<div id="analyticsDialogContent">'
            + '</div>'
            + '<br><div class="analyticsDialogButtonrow">'
            + '<a class="button primary" id="analyticsDialogBtnGo">' + t('analytics', 'OK') + '</a>'
            + '</div></div>'
        );
        document.getElementById('analyticsDialogGuidance').innerHTML = guidance;
        document.getElementById('analyticsDialogContent').innerHTML = text;
        document.getElementById("analyticsDialogBtnClose").addEventListener("click", OCA.ShareReview.Notification.dialogClose);
        document.getElementById("analyticsDialogBtnGo").addEventListener("click", OCA.ShareReview.Notification.dialogClose);
    },

    confirm: function (header, text, callback) {
        document.body.insertAdjacentHTML('beforeend',
            '<div id="analyticsDialogOverlay" class="analyticsDialogDim"></div>'
            + '<div id="analyticsDialogContainer" class="analyticsDialog">'
            + '<a class="analyticsDialogClose" id="analyticsDialogBtnClose"></a>'
            + '<h2 class="analyticsDialogHeader" id="analyticsDialogHeader" style="display:flex;margin-right:30px;">'
            + header
            + '</h2>'
            + '<div id="analyticsDialogContent">'
            + '<div style="text-align:center; padding-top:100px" class="get-metadata icon-loading"></div>'
            + '</div>'
            + '<br><div class="analyticsDialogButtonrow">'
            + '<a class="button primary" id="analyticsDialogBtnGo">' + t('analytics', 'OK') + '</a>'
            + '<a class="button" id="analyticsDialogBtnCancel">' + t('analytics', 'Cancel') + '</a>'
            + '</div></div>'
        );
        document.getElementById('analyticsDialogContent').innerHTML = text;
        document.getElementById("analyticsDialogBtnClose").addEventListener("click", OCA.ShareReview.Notification.dialogClose);
        document.getElementById("analyticsDialogBtnCancel").addEventListener("click", OCA.ShareReview.Notification.dialogClose);
        document.getElementById("analyticsDialogBtnGo").addEventListener("click", callback);
    },

    /**
     * Function to display notifications.
     * @param {('info'|'success'|'error')} type - The type of the notification.
     * @param {string} message - The notification message.
     */
    notification: function (type, message) {
        if (parseInt(OC.config.versionstring.substr(0, 2)) >= 17) {
            if (type === 'success') {
                OCP.Toast.success(message)
            } else if (type === 'error') {
                OCP.Toast.error(message)
            } else {
                OCP.Toast.info(message)
            }
        } else {
            OC.Notification.showTemporary(message);
        }
    },

    /**
     * @param {string} header Popup header as text
     * @param callback Callback function of the OK button
     */
    htmlDialogInitiate: function (header, callback) {
        document.body.insertAdjacentHTML('beforeend',
            '<div id="analyticsDialogOverlay" class="analyticsDialogDim"></div>'
            + '<div id="analyticsDialogContainer" class="analyticsDialog">'
            + '<a class="analyticsDialogClose" id="analyticsDialogBtnClose"></a>'
            + '<h2 class="analyticsDialogHeader" id="analyticsDialogHeader" style="display:flex;margin-right:30px;">'
            + header
            + '</h2>'
            + '<span id="analyticsDialogGuidance" class="userGuidance"></span><br><br>'
            + '<div id="analyticsDialogContent">'
            + '<div style="text-align:center; padding-top:100px" class="get-metadata icon-loading"></div>'
            + '</div>'
            + '<br><div class="analyticsDialogButtonrow">'
            + '<a class="button primary" id="analyticsDialogBtnGo">' + t('analytics', 'OK') + '</a>'
            + '<a class="button" id="analyticsDialogBtnCancel">' + t('analytics', 'Cancel') + '</a>'
            + '</div></div>'
        );

        document.getElementById("analyticsDialogBtnClose").addEventListener("click", OCA.ShareReview.Notification.dialogClose);
        document.getElementById("analyticsDialogBtnCancel").addEventListener("click", OCA.ShareReview.Notification.dialogClose);
        document.getElementById("analyticsDialogBtnGo").addEventListener("click", callback);
    },

    htmlDialogUpdate: function (content, guidance) {
        document.getElementById('analyticsDialogContent').innerHTML = '';
        document.getElementById('analyticsDialogContent').appendChild(content);
        document.getElementById('analyticsDialogGuidance').innerHTML = guidance;
    },

    htmlDialogUpdateAdd: function (guidance) {
        document.getElementById('analyticsDialogGuidance').innerHTML += '<br>' + guidance;
    },

    dialogClose: function () {
        document.getElementById('analyticsDialogContainer').remove();
        document.getElementById('analyticsDialogOverlay').remove();
    },
}