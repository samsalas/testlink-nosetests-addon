<?php

/* @file exportPythonFile.php
 * @brief Allow to write the Python File using the TestCase ID and the Test Plan XML File
 * From TestLink 1.9.12 XML TestSuite export 
 * @author Samuel Salas (2015)
 * @version 01.00
*/
header('content-type: plain/text; charset=utf-8');

/* Receiving GET info */
$fichier = $_GET["path"];
$testSuiteId = (int) $_GET["testid"];
$testSuiteId_str = (string) $_GET["testid"];
/* Reading Decription XML */
$python = simplexml_load_file($fichier);
$testSuiteName = $python->testsuites->testsuite["name"];
$testSuiteOrder = (int) $python->testsuites->testsuite->node_order;
if((string) $_GET["testid"] == "all") {
   $filename = "TestSuite".sprintf('%03d', $testSuiteOrder)."_CaseAll.py";
} else {
   $filename = "TestSuite".sprintf('%03d', $testSuiteOrder)."_Case".sprintf('%03d', $testSuiteId).".py";
}

header("Content-Disposition: attachment; filename=".$filename);

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

// Loop on the test case (filtering with the test case ID)
include("headerPython.txt");
foreach($python->testsuites->testsuite->testcase as $case) {
   if(($case["internalid"] == $testSuiteId_str)||($testSuiteId_str == "all")) {
      echo class_python("TestSuite".$case['internalid']);
      echo comment_class_python($case["name"]);
      echo function_python("__init__");
      echo "\t\tpass\n\n";
         
      echo function_python("setUp");
      echo "\t\tpass\n\n";
      
      echo function_python("tearDown");
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

?>