<?php

/* @file exportPythonFile.php
 * @brief Allow to write the Python File using the TestCase ID and the Test Plan XML File
 * From TestLink 1.9.12 XML TestSuite export 
 * @author Samuel Salas (2015)
 * @version 01.01
*/
header('content-type: plain/text; charset=utf-8');

/* Receiving GET info */
$testCaseId = (int) $_GET["testcaseid"];
$testSuiteId = (int) $_GET["testsuiteid"];
$filename = "TestSuite".$testSuiteId."_Case".$testCaseId.".py";
header("Content-Disposition: attachment; filename=".$filename);
$testCaseId_str = (string) $_GET["testcaseid"];
$testSuiteId_str = (string) $_GET["testsuiteid"];

 /** 
  * Need the IXR class for client
  */
define("THIRD_PARTY_CODE","../third_party");
require_once THIRD_PARTY_CODE . '/xml-rpc/class-IXR.php';
require_once 'config.php';

$server_url = TL_TESTLINK_SERVERURI . "/lib/api/xmlrpc/v1/xmlrpc.php";

$args=array();
$args["devKey"]=isset($_REQUEST['apiKey']) ? $_REQUEST['apiKey'] : TL_TESTLINK_API_KEY;
$args["testcaseid"]= (int) $_GET["testcaseid"];
$args["version"]=1;

$debug=true;

$clientIXR = new IXR_Client($server_url);
$client->debug=$debug;

if(!$clientIXR->query("tl.getTestCase", $args)) {
   $client=null;
} else {
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
   $tmpStr1 = str_replace($tag, "", $comment);
   // replace special chars
   $tmpStr2 = str_replace("&eacute;", "é", $tmpStr1);
   $tmpStr1 = str_replace("&agrave;", "à", $tmpStr2);
   $tmpStr2 = str_replace("&egrave;", "è", $tmpStr1);
   $tmpStr1 = str_replace("&nbsp;", " ", $tmpStr2);
   $tmpStr2 = str_replace("&ecirc;", "ê", $tmpStr1);
   $tmpStr1 = str_replace("&acirc;", "â", $tmpStr2);
   $tmpStr2 = str_replace("&euml;", "ë", $tmpStr1);
   $tmpStr1 = str_replace("&icirc;", "î", $tmpStr2);
   $tmpStr2 = str_replace("&ugrave;", "ù", $tmpStr1);
   $tmpStr1 = str_replace("&ccedil;", "ç", $tmpStr2);   
   return $tmpStr1;
}

// ----------------------------
// TEXT format functions
// ----------------------------

function function_python($word) {
   return "    def "
         .$word
         ."(self):\r\n";
}

function class_python($word) {
   return "class "
         .$word
         ."(object):\r\n";
}

function comment_file_python($word) {
   $retVal = '""" '."\r\n"
            .'    '.$word."\r\n"
            .'"""'."\r\n";
   return $retVal;
}

function comment_class_python($word) {
   $retVal = "    ".'""" '.$word."\r\n"
            ."    ".'"""'."\r\n";
   return $retVal;
}

function comment_def_python($word) {
   $retVal = "        ".'""" '.rtrim($word)."\r\n"
            ."        ".'"""'."\r\n";
   return $retVal;
}

// ----------------------------
// MAIN : writing the file
// ----------------------------
if ($client !=  null) {
   $case = $client[0];
   include("headerPython.txt");

   // Filtering with the test case ID
   $returnTxt = class_python("TestCase".$case["testcase_id"]);
   $returnTxt .= comment_class_python($case["name"]);

   $returnTxt .= function_python("__init__");
   $returnTxt .= comment_def_python("Initialization of the class");
   $returnTxt .= "        pass\r\n\r\n";
      
   $returnTxt .= function_python("setup");
   $returnTxt .= comment_def_python("Put here actions before the first test case");
   $returnTxt .= "        pass\r\n\r\n";

   $returnTxt .= function_python("teardown");
   $returnTxt .= comment_def_python("Put here actions after the last test case");
   $returnTxt .= "        pass\r\n\r\n";

   foreach($case["steps"] as $step) {
      $returnTxt .= function_python("test_".(string) $step["step_number"]);
      $tmpStr = extract_tagsAndSpecialChar((string) $step["actions"]);
      $returnTxt .= comment_def_python($tmpStr);
      $returnTxt .= "        assert False #Expected result ".extract_tagsAndSpecialChar((string) $step["expected_results"]);
      $returnTxt .= "\r\n\r\n";
   }
} else {
   $returnTxt .= "This page has not converted a Test Case Number from TestLink 1.9.12 into Nosetests Python scripts using the 'class' method.\r\n";
   $returnTxt .= "Something went wrong - " . $clientIXR->getErrorCode() . " - " . $clientIXR->getErrorMessage();
}

echo $returnTxt;

?>
