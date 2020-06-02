<?php

function local_apprenticeoffjob_extend_navigation_course($navigation, $course, $context) {
    if (has_capability('report/apprenticeoffjob:view', $context)) {
        $url = new moodle_url('/local/apprenticeoffjob/index.php');
        $navigation->add(get_string('navigationlink', 'local_apprenticeoffjob'), $url, navigation_node::TYPE_SETTING, null, null, new pix_icon('i/report', ''));
    }
}
