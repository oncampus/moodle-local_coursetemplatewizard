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

use local_coursetemplatewizard\service_user_creation_handler;

/**
 * Install hook: creates a dedicated service user and role for handling backup and restore functionality.
 *
 * @license     https://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 * @package     local_coursetemplatewizard
 * @category    admin
 * @copyright   2025 oncampus GmbH <support@oncampus.de>
 * @return void
 * @throws dml_exception
 * @throws moodle_exception
 */
function xmldb_local_coursetemplatewizard_install(): void {
    $serviceusercreationhandler = new service_user_creation_handler();
    $serviceusercreationhandler->create_service_user_with_role_and_capabilities();
}
