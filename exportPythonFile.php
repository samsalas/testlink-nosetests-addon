<?php

/* @file exportPythonFile.php
 * @brief Allow to write the Python File using the TestCase ID and the Test Plan XML File
 * From TestLink 1.9.12 XML TestSuite export 
 * @author Samuel Salas (2015)
 * @version 01.01
*/
header('content-type: plain/text; charset=utf-8');

/* Receiving GET info */
header("Content-Disposition: attachment; filename=".(string) $_GET["filename"]);
$fichier = $_GET["path"];
$testCaseId = (int) $_GET["testcaseid"];
$testCaseId_str = (string) $_GET["testcaseid"];
$testSuiteId = (int) $_GET["testsuiteid"];
$testSuiteId_str = (string) $_GET["testsuiteid"];
/* Reading Decription XML */
$python = simplexml_load_file($fichier);

// ----------------------------
// Global functions
// ----------------------------

/** @brief Return string without tags and special chars
 * @param $comment string to process
 * @return Return the string without the special char in the array
 */
function extract_tagsAndSpecialChar($comment) {
   $tag = array("<p>", "</p>", "\n", "\t");
   return str_replace($tag, "", $comment);
}

// ----------------------------
// TEXT format functions
// ----------------------------

function function_python($word) {
   return "\tdef "
         .$word
         ."(self):\n";
}

function class_python($word) {
   return "class "
         .$word
         ."(object):\n";
}

function comment_file_python($word) {
   $retVal = '""" '."\n"
            .'    '.$word."\n"
            .'"""'."\n";
   return $retVal;
}

function comment_class_python($word) {
   $retVal = "\t".'""" '.$word."\n"
            ."\t".'"""'."\n";
   return $retVal;
}

function comment_def_python($word) {
   $retVal = "\t\t".'""" '.rtrim($word)."\n"
            ."\t\t".'"""'."\n";
   return $retVal;
}

// ----------------------------
// MAIN : writing the file
// ----------------------------

include("headerPython.txt");
// Loop on the test suites
foreach($python->testsuites->testsuite as $suite) {
   // filtering with the test suite node order
   if(((int) $suite->node_order == $testSuiteId)||($testSuiteId_str == "all")) {
      echo comment_file_python($suite["name"]);
      // Loop on the test case (
      foreach($suite->testcase as $case) {
         // Filtering with the test case ID
         if(($case["internalid"] == $testCaseId_str)||($testCaseId_str == "all")) {
            echo class_python("TestCase".$case['internalid']);
            echo comment_class_python($case["name"]);
            echo function_python("__init__");
            echo comment_def_python("Initialization of the class");
            echo "\t\tpass\n\n";
               
            echo function_python("setUp");
            echo comment_def_python("Put here actions before the first test case");
            echo "\t\tpass\n\n";
            
            echo function_python("tearDown");
            echo comment_def_python("Put here actions after the last test case");
            echo "\t\tpass\n\n";
            
            foreach($case->steps->step as $step) {
               echo function_python("test_".(string) $step->step_number);
               $tmpStr = extract_tagsAndSpecialChar((string) $step->actions);
               echo comment_def_python($tmpStr);
               echo "\t\tasset False #Expected result ".extract_tagsAndSpecialChar((string) $step->expectedresults);
               echo "\n\n";
            }
         }
      }
   }
}



?>