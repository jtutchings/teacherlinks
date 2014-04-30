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

require_once(dirname(__FILE__).'/lib.php');

require_once('../../config.php');
global $DB;
$cmid = required_param('id', PARAM_INT);

$course = $DB->get_record('course', array('id' => $cmid), '*', MUST_EXIST);

require_login($course->id);
$PAGE->set_url(new moodle_url('/blocks/teacherlinks/displayall.php', array('id' => $course->id)));
$PAGE->set_title(get_config('teacherlinks', 'Display_All_Title'));
$PAGE->set_heading(get_config('teacherlinks', 'frontpage_header'));
$PAGE->navbar->add(get_config('teacherlinks', 'frontpage_header'));

$courseid = required_param('id', PARAM_INT);
global $COURSE;

$PAGE->set_context(context_course::instance($courseid));

$PAGE->requires->js('/blocks/teacherlinks/displayall.js', true);

$PAGE->set_pagelayout('standard');

echo $OUTPUT->header();
/*
 * First work out the personal and module links
 */

$generaltablink = get_string('labelgeneraltabdisplayname', 'block_teacherlinks');

$tabheaders[] = html_writer::tag('a', get_string('other_cohort_tab', 'block_teacherlinks'), array('href' => '#othercohorts', ));
$tabheaders[] = html_writer::tag('a', $generaltablink, array('href' => '#' . strtolower($generaltablink) , ));

/*
* Start of tabbed content
*/
echo html_writer::tag('H1', $course->fullname);
echo get_config('teacherlinks', 'frontpage_top_text');
echo html_writer::start_tag('div', array('id' => "tabContainer"));
echo html_writer::alist($tabheaders);
echo html_writer::start_tag('div');

echo teacherlinks_get_other_cohorts_tab($course, 'othercohorts');

echo teacherlinks_general_tab( $generaltablink);

echo html_writer::end_tag('div');
echo html_writer::end_tag('div');
/*
* End of tabbed content
*/
echo get_config('teacherlinks', 'frontpage_footer_text');
echo $OUTPUT->footer();