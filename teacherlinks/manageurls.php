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
 * Script to let a user manage their RSS feeds.
 *
 * @package   moodlecore
 * @copyright 2009 Tim Hunt
 * @license   http://www.gnu.org/copyleft/gpl.html GNU GPL v3 or later
 */

require_once(dirname(__FILE__) . '/../../config.php');
require_once($CFG->libdir . '/tablelib.php');

require_login();

$returnurl = optional_param('returnurl', '', PARAM_LOCALURL);
$courseid = optional_param('courseid', 0, PARAM_INTEGER);
$deleteurlid = optional_param('deleteurlid', 0, PARAM_INTEGER);
$hideurlid = optional_param('hideurlid', 0, PARAM_INTEGER);
$showurl = optional_param('show', 0, PARAM_INTEGER);
$hideurl = optional_param('hide', 0, PARAM_INTEGER);
$moveup = optional_param('moveup', 0, PARAM_INTEGER);
$movedown = optional_param('movedown', 0, PARAM_INTEGER);
$baseurl = new moodle_url('/blocks/teacherlinks/manageurls.php');
$editbaseurl = new moodle_url('/blocks/teacherlinks/editurl.php');

if ($courseid == SITEID) {
    $courseid = 0;
}
if ($courseid) {
    $course = $DB->get_record('course', array('id' => $courseid), '*', MUST_EXIST);
    $PAGE->set_course($course);
    $context = $PAGE->context;
} else {
    $context = get_context_instance(CONTEXT_SYSTEM);
    $PAGE->set_context($context);
}



$urlparams = array();
$extraparams = '';
if ($courseid) {
    $urlparams['courseid'] = $courseid;
    $extraparams = '&courseid=' . $courseid;
}
if ($returnurl) {
    $urlparams['returnurl'] = $returnurl;
    $extraparams = '&returnurl=' . $returnurl;
}
$baseurl = new moodle_url('/blocks/teacherlinks/manageurls.php', $urlparams);
$PAGE->set_url($baseurl);

// Process any actions.
if ($deleteurlid && confirm_sesskey()) {
    $DB->delete_records('block_teacherlinks', array('id' => $deleteurlid));

}
if ($hideurlid && confirm_sesskey()) {

    if ($hideurl) {
        $DB->update_record('block_teacherlinks', array('id' => $hideurlid, 'visible' => 0));
    } else {
        $DB->update_record('block_teacherlinks', array('id' => $hideurlid, 'visible' => 1));
    }

}

$swapurl = null;

if (!empty($moveup) || !empty($movedown)) {
    if (!empty($moveup)) {
        if ($moveurl = $DB->get_record('block_teacherlinks', array('id' => $moveup))) {
            $swapurl = $DB->get_record('block_teacherlinks', array('sortorder' => $moveurl->sortorder - 1));
        }
    } else if ($moveurl = $DB->get_record('block_teacherlinks', array('id' => $movedown))) {
        $swapurl = $DB->get_record('block_teacherlinks', array('sortorder' => $moveurl->sortorder + 1));
    }

    if ($swapurl and $moveurl) {
        $DB->set_field('block_teacherlinks', 'sortorder', $swapurl->sortorder, array('id' => $moveurl->id));
        $DB->set_field('block_teacherlinks', 'sortorder', $moveurl->sortorder, array('id' => $swapurl->id));
    }

}

// Display the list of feeds.

$links = $DB->get_records_select('block_teacherlinks', null, null, $DB->sql_order_by_text('sortorder'));

$strmanage = get_string('manageurls', 'block_teacherlinks');

$PAGE->set_pagelayout('standard');
$PAGE->set_title($strmanage);
$PAGE->set_heading($strmanage);

$settingsurl = new moodle_url('/admin/settings.php?section=blocksettingteacherlinks');
$managelinks = new moodle_url($baseurl, $urlparams);
$PAGE->navbar->add(get_string('blocks'));
$PAGE->navbar->add(get_string('pluginname', 'block_teacherlinks'), $settingsurl);
$PAGE->navbar->add(get_string('manageurls', 'block_teacherlinks'), $managelinks);
echo $OUTPUT->header();

