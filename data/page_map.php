<?php
/**
 * This file is part of the Medlib\Textbooks component.
 *
 * @filename page_map.php contains a data map to specify
 * which sets of textbooks appear on each page.
 *
 * @version 0.1
 * @configFor Renderer.php
 * @author Keith Engwall
 * @copyright (c) Oakland University William Beaumont School of Medicine Library
 * @license MIT
 */

/**
 * Use include() to pass the contents of this file into a variable:
 * $page_map = include('page_map.php');
 */
return [
    /**
     * The page map is formatted as follows:
     *  'page' => [
     *      'PAGEKEY' => [
     *          'title' => 'Page Title',
     *          'map' => [
     *              'SETKEY' => 'Set Title',
     *              ...
     *          ],
     *          'map_sort' => 'key|value'
     *      ], ...
     *
     * The PAGEKEY will be the string passed to the render method to
     * display the page.
     *
     * Each SETKEY should match a key used in the key field in textbook_data,
     * and maps to the title of the set.
     *
     * You may specify whether to sort the list of sets by key (SETKEY) or
     * value (title of the set).  If map_sort is not specified, the renderer
     * will sort by set titles.
     *
     * Although it is possible to use the same SETKEY on multiple pages,
     * that will result in the same textbook set being displayed on each
     * page.
     */
    'pages' => [
        'M1' => [
            'title' => 'M1 Textbooks (2020/2021 Academic Year)',
            'description' => [
                'The OUWB Medical Library keeps at least two 
            copies of all curricular textbooks in the KL102 Study Room. 
            Textbooks for M1 Students are listed below by course. Required 
            textbooks are denoted as such after the title. Please see your 
            syllabus for details.',
            'In addition, you can find course-related information on our 
            <a href="https://medlib.oakland.edu/guides/m1_m2_guides.php">M1/M2 
            Guides</a> page.',
            'Note: Many electronic books are only available in the most current 
            edition.'
            ],
            'map' => [
                "AFCP"       => "Anatomical Foundations of Clinical Practice",
                "APM12"      => "Art &amp; Practice of Medicine 1 &amp; 2",
                "BFCP"       => "Biomedical Foundations of Clinical Practice",
                "CARDIO"     => "Cardiovascular",
                "EMBARK"     => "Embark",
                "HEMATO"     => "Hematopoietic &amp; Lymphoid",
                "HUM"        => "Medical Humanities &amp; Clinical Bioethics",
                "NEURO1"     => "Neuroscience 1",
                "PMH"        => "Promotion &amp; Maintenance of Health",
                "RESP"       => "Respiratory"
            ],
            'map_sort' => 'value'
        ],
        'M2' => [
            'title' => 'M1 Textbooks (2020/2021 Academic Year)',
            'description' => [
                'The OUWB Medical Library keeps at least two 
            copies of all curricular textbooks in the KL102 Study Room. 
            Textbooks for M2 Students are listed below by course. Required 
            textbooks are denoted as such after the title. Please see your 
            syllabus for details.',
            'In addition, you can find course-related information on our 
            <a href="https://medlib.oakland.edu/guides/m1_m2_guides.php">M1/M2 
            Guides</a> page.',
            'Note: Many electronic books are only available in the most current 
            edition.'
            ],
            'map' => [
                "APM345"     => "Art &amp; Practice of Medicine 3, 4 &amp; 5",
                "BEHAV"      => "Behavioral Science",
                "EBM"        => "Integrative Evidence-Based Medicine",
                "ENDO"       => "Endocrinology",
                "GASTRO"     => "Gastroenterology &amp; Hepatology",
                "HUM"        => "Medical Humanities &amp; Clinical Bioethics",
                "MSK"        => "Musculoskeletal, Connective Tissue &amp; Skin",
                "NEURO2"     => "Neuroscience 2",
                "PSYCH"      => "Psychopathology",
                "RENAL"      => "Renal &amp; Urinary",
                "REPRO"      => "Male &amp; Female Reproductive"
            ],
            'map_sort' => 'value'
        ],
        'M3' => [
            'title' => 'M1 Textbooks (2020/2021 Academic Year)',
            'description' => [
                'The OUWB Medical Library keeps two copies of all 
            curricular textbooks in the KL102 Study Room. Copies of the textbooks 
            are also available in an OUWB Reserves collection at the Beaumont 
            Health System Library. Clerkship textbooks are listed below by clerkship 
            rotation. Required textbooks are denoted as such after the title.'
            ],
            'map' => [
                "FAMMED"     => "Family Medicine Clerkship",
                "HUM"        => "Medical Humanities &amp; Clinical Bioethics",
                "INTMED"     => "Internal Medicine Clerkship",
                "NEUROLOGY"  => "Neurology Clerkship",
                "OBGYN"      => "OB-GYN Clerkship",
                "OPHTH"      => "Ophthalmology Clerkship",
                "PED"        => "Pediatrics Clerkship",
                "PSYCHIATRY" => "Psychiatry Clerkship",
                "SURGERY"    => "Surgery Clerkship"
            ],
            'map_sort' => 'value'
        ],
        'M4' => [
            'title' => 'M4 Textbooks (2020/2021 Academic Year)',
            'description' => [
                'The OUWB Medical Library keeps two copies of all 
            curricular textbooks in the KL102 Study Room. Copies of the textbooks 
            are also available in an OUWB Reserves collection at the Beaumont 
            Health System Library. Clerkship textbooks are listed below by clerkship 
            rotation. Required textbooks are denoted as such after the title.'
            ],
            'map' => [
                "ANESTH"     => "Anesthesiology &amp; Pain Medicine Clerkship",
                "DIAG"       => "Diagnostic Medicine Clerkship",
                "EMERGENCY"  => "Emergency Medicine Clerkship",
                "HUM"        => "Medical Humanities &amp; Clinical Bioethics",
                "INTMEDM4"   => "Internal Medicine Sub-Internship"
            ],
            'map_sort' => 'value'
        ]
    ]
];
?>
