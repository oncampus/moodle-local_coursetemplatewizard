<?php
// This file is part of Moodle - https://moodle.org/
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
// along with Moodle.  If not, see <https://www.gnu.org/licenses/>.

/**
 * Deinstall the plugin and the plugin settings
 * Won't deinstall course category to ensure course templates are sustained
 *
 * @package     local_ocbsbcoursecreation
 * @category    admin
 * @copyright   2025 Oncampus GmbH
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
defined('MOODLE_INTERNAL') || die();
global $DB, $CFG;
$sqltype   = "DROP TABLE IF EXISTS {ocbsbcoursecreation_type}";
$sqlvalues = "DROP TABLE IF EXISTS {ocbsbcoursecreation_value}";

$transaction   = $DB->start_delegated_transaction();
$droptype     = $DB->execute($sqltype);
$dropvalue    = $DB->execute($sqlvalues);
$dropsettings = $DB->delete_records('config_plugins', ['name' => 'local_ocbsbcoursecreation']);
if ($droptype && $dropvalue && $dropsettings) {
    $DB->commit_delegated_transaction($transaction);
}