$table = new flexible_table('teacherlinks-display-links');

$table->define_columns(array('displaytext', 'url',  'actions'));
$table->define_headers(array(get_string('configtableheaderdisplaytext', 'block_teacherlinks'),
                             get_string('configtableheaderurl', 'block_teacherlinks'),
                             get_string('actions', 'moodle')));
$table->define_baseurl($baseurl);

$table->set_attribute('cellspacing', '0');
$table->set_attribute('id', 'rssfeeds');
$table->set_attribute('class', 'generaltable generalbox');
$table->column_class('feed', 'feed');
$table->column_class('actions', 'actions');

$table->setup();

$count = 0;
$lastlink = count($links);

foreach ($links as $link) {

    $count++;

    if (!empty($link->displaytext)) {
        $displaytext = s($link->displaytext);
    } else {
        $displaytext = s($link->displaytext);
    }

    $viewlink = html_writer::link($CFG->wwwroot . $baseurl . '?rssid=' . $link->id . $extraparams, $displaytext);

    $feedinfo = '<div class="title">' . $viewlink . '</div>' .
        '<div class="url">' . html_writer::link($link->url, $link->url) .'</div>' .
        '<div class="description">' . $link->displaytext . '</div>';

    $editurl = new moodle_url($editbaseurl, array('urlid' => $link->id));
    $feedicons = $OUTPUT->action_icon($editurl, new pix_icon('t/edit', get_string('edit')));

    $deleteurl = new moodle_url($baseurl, array('deleteurlid' => $link->id, 'sesskey' => sesskey() ));
    $deleteicon = new pix_icon('t/delete', get_string('delete'));
    $feedicons .= $OUTPUT->action_icon($deleteurl,
                                        $deleteicon,
                                        new confirm_action(get_string('deleteurlconfirm', 'block_teacherlinks')));

    if ($link->visible) {
        $hideicon = new pix_icon('t/hide', get_string('hide'));
        $hideurl = new moodle_url($baseurl, array('hideurlid' => $link->id, 'hide' => 1, 'sesskey' => sesskey()));

    } else {
        $hideicon = new pix_icon('t/show', get_string('show'));
        $hideurl = new moodle_url($baseurl, array('hideurlid' => $link->id, 'show' => 1, 'sesskey' => sesskey()));
    }

    $feedicons .= $OUTPUT->action_icon($hideurl, $hideicon);

    if ($count == 1) {
        $feedicons .= $OUTPUT->action_icon(new moodle_url($baseurl, array('movedown' => $link->id)),
                                           new pix_icon('t/down', get_string('down')));

    } else if ($count == $lastlink) {
        $feedicons .= $OUTPUT->action_icon(new moodle_url($baseurl, array('moveup' => $link->id)),
                                           new pix_icon('t/up', get_string('up')));

    } else {
        $feedicons .= $OUTPUT->action_icon(new moodle_url($baseurl, array('moveup' => $link->id)),
                                           new pix_icon('t/up', get_string('up')));
        $feedicons .= $OUTPUT->action_icon(new moodle_url($baseurl, array('movedown' => $link->id)),
                                           new pix_icon('t/down', get_string('down')));

    }
    $table->add_data(array($displaytext, $link->url, $feedicons));
}

$table->print_html();

$url = $CFG->wwwroot . '/blocks/teacherlinks/editurl.php?' . substr($extraparams, 1);
echo '<div class="actionbuttons">' . $OUTPUT->single_button($url, get_string('addnewurl', 'block_teacherlinks'), 'get') . '</div>';

if ($returnurl) {
    echo '<div class="backlink">' . html_writer::link($returnurl, get_string('back')) . '</div>';
}

echo $OUTPUT->footer();
