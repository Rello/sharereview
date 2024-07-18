/**
 * Share Review
 *
 * SPDX-FileCopyrightText: 2024 Marcel Scherello
 * SPDX-License-Identifier: AGPL-3.0-or-later
 */

/** global: OCA */
/** global: OCP */
/** global: OC */
/** global: table */
/** global: Headers */

'use strict';

/**
 * @namespace OCA.ShareReview.Visualization
 */
OCA.ShareReview.Visualization = {

    // *************
    // *** table ***
    // *************

    buildDataTable: function (data) {

        if (data.length === 0) {
            this.showElement('noDataContainer');
            this.hideElement('tableContainer');
            return;
        } else {
            this.hideElement('noDataContainer');
            this.showElement('tableContainer');
        }
        let domTarget = document.getElementById("tableContainer");
        domTarget.innerHTML = '';
        if (OCA.ShareReview.tableObject) {
            OCA.ShareReview.tableObject.destroy();

        }


        let language = {
            // TRANSLATORS Noun
            search: t('sharereview', 'Search'),
            lengthMenu: t('sharereview', 'Show _MENU_ entries'),
            info: t('sharereview', 'Showing _START_ to _END_ of _TOTAL_ entries'),
            infoEmpty: t('sharereview', 'Showing 0 to 0 of 0 entries'),
            paginate: {
                first: '<<',
                previous: '<',
                next: '>',
                last: '>>'
            },
        };

        let columns = Object.keys(data[0]).map(key => ({
            title: key.charAt(0).toUpperCase() + key.slice(1),
            data: key
        }));

        columns = OCA.ShareReview.Visualization.addColumnRender(columns);

        const isDataLengthGreaterThanDefault = data.length > 10;


        OCA.ShareReview.tableObject = new DataTable(domTarget, {
            pagingType: 'simple_numbers',
            autoWidth: false,
            data: data,
            columns: columns,
            language: language,
            order: [[6, 'desc']],
            layout: {
                topStart: isDataLengthGreaterThanDefault ? 'pageLength' : null,
                topEnd: isDataLengthGreaterThanDefault ? 'search' : null,
                bottomStart: isDataLengthGreaterThanDefault ? 'info' : null,
                bottomEnd: isDataLengthGreaterThanDefault ? 'paging' : null,
            },
        });

        },

    addColumnRender: function(columns) {
        columns.forEach(obj => {
            if (obj.data === 'permissions') {
                obj.render = OCA.ShareReview.Visualization.renderPermissions;
            } else if (obj.data === 'time') {
                obj.render = OCA.ShareReview.Visualization.renderDates;
            } else if (obj.data === 'action') {
                obj.render = OCA.ShareReview.Visualization.renderAction;
            } else if (obj.data === 'type') {
                obj.render = OCA.ShareReview.Visualization.renderTypes;
            }
        });
        return columns;
    },

    renderPermissions: function(data) {
        let iconClass = 'icon-sharereview-more';
        let titleText = 'more'

        switch (data) {
            case 1:
                iconClass = 'icon-sharereview-read';
                titleText = 'read';
                break;
            case 17: // link read
                iconClass = 'icon-sharereview-read';
                break;
            case 31:
                iconClass = 'icon-sharereview-edit';
                break;
            case 19: //link edit
                iconClass = 'icon-sharereview-edit';
                break;
        }

        return '<div permission="' + data + '"class="' + iconClass + '" title="'+titleText+'"></div>';
},

    renderTypes: function(data) {
        let iconClass = 'icon-sharereview-link';
        let titleText = 'more'

        switch (data) {
            case 'email':
                iconClass = 'icon-sharereview-email';
                break;
            case 'link': // link read
                iconClass = 'icon-sharereview-link';
                break;
            case 'group':
                iconClass = 'icon-sharereview-group';
                break;
            case 'user': //link edit
                iconClass = 'icon-sharereview-user';
                break;
        }

        return '<div class="' + iconClass + '" ><span style="margin-left: 20px;">'+data+'</span></div>';
    },

    renderDates: function (data) {
        const date = new Date(data);
        return date.toLocaleString();
    },

    renderAction: function (data) {
        let string = '<div class="icon-sharereview-delete" data-id='+data+'></div>';
        let div = document.createElement('div');
        div.classList.add('icon-sharereview-delete');
        div.id = data;
        div.addEventListener('click', OCA.ShareReview.UI.handleDeleteClicked);
        return div;
    },

    showElement: function (element) {
        if (document.getElementById(element)) {
            document.getElementById(element).hidden = false;
            //document.getElementById(element).style.removeProperty('display');
        }
    },

    hideElement: function (element) {
        if (document.getElementById(element)) {
            document.getElementById(element).hidden = true;
            //document.getElementById(element).style.display = 'none';
        }
    },
}