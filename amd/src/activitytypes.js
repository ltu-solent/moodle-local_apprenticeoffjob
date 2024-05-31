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
 * Add dynamic help when adding or editing an activity.
 *
 * @module     local_apprenticeoffjob/activitytypes
 * @copyright  2024 Solent University {@link https://www.solent.ac.uk}
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

export const init = (activitytypes) => {
    const activitytypediv = document.querySelector('#id_error_activitytype');
    if (!activitytypediv) {
        return;
    }
    let activitydesc = document.createElement('div');
    activitydesc.setAttribute('id', 'activitydesc');
    activitydesc.style.width = '100%';
    activitydesc.style.marginTop = '10px';
    activitytypediv.after(activitydesc);
    let activity = document.querySelector('#id_activitytype').value;
    if (activity > 0) {
        activitydesc.innerHTML = activitytypes[activity] ?? '';
    }
    document.addEventListener('change', e => {
        if (e.target.id == 'id_activitytype') {
            activity = e.target.value;
            if (activity > 0) {
                activitydesc.innerHTML = activitytypes[activity] ?? '';
            }
        }
    });
};
