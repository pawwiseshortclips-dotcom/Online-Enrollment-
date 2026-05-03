<?php
$subjectFile = __DIR__ . '/subjects.json';
$subjects = [];
if (file_exists($subjectFile)) {
    $json = file_get_contents($subjectFile);
    $decoded = json_decode($json, true);
    if (is_array($decoded)) {
        $subjects = $decoded;
    }
}

if (!is_array($subjects) || count($subjects) === 0) {
    $subjects = [
        ['code' => 'ISP 300', 'title' => 'Project Management', 'units' => 3, 'time' => '8:00-9:30 AM', 'day' => 'MWF'],
        ['code' => 'ISP 301', 'title' => 'Evaluation of Business Performance', 'units' => 3, 'time' => '10:00-11:30 AM', 'day' => 'TTH'],
        ['code' => 'ISP 302', 'title' => 'Application Development Emerging and Technologies', 'units' => 3, 'time' => '1:00-2:30 PM', 'day' => 'MWF'],
        ['code' => 'ISP 303', 'title' => 'IS Strategy and Management Acquisition', 'units' => 3, 'time' => '3:00-4:30 PM', 'day' => 'TTH'],
        ['code' => 'ISP 304', 'title' => 'IS Innovation and New Technologies', 'units' => 3, 'time' => '7:00-8:30 AM', 'day' => 'Sat'],
    ];
}
