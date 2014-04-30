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

require_once(dirname(__FILE__). '/lib.php');
class block_teacherlinks extends block_base {
    public function init() {
        $this->title = get_string('blocktitle', 'block_teacherlinks');
    }
    public function has_config() {
        return true;
    }
    public function get_content() {
        global $COURSE;
        global $USER;

        if ($this->content !== null) {
            return $this->content;
        }

        $this->content = new stdClass;

        if ($COURSE->id == 1) {

            $this->content = '';
            return $this->content;

        }

        $coursecontext = get_context_instance(CONTEXT_COURSE, $COURSE->id);
        $enrolled = user_has_role_assignment($USER->id, teacherlinks_get_editingteacher_roleid(), $coursecontext->id);

        if (!has_capability('block/teacherlinks:viewlink', $this->page->context)) {

            $this->content = '';
            return $this->content;
        }

        if (get_config('teacherlinks', 'block_top_text') != '') {
            $this->content->text = get_config('teacherlinks', 'block_top_text');
        }
        $links = array();
        $links[] = html_writer::link(new moodle_url('/blocks/teacherlinks/displayall.php',
                                        array('id' => $COURSE->id)),
                                        get_string('othercohortslinktext', 'block_teacherlinks'));
        $links[] = html_writer::link(new moodle_url('/notes/index.php',
                                        array('filterselect' => $COURSE->id, 'filtertype' => 'course')),
                                        get_string('noteslink', 'block_teacherlinks'));
        $links[] = html_writer::link(new moodle_url('/user/index.php',
                                        array('contextid' => $coursecontext->id)),
                                        get_string('participants'));

        $this->content->text .= html_writer::tag('p',
                                        get_string('otherlinktitle', 'block_teacherlinks') . html_writer::alist($links));

        if (get_config('teacherlinks', 'block_footer_text')) {
            $this->content->footer = html_writer::tag('p', get_config('teacherlinks', 'block_footer_text'));
        }
        return $this->content;
    }
}