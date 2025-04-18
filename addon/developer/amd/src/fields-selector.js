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
 * Search user selector module.
 *
 * @module block_dash/group-user-selector
 * @copyright 2017 The Open University
 * @license http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

define(['jquery', 'core/ajax', 'core/templates'], function ($, Ajax, Templates) {

    return /** @alias module:block_dash/group-user-selector */ {

        processResults: function (selector, results) {
            return results;
        },

        transport: function (selector, query, success, failure) {
            var promise;

            // Search within specific course if known and if the 'search within' dropdown is set
            // to search within course or activity.
            var args = { query: query };

            // Tables to fetch the fields.
            var tables = { main: '', joins: [] };

            document.querySelectorAll('[name="maintable"]').forEach(function (r) {
                tables.main = r.value;
            });

            document.querySelectorAll('[name^="tablejoins"]').forEach(function (r) {
                // Verify the enable joins is checked.
                if (document.querySelector('[type="checkbox"][name="enablejoins"]').checked) {
                    tables.joins.push(r.value);
                }
            });

            // Include the tables in the params.
            args.tables = tables;

            // Call AJAX request.
            promise = Ajax.call([{ methodname: 'dashaddon_developer_get_fields_list', args: args }]);

            promise[0].then(function (result) {
                success(result);
                return;
            }).fail(failure);
        }

    };

});
