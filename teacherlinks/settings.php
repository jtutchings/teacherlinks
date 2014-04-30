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

$settings->add(new admin_setting_heading('block_teacherlinks_general',
                                         get_string('generalbehaviourheading', 'block_teacherlinks'),
                                         get_string('generalbehaviourtext', 'block_teacherlinks')));

$settings->add(new admin_setting_configtext(
            'teacherlinks/frontpage_link_text',
            get_string('labelfrontpagelinktext', 'block_teacherlinks'),
            get_string('descfrontpagelinktext', 'block_teacherlinks'),
            ''
        ));
$settings->add(new admin_setting_configcheckbox(
            'teacherlinks/allow_self_enrol',
            get_string('labelallowserlfenrol', 'block_teacherlinks'),
            get_string('descallowserlfenrol', 'block_teacherlinks'),
            ''
        ));


$settings->add(new admin_setting_confightmleditor(
            'teacherlinks/general_tab_text',
            get_string('labelgeneraltabdisplayname', 'block_teacherlinks'),
            get_string('descgeneraltabdisplayname', 'block_teacherlinks'),
            ''
        ));

$settings->add(new admin_setting_heading('block_teacherlinks_block',
                                         get_string('blockbehaviourheading', 'block_teacherlinks'),
                                         get_string('blockbehaviourtext', 'block_teacherlinks')));
$settings->add(new admin_setting_confightmleditor(
            'teacherlinks/block_top_text',
            get_string('labelblocktoptext', 'block_teacherlinks'),
            get_string('descblocktoptext', 'block_teacherlinks'),
            ''
        ));
$settings->add(new admin_setting_confightmleditor(
            'teacherlinks/block_footer_text',
            get_string('labelblockfootertext', 'block_teacherlinks'),
            get_string('descblockfootertext', 'block_teacherlinks'),
            ''
        ));

//
// Front Page Settings.
//

$settings->add(new admin_setting_heading('block_teacherlinks_frontpage',
                                         get_string('frontpagebehaviourheading', 'block_teacherlinks'),
                                         get_string('frontpagebehaviourtext', 'block_teacherlinks')));
$settings->add(new admin_setting_configtext(
            'teacherlinks/frontpage_header',
            get_string('labelfrontpageheader', 'block_teacherlinks'),
            get_string('descfrontpageheader', 'block_teacherlinks'),
            ''
        ));

$settings->add(new admin_setting_confightmleditor(
            'teacherlinks/frontpage_top_text',
            get_string('labelfrontpagetoptext', 'block_teacherlinks'),
            get_string('descfrontpagetoptext', 'block_teacherlinks'),
            ''
        ));
$settings->add(new admin_setting_confightmleditor(
            'teacherlinks/frontpage_footer_text',
            get_string('labelfrontpagefootertext', 'block_teacherlinks'),
            get_string('descfrontpagefootertext', 'block_teacherlinks'),
            ''
        ));