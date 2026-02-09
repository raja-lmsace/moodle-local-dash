<?php
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
 * Certificates - dashaddon certificate table.
 *
 * @package    dashaddon_certificates
 * @copyright  2024 bdecent gmbh <https://bdecent.de>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

namespace dashaddon_certificates\local\dash_framework\structure;

use moodle_url;
use lang_string;
use block_dash\local\dash_framework\structure\field;
use block_dash\local\dash_framework\structure\table;
use block_dash\local\data_grid\field\attribute\date_attribute;
use block_dash\local\data_grid\field\attribute\button_attribute;
use block_dash\local\data_grid\field\attribute\identifier_attribute;
use block_dash\local\data_grid\field\attribute\linked_data_attribute;
use block_dash\local\data_grid\field\attribute\moodle_url_attribute;
use block_dash\local\data_grid\field\attribute\widget_attribute;
use tool_certificate\reportbuilder\local\formatters\certificate as formatter;

/**
 * Certificates table structure definitions for programs datasource.
 */
class certificates_table extends table {
    /**
     * Build a new table.
     */
    public function __construct() {
        parent::__construct('dashaddon_certificates', 'tci');
    }

    /**
     * Get human readable title for table.
     *
     * @return string
     */
    public function get_title(): string {
        return get_string('tablealias_tci', 'dashaddon_certificates');
    }

    /**
     * Setup available fields for the table.
     *
     * @return field_interface[]
     * @throws \moodle_exception
     */
    public function get_fields(): array {

        $fields = [

            new field('id', new \lang_string('certificate', 'tool_certificate'), $this, null, [
                new identifier_attribute(),
            ]),

            // Name of the certificate template.
            new field('templatename', new lang_string('certificatetemplatename', 'tool_certificate'), $this, 'tct.name'),

            // Certificate code.
            new field('code', new lang_string('code', 'tool_certificate'), $this, 'tci.code'),

            // Certificate code with link.
            new field('code_link', new lang_string('certificatecodelinked', 'block_dash'), $this, 'tci.code', [
                new widget_attribute([
                    'callback' => fn($row, $data) => formatter::code_with_link($data, $row),
                ]),
            ]),

            // Certificate date issued.
            new field('timecreated', new lang_string('issueddate', 'tool_certificate'), $this, null, [
                new date_attribute([
                    'format' => get_string('strftimedaydatetime', 'langconfig'),
                ]),
            ]),

            // Certificate expiry date.
            new field('expires', new lang_string('expirydate', 'tool_certificate'), $this, null, [
                new widget_attribute([
                    'callback' => fn($row, $data) => $data > 0 ? userdate($data) : get_string('never', 'tool_certificate'),
                ]),
            ]),

            // Certificate status.
            new field('status', new lang_string('status', 'tool_certificate'), $this, 'tci.expires AS expires, tci.expires', [
                new widget_attribute([
                    'callback' => fn($row, $data) => formatter::certificate_issued_status($data, $row),
                ]),
            ]),

            // Download certificate button.
            new field('downloadbutton', new lang_string('downloadcertificate', 'block_dash'), $this, 'tci.code AS code, tci.code', [
                // Coursecertificate module doesn't provide option to download the certificate.
                // Create a certificate as link.
                new widget_attribute([
                    'callback' => function ($record, $data) {
                        $url = \tool_certificate\template::instance($record->tct_templateid)->get_issue_file_url($record);
                        $murl = new moodle_url($url);
                        $murl->param('forcedownload', true);

                        $attr = new linked_data_attribute(['url' => $murl]);
                        $data = get_string('downloadcertificate', 'block_dash');
                        return $attr->transform_data($data, $record);
                    },
                ]),

            ]),

            // View certificate button.
            new field('viewbutton', new lang_string('viewcertificate', 'tool_certificate'), $this, 'tci.code', [
                new moodle_url_attribute(['url' => new moodle_url('/admin/tool/certificate/view.php', ['code' => 'tci_code'])]),
                new button_attribute(['label' => new lang_string('viewcertificate', 'tool_certificate')]),
            ]),

        ];
        return $fields;
    }
}
