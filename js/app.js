/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/** global: OCA */
/** global: OCP */
/** global: table */

'use strict';

OCA.ShareReview = Object.assign({}, OCA.ShareReview, {
    initialDocumentTitle: null,
    currentReportData: {},
    tableObject: null,

    headers: function () {
        let headers = new Headers();
        headers.append('requesttoken', OC.requestToken);
        headers.append('OCS-APIREQUEST', 'true');
        headers.append('Content-Type', 'application/json');
        return headers;
    }
});

OCA.ShareReview.UI = {
    handleDeleteClicked: function (evt) {
        let shareId = evt.target.id;
        OCA.ShareReview.Notification.confirm(
            t('sharereview', 'Delete'),
            t('sharereview', 'Are you sure?') + ' ' + t('sharereview', 'The share will be deleted!'),
            function () {
                OCA.ShareReview.Backend.delete(shareId);
            }
        );
    },
};

OCA.ShareReview.Backend = {
    getData: function () {
        let requestUrl = OC.generateUrl('apps/sharereview/data');
        fetch(requestUrl, {
            method: 'GET',
            headers: OCA.ShareReview.headers()
        })
            .then(response => response.json())
            .then(data => {
                OCA.ShareReview.Visualization.buildDataTable(document.getElementById("tableContainer"), data);
            });
     },

    delete: function (shareId) {
        OCA.ShareReview.Notification.dialogClose();
        let requestUrl = OC.generateUrl('apps/sharereview/delete/')+shareId;
        fetch(requestUrl, {
            method: 'DELETE',
            headers: OCA.ShareReview.headers()
        })
            .then(response => response.json())
            .then(data => {
                OCA.ShareReview.Backend.getData()
            });

    }
};

document.addEventListener('DOMContentLoaded', function () {
    OCA.ShareReview.Backend.getData();
});
