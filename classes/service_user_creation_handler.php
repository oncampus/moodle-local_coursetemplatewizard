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

namespace local_coursetemplatewizard;

use coding_exception;
use context_system;
use dml_exception;
use moodle_exception;
use stdClass;

defined('MOODLE_INTERNAL') || die();

require_once($CFG->dirroot . '/user/lib.php');

/**
 * Class for handling the creation of a service user and their required role and capabilities.
 *
 * @package     local_coursetemplatewizard
 * @copyright   2025 Oncampus GmbH
 * @license     http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class service_user_creation_handler {
    /**
     * Creates a new service user to handle the backup and restore process, triggered by another user.
     * Also creates a unique service user role with backup and restore capability permissions.
     *
     * @return void
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    public function create_service_user_with_role_and_capabilities(): void {
        global $DB;
        $serviceuser = $this->create_service_user_object();
        if ($DB->record_exists('user', ['username' => $serviceuser->username])) {
            throw new moodle_exception("User {$serviceuser->username} already exists. " .
                "Please delete the user to ensure a clean installation.");
        }
        $serviceuserid = user_create_user($serviceuser, false, false);
        set_config('serviceuserid', $serviceuserid, 'local_coursetemplatewizard');
        $serviceuserroleid = $this->create_service_user_role();
        $systemcontext = context_system::instance();
        role_assign($serviceuserroleid, $serviceuserid, $systemcontext->id);
        $this->create_service_user_role_capabilities($serviceuserroleid, $systemcontext->id);
    }

    /**
     * Creates a service user object.
     *
     * @return stdClass
     * @throws coding_exception
     * @throws dml_exception
     * @throws moodle_exception
     */
    private function create_service_user_object(): stdClass {
        $user = new stdClass();
        $user->auth = 'manual';
        $user->confirmed = 1;
        $user->username = 'coursetemplateserviceuser';
        $user->firstname = get_string('coursetemplateserviceuser_firstname', 'local_coursetemplatewizard');
        $user->lastname = get_string('coursetemplateserviceuser_lastname', 'local_coursetemplatewizard');
        $user->email = get_config('core', 'noreplyaddress');
        $newpassword = generate_password(20);
        $hashedpassword = hash_internal_user_password($newpassword);
        $user->password = $hashedpassword;
        return $user;
    }

    /**
     * Creates the dedicated role for the service user.
     *
     * @throws coding_exception
     */
    private function create_service_user_role(): int {
        $roleid = create_role(
            get_string('coursetemplateserviceuserrole_name', 'local_coursetemplatewizard'),
            'coursetemplateserviceuser',
            get_string('coursetemplateserviceuserrole_desc', 'local_coursetemplatewizard')
        );
        return $roleid;
    }

    /**
     * Assigns required capabilities to the role of the service user.
     *
     * @param int $roleid
     * @param int $systemcontextid
     * @return void
     * @throws coding_exception
     */
    private function create_service_user_role_capabilities(int $roleid, int $systemcontextid): void {
        assign_capability('moodle/backup:backupcourse', CAP_ALLOW, $roleid, $systemcontextid);
        assign_capability('moodle/restore:restorecourse', CAP_ALLOW, $roleid, $systemcontextid);
    }
}
