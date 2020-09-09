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
 * List groups from currente course and courses that user can manage groups.
 *
 * @package    tool_syncgroups
 * @copyright  Daniel Neis Araujo <danielneis@gmail.com>
 * @license    http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require('../../../config.php');
require_once("{$CFG->dirroot}/group/lib.php");

require_once($CFG->dirroot . '/backup/util/includes/backup_includes.php');
require_once($CFG->dirroot . '/backup/util/includes/restore_includes.php');
require_once($CFG->dirroot . '/backup/util/ui/renderer.php');
require_once($CFG->dirroot . '/backup/util/ui/import_extensions.php');

require("{$CFG->dirroot}/admin/tool/syncgroups/lib.php");
require("{$CFG->dirroot}/admin/tool/syncgroups/ui/components.php");

$courseid = required_param('courseid', PARAM_INT);
$groups = optional_param_array('groups', 0, PARAM_INT);
$destinations = optional_param_array('destinations', 0, PARAM_INT);
$searchcourses = optional_param('searchcourses', false, PARAM_BOOL);

$course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);

$url = new moodle_url('/admin/tool/syncgroups/index.php', array('courseid' => $courseid));

$PAGE->set_url($url);

require_login($course);

$context = context_course::instance($course->id);
require_capability('moodle/course:managegroups', $context);

$strsyncgroups = get_string('pluginname', 'tool_syncgroups');

$PAGE->set_title($strsyncgroups);
$PAGE->set_heading($course->fullname);
$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
echo $OUTPUT->heading(get_string('pluginname', 'tool_syncgroups'));

if (!$searchcourses && !empty($groups) && !empty($destinations)) {

    $error = false;
    if ($origem = get_course($courseid)) {

        list($in_or_equal, $groupparams) = $DB->get_in_or_equal($groups);
        if (!$groups_to_sync = $DB->get_records_select('groups', "courseid = ? AND id {$in_or_equal}",
                                                  array_merge(array($courseid), $groupparams))) {
            $error = true;
        }
    } else {
        $error = true;
    }

    $courses_to_sync = array();
    foreach ($destinations as $dest) {
        if ($cdest = get_course($dest)) {
            $cdest->context = context_course::instance($dest);
            if (has_capability('moodle/course:managegroups', $cdest->context)) {
                $courses_to_sync[] = $cdest;
            }
        } else {
            $error = true;
        }
    }

    if ($error) {
        print_error(get_string('error', 'tool_syncgroups'));
    } else {
        $trace = new html_list_progress_trace();
        tool_syncgroups_do_sync($groups_to_sync, $courses_to_sync, $trace);
        echo html_writer::link($url, get_string('back'));
    }

} else {

    echo html_writer::tag('p', get_string('intro', 'tool_syncgroups'));

    $search = new destination_courses_search(array('url' => $url), $courseid);

    $renderer = $PAGE->get_renderer('tool_syncgroups');

    echo $renderer->destination_courses_selector($url, $search, $courseid);
}
echo $OUTPUT->footer();
