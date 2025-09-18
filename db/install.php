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
 * Install hook (no-op): do not create any course category.
 *
 * @return void
 */
function xmldb_local_ocbsbcoursecreation_install(): void {
    // Intentionally empty. We no longer auto-create a category.
    // Admin chooses an existing category in the plugin settings.
}
