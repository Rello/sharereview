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

OCA.ShareReview.Navigation = {
    buildNavigation: function (data) {
        document.getElementById('shareReviewNavigation').innerHTML = '';

        let navigations = [
            {
                name: 'All Shares',
                event: OCA.ShareReview.Navigation.handleAllNavigation,
                style: 'icon-sharereview-shares',
                pinned: false
            },
            {
                name: 'New Shares',
                event: OCA.ShareReview.Navigation.handleNewSharesNavigation,
                style: 'icon-sharereview-new',
                pinned: false
            },
            {
                name: 'Confirm reviewed',
                event: OCA.ShareReview.Navigation.handleConfirmNavigation,
                style: 'icon-sharereview-check',
                pinned: false
            },
            {
                name: 'Reset time',
                event: OCA.ShareReview.Navigation.handleConfirmResetNavigation,
                style: 'icon-sharereview-reset',
                pinned: false
            }

        ];
        for (let navigation of navigations) {
            document.getElementById('shareReviewNavigation').appendChild(OCA.ShareReview.Navigation.buildNewButton(navigation));
        }
    },

    buildNewButton: function (data) {
        let li = document.createElement('li');
        if (data['pinned']) li.classList.add('pinned', 'first-pinned');
        let a = document.createElement('a');
        a.classList.add(data['style'], 'svg');
        a.addEventListener('click', data['event']);
        a.innerText = t('sharereview', data['name']);
        li.appendChild(a);
        return li;
    },

    handleAllNavigation: function () {
        OCA.ShareReview.Backend.getData();
    },

    handleNewSharesNavigation: function () {
        OCA.ShareReview.Backend.getData(true);
    },

    handleConfirmNavigation: function () {
        OCA.ShareReview.Backend.confirm();
    },

    handleConfirmResetNavigation: function () {
        OCA.ShareReview.Backend.confirmReset();
    }

}

OCA.ShareReview.Backend = {
    getData: function (onlyNew) {
        let newUrl = '';
        if (onlyNew) newUrl = '/new';
        let requestUrl = OC.generateUrl('apps/sharereview/data') + newUrl;
        fetch(requestUrl, {
            method: 'GET',
            headers: OCA.ShareReview.headers()
        })
            .then(response => {
                if (!response.ok) {
                    throw new Error(response.statusText);
                }
                return response.json();
            })
            .then(data => {
                OCA.ShareReview.Visualization.buildDataTable(data);
            })
            .catch(error => {
                OCA.ShareReview.Notification.notification('error', error)
                OCA.ShareReview.Visualization.hideElement('noDataContainer');
                OCA.ShareReview.Visualization.showElement('notSecuredContainer');
                OCA.ShareReview.Visualization.hideElement('tableContainer');
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
                OCA.ShareReview.Notification.notification('success', t('sharereview', 'Share deleted'));
                OCA.ShareReview.Backend.getData();
            })
            .catch(error => {
                OCA.ShareReview.Notification.notification('error', t('sharereview', 'Request could not be processed'))
            });
    },

    confirm: function () {
        let requestUrl = OC.generateUrl('apps/sharereview/confirm');
        fetch(requestUrl, {
            method: 'POST',
            headers: OCA.ShareReview.headers()
        })
            .then(response => response.json())
            .then(data => {
                OCA.ShareReview.Notification.notification('success', t('sharereview', 'Timestamp saved'));
            });
    },

    confirmReset: function () {
        let requestUrl = OC.generateUrl('apps/sharereview/confirmReset');
        fetch(requestUrl, {
            method: 'POST',
            headers: OCA.ShareReview.headers()
        })
            .then(response => response.json())
            .then(data => {
                OCA.ShareReview.Notification.notification('success', t('sharereview', 'Timestamp deleted'));
            });
    },
};

document.addEventListener('DOMContentLoaded', function () {
    OCA.ShareReview.Backend.getData();
    OCA.ShareReview.Navigation.buildNavigation();
});
