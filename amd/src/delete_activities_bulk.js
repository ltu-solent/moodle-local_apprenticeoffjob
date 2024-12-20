// This file is part of Moodle - http://moodle.org/
//
// Moodle is free software: you can redistribute it and/or modify
// it under the terms of the GNU General Public License as published by
// the Free Software Foundation, either version 3 of the License, or
// (at your option) any later version.
//
// Moodle is distributed in the hope that it will be useful,
// but WITHOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// You should have received a copy of the GNU General Public License
// along with Moodle.  If not, see <http://www.gnu.org/licenses/>.

/**
 * Bulk delete activities with a Modal confirm dialogue.
 *
 * @module     local_apprenticeoffjob/delete_activities_bulk
 * @copyright  2024 Southampton Solent University {@link https://www.solent.ac.uk}
 * @author Mark sharp <mark.sharp@solent.ac.uk>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

import {prefetchStrings} from 'core/prefetch';
import {getList} from 'core/normalise';
import Notification from 'core/notification';
import {get_string as getString} from 'core/str';
import Ajax from 'core/ajax';
import Url from 'core/url';

const selectors = {
    activeRows: 'input.activitycheckbox:checked',
    bulkActions: '#apprentice_activitiesbulkactions',
    table: '#apprentice_useractivities_table',
};

export const init = () => {
    prefetchStrings('local_apprenticeoffjob', [
        'confirmdelete',
        'deletewarning',
    ]);
    prefetchStrings('core', [
        'delete',
    ]);
    registerEventListeners();
};

/**
 * Register event listeners for bulk deleting
 */
const registerEventListeners = () => {
    document.querySelector(selectors.bulkActions).addEventListener('change', e => {
        const action = e.target;
        if (action.value.indexOf('#') !== -1) {
            e.preventDefault();
            if (action.value == '#deleteselect') {
                deleteActivitiesConfirm();
            }
        }
    });
};

/**
 * Confirm delete activities dialogue.
 */
const deleteActivitiesConfirm = () => {
    const table = document.querySelector(selectors.table);
    const fullname = table.getAttribute('data-fullname');
    const userId = table.getAttribute('data-userid');
    const activeRows = getList(table.querySelectorAll(selectors.activeRows));
    const ids = activeRows.map(item => item.value);

    Notification.saveCancelPromise(
        getString('confirmdelete', 'local_apprenticeoffjob'),
        getString('deletewarning', 'local_apprenticeoffjob', {n: ids.length, who: fullname}),
        getString('delete', 'core'),
    ).then(() => {
        return deleteActivities(ids, userId);
    }).catch(() => {
        return;
    });
};

/**
 * Delete activities
 *
 * @param {Number[]} ids Activity IDs
 * @param {Number} userId
 */
async function deleteActivities(ids, userId) {
    const request = {
        methodname: 'local_apprenticeoffjob_delete_activities',
        args: {
            activityids: ids,
        }
    };
    try {
        await Ajax.call([request])[0];
        window.location.href = Url.relativeUrl(
            'local/apprenticeoffjob/user.php',
            {
                userid: userId,
            },
            false
        );
    } catch (error) {
        Notification.exception(error);
    }
}
