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
 * Install script: creates (if needed) a default hidden course category
 * and stores its ID in plugin config.
 *
 * @package    local_ocbsbcoursecreation
 * @category   admin
 * @copyright   2025 Oncampus GmbH
 * @license    https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

/**
 * xmldb_local_ocbsbcoursecreation_install
 */
function xmldb_local_ocbsbcoursecreation_install(): void {
    global $CFG;

    require_once($CFG->dirroot . '/course/lib.php');

    // If an admin already picked a category in settings, keep it.
    $existingcatid = get_config('local_ocbsbcoursecreation', 'categoryid');
    if (!empty($existingcatid)) {
        return;
    }

    // Create a hidden default category (if not already present by name).
    $defaultname = get_string('plugin_categoryname', 'local_ocbsbcoursecreation');

    // Try to find an existing category with that name first.
    $existing = \core_course_category::get_all(['returnhidden' => true]);
    foreach ($existing as $cat) {
        if ($cat->name === $defaultname) {
            set_config('categoryid', $cat->id, 'local_ocbsbcoursecreation');
            return;
        }
    }

    // Create new category.
    $data              = new stdClass();
    $data->name        = $defaultname;
    $data->idnumber    = '';
    $data->description = 'Default course template category for local_ocbsbcoursecreation.';
    $data->visible     = 0; // Hidden by default.

    $created = \core_course_category::create($data);
    set_config('categoryid', $created->id, 'local_ocbsbcoursecreation');
}
