<?php

/* @file exportPython.php
 * @brief Result page of the export into the Python File using the TestCase ID and the Test Plan XML File
 * From TestLink 1.9.12 XML TestSuite export 
 * @author Samuel Salas (2015)
 * @version 01.01
*/

header('content-type: text/html; charset=utf-8');

 /** 
  * Need the IXR class for client
  */
define("THIRD_PARTY_CODE","../third_party");
require_once THIRD_PARTY_CODE . '/xml-rpc/class-IXR.php';
require_once 'config.php';


$server_url = TL_TESTLINK_SERVERURI . "/lib/api/xmlrpc/v1/xmlrpc.php";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : TL_TESTLINK_API_KEY;
$args["testcaseid"]= (int) $_POST["testcase"];
$args["version"]=1;

$debug=true;

$clientIXR = new IXR_Client($server_url);
$client->debug=$debug;

if(!$clientIXR->query("tl.getTestCase", $args))
{		
   $client=null;
}
else
{
   $client=$clientIXR->getResponse();
}

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
// HTML fromat functions 
// (no comment because it's obvious)
// ----------------------------

function span_color($word, $color) {
   return "<span style='color: ".$color."; font-weight: bold;'>".$word."</span>";
}

function keyword_python($word) {
   return span_color($word, "blue");
}

function function_python($word) {
   return "\t"
         .keyword_python("def")
         ." "
         .span_color($word, "#FA58F4")
         ."(self):\n";
}

function class_python($word) {
   return keyword_python("class")
         ." "
         .span_color($word, "#FA58F4")
         ."(object):\n";
}

function comment_class_python($word) {
   $retVal = "\t".'""" '.$word."\n"
            ."\t".'"""'."\n";
   return span_color($retVal, "#B40404");
}

function comment_def_python($word) {
   $retVal = "\t\t".'""" '.rtrim($word)."\n"
            ."\t\t".'"""'."\n";
   return span_color($retVal, "#B40404");
}

// ----------------------------
// MAIN : writing the HTML to dispay
// (we could use smarty + template)
// ----------------------------

echo "<html lang='fr'>\n";
echo "<head>\n";
echo "<link rel='stylesheet' href='export.css' type='text/css' media='screen' />\n";
echo "</head>\n";

echo "<body>\n";
echo "<div class='title'>\n";
echo "TestLink 1.9.12 > Python Nosetests";
echo "</div>\n";

if ($client !=  null) {
   echo "<div class='general'>\n";
   echo "This page has converted a Test Case Number from TestLink 1.9.12 into <a href='https://nose.readthedocs.org' target='blank'>Nosetests</a> Python scripts using the 'class' method.\n";
   echo "</div>\n";

   echo "<div class='subtitle'>\n";
   echo "Python Code";
   echo "</div>\n";
/*echo "<pre>";
print_r($client);
echo "</pre>";*/
   $case = $client[0];
   echo "<pre>";
   echo "Test Case Name : ";
   echo $case["name"];
   echo "\n";
   echo "Test Case ID : ";
   $testCaseId = $case["testcase_id"];
   $testSuiteId = $case["testsuite_id"];
   echo $testCaseId;
   echo "\n";
   echo "Test Case Filename : ";
   $filename = "TestSuite".sprintf('%03d', $testSuiteId)."_Case".sprintf('%03d', $testCaseId).".py";
   echo "<a href='exportPythonFileTC.php?testsuiteid=".$testSuiteId."&testcaseid=".$testCaseId."'>".$filename."</a>";
   echo "\n";
   
   echo "<code>\n";
   echo class_python("TestCase".$testCaseId);
   echo comment_class_python($case["name"]);
   echo function_python("__init__");
   echo comment_def_python("Initialization of the class");
   echo "\t\t".keyword_python("pass")."\n\n";
      
   echo function_python("setUp");
   echo comment_def_python("Put here actions before the first test case");
   echo "\t\t".keyword_python("pass")."\n\n";
   
   echo function_python("tearDown");
   echo comment_def_python("Put here actions after the last test case");
   echo "\t\t".keyword_python("pass")."\n\n";
   
   // Loop on the steps for each test case
   foreach($case["steps"] as $step) {
      echo function_python("test_".(string) $step["step_number"]);
      $tmpStr = extract_tagsAndSpecialChar((string) $step["actions"]);
      echo comment_def_python($tmpStr);
      echo "\t\t".keyword_python("asset")." False ".span_color("#Expected result"." ".extract_tagsAndSpecialChar((string) $step["expected_results"]), "#31B404");
      echo "\n\n";
   }
   echo "</code></pre>\n";
   
} else {
   echo "<div class='general'>\n";
   echo "This page has not converted a Test Case Number from TestLink 1.9.12 into <a href='https://nose.readthedocs.org' target='blank'>Nosetests</a> Python scripts using the 'class' method.\n";
   echo "<br />\n";
   echo "Something went wrong - " . $clientIXR->getErrorCode() . " - " . $clientIXR->getErrorMessage();	
   echo "</div>\n";
   
}
echo "</body>\n";

echo "</html>\n";

?>
