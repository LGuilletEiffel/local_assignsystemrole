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
 * Observer file
 *
 * @package    local_assignsystemrole
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */
class local_assignsystemrole_observer {

    public static function assignrole(core\event\base $event) {

        global $DB;

        $event_data = $event->get_data();

        $userid = $event_data['relateduserid'];

        $contextsystemid = context_system::instance()->id;

        $profilesystemroles = $DB->get_record('user_info_field', array('shortname' => 'systemrole'))->param1;

        $tabprofilesystemroles = explode("\n", $profilesystemroles);

        $listprofilesystemrolestokeep = array();

        foreach ($tabprofilesystemroles as $profilesystemrole) {

            $roleid = $DB->get_record('role', array('name' => trim($profilesystemrole)))->id;

            $listprofilesystemrolestoremove [$roleid] = 1;
        }

        $fieldid = $DB->get_record('user_info_field', array('shortname' => 'systemrole'))->id;

        $usersystemroles = $DB->get_record('user_info_data', array('userid' => $userid, 'fieldid' => $fieldid))->data;

        $tabusersystemroles = explode("\n", $usersystemroles);

        foreach ($tabusersystemroles as $usersystemrole) {

            $roleid = $DB->get_record('role', array('name' => trim($usersystemrole)))->id;

            role_assign($roleid, $userid, $contextsystemid);

            $listprofilesystemrolestoremove[$roleid] = 0;
        }

        foreach ($listprofilesystemrolestoremove as $localroleid => $removerole) {

            if ($removerole) {

                role_unassign($localroleid, $userid, $contextsystemid);
            }
        }
    }

}
