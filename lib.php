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
 * Helper functions for tool_syncgroups.
 *
 * @package    tool_syncgroups
 * @copyright  Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once($CFG->dirroot . '/group/lib.php');
require_once($CFG->libdir . '/formslib.php');

function tool_syncgroups_extend_navigation_course($navigation, $course, context $context = null) {
    global $PAGE;

    // Only add this settings item on non-site course pages.
    if (!$PAGE->course or $PAGE->course->id == 1) {
        return;
    }

    $url = new moodle_url('/admin/tool/syncgroups/index.php', array('courseid' => $context->instanceid));
    $navigation->add(get_string('pluginname','tool_syncgroups'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
}

function tool_syncgroups_do_sync($groups, $destinations, $trace) {
    global $DB;

    foreach ($groups as $group) {

        $trace->output(get_string('group') . ': ' . $group->name);

        if (!$members = $DB->get_records_menu('groups_members', array('groupid' => $group->id), '', 'userid, id')) {
            $trace->output('group with no members, skipping');
            continue;
        }

        foreach ($destinations as $dest) {

            $trace->output(get_string('course') . ': ' . $dest->shortname);

            if ($dgr = $DB->get_record('groups', array('courseid' => $dest->id, 'name' => $group->name), 'id, courseid, idnumber, name')) {

                $trace->output(get_string('groupexists', 'tool_syncgroups'));

            } else {

                $dgr = new Stdclass();
                $dgr->courseid     = $dest->id;
                $dgr->timecreated  = time();
                $dgr->timemodified = $dgr->timecreated;
                $dgr->name         = $group->name;
                $dgr->description  = $group->description;
                $dgr->descriptionformat = $group->descriptionformat;
                if ($dgr->id = groups_create_group($dgr)) {
                    $trace->output(get_string('groupcreated', 'tool_syncgroups'));
                } else {
                    print_error(get_string('error', 'tool_syncgrouops'));
                }
            }

            $trace->output(get_string('addingmembers', 'tool_syncgroups'));

            foreach ($members as $userid => $memberid) {

                if (groups_add_member($dgr->id, $userid)) {
                    $trace->output(get_string('memberadded', 'tool_syncgroups', $userid));
                } else {
                    $trace->output(get_string('usernotenrolled', 'tool_syncgroups', $userid));
                }
            }

            $trace->output(get_string('removingmembers', "tool_syncgroups"));
            $members_dest = $DB->get_records('groups_members', array('groupid' => $dgr->id), '', 'id, groupid, userid');
            foreach ($members_dest as $id => $usum) {
                if (!isset($members[$usum->userid])) {
                    groups_remove_member($dgr->id, $usum->userid);
                    $trace->output(get_string('memberremoved', 'tool_syncgroups', $usum->userid));
                }
            }
        }
    }
    $trace->output(get_string('done', 'tool_syncgroups'));
    $trace->finished();
}
