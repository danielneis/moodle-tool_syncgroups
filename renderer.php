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

class tool_syncgroups_renderer extends core_backup_renderer {

    /**
     * Renders a restore course search object
     *
     * @param restore_course_search $component
     * @return string
     */
    public function render_destination_courses_search(destination_courses_search $component) {
        $url = $component->get_url();

        $output = html_writer::start_tag('div', array('class' => 'restore-course-search'));
        $output .= html_writer::start_tag('div', array('class' => 'rcs-results'));

        $table = new html_table();
        $table->head = array('', get_string('shortnamecourse'), get_string('fullnamecourse'));
        $table->data = array();
        if ($component->get_count() !== 0) {
            foreach ($component->get_results() as $course) {
                $row = new html_table_row();
                $row->attributes['class'] = 'rcs-course';
                if (!$course->visible) {
                    $row->attributes['class'] .= ' dimmed';
                }
                $row->cells = array(
                    html_writer::empty_tag('input', array('type' => 'checkbox', 'name' => 'destinations[]', 'value' => $course->id)),
                    format_string($course->shortname, true, array('context' => context_course::instance($course->id))),
                    format_string($course->fullname, true, array('context' => context_course::instance($course->id)))
                );
                $table->data[] = $row;
            }
            if ($component->has_more_results()) {
                $cell = new html_table_cell(get_string('moreresults', 'backup'));
                $cell->colspan = 3;
                $cell->attributes['class'] = 'notifyproblem';
                $row = new html_table_row(array($cell));
                $row->attributes['class'] = 'rcs-course';
                $table->data[] = $row;
            }
        } else {
            $cell = new html_table_cell(get_string('nomatchingcourses', 'backup'));
            $cell->colspan = 3;
            $cell->attributes['class'] = 'notifyproblem';
            $row = new html_table_row(array($cell));
            $row->attributes['class'] = 'rcs-course';
            $table->data[] = $row;
        }
        $output .= html_writer::table($table);
        $output .= html_writer::end_tag('div');

        $output .= html_writer::start_tag('div', array('class' => 'rcs-search'));
        $output .= html_writer::empty_tag('input', array('type' => 'text', 'name' => restore_course_search::$VAR_SEARCH, 'value' => $component->get_search()));
        $output .= html_writer::empty_tag('input', array('type' => 'submit', 'name' => 'searchcourses', 'value' => get_string('search')));
        $output .= html_writer::end_tag('div');

        $output .= html_writer::end_tag('div');
        return $output;
    }

    public function destination_courses_selector(moodle_url $nextstageurl, destination_courses_search $courses=null, $courseid) {

        $html  = html_writer::start_tag('div', array('class' => 'import-course-selector backup-restore'));
        $html .= html_writer::start_tag('form', array('method' => 'post', 'action' => $nextstageurl->out_omit_querystring()));
        foreach ($nextstageurl->params() as $key => $value) {
            $html .= html_writer::empty_tag('input', array('type' => 'hidden', 'name' => $key, 'value' => $value));
        }

        $html .= html_writer::start_tag('div', array('class' => 'ics-existing-group backup-section'));
        $html .= $this->output->heading(get_string('selectgroups', 'tool_syncgroups'), 2, array('class' => 'header'));
        $html .= $this->output->render(new tool_syncgroups\output\groups_list($courseid));
        $html .= html_writer::end_tag('div');

        // We only allow import adding for now. Enforce it here.
        $html .= html_writer::start_tag('div', array('class' => 'ics-existing-course backup-section'));
        $html .= $this->output->heading(get_string('syncgroupsto', 'tool_syncgroups'), 2, array('class' => 'header'));
        $html .= $this->backup_detail_pair(get_string('selectacourse', 'backup'), $this->render($courses));

        $html .= $this->backup_detail_pair('', html_writer::empty_tag('input', array('type' => 'submit', 'value' => get_string('continue'))));

        $html .= html_writer::end_tag('div');
        $html .= html_writer::end_tag('form');
        $html .= html_writer::end_tag('div');
        return $html;
    }
}
