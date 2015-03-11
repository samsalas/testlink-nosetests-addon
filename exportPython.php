<?php

/* @file exportPython.php
 * @brief Result page of the export into the Python File using the TestCase ID and the Test Plan XML File
 * From TestLink 1.9.12 XML TestSuite export 
 * @author Samuel Salas (2015)
 * @version 01.01
*/

header('content-type: text/html; charset=utf-8');

// ----------------------------
// UPLOAD ANALYSE
// This part was insipred by "CertaiN" published at http://php.net/manual/en/features.file-upload.php
// ----------------------------
$upload_dir = "uploads";

try {
    
    // Undefined | Multiple Files | $_FILES Corruption Attack
    // If this request falls under any of them, treat it invalid.
    if (
        !isset($_FILES['upfile']['error']) ||
        is_array($_FILES['upfile']['error'])
    ) {
        throw new RuntimeException('Invalid parameters.');
    }

    // Check $_FILES['upfile']['error'] value.
    switch ($_FILES['upfile']['error']) {
        case UPLOAD_ERR_OK:
            break;
        case UPLOAD_ERR_NO_FILE:
            throw new RuntimeException('No file sent.');
        case UPLOAD_ERR_INI_SIZE:
        case UPLOAD_ERR_FORM_SIZE:
            throw new RuntimeException('Exceeded filesize limit.');
        default:
            throw new RuntimeException('Unknown errors.');
    }

    // You should also check filesize here. 
    if ($_FILES['upfile']['size'] > 1000000) {
        throw new RuntimeException('Exceeded filesize limit.');
    }

    // Check extension
    $info = pathinfo($_FILES['upfile']['name']);
    $ext = pathinfo($_FILES['upfile']['name'], PATHINFO_EXTENSION);
    if ($info["extension"] != "xml") { 
        throw new RuntimeException('Invalid file format. Should be XML');
    }

    // On this example, we obtain a safe and unique name from its binary data.
    $sha1filename = sprintf('%s.%s', sha1($_FILES['upfile']['tmp_name']), $ext);
    $filenamepath = sprintf("%s/%s", $upload_dir, $sha1filename); //
    if (!move_uploaded_file($_FILES['upfile']['tmp_name'], $filenamepath)) {
        throw new RuntimeException('Failed to move uploaded file.');
    }
   $error_upload = "";
   
   $correct_upload = "File <span style='font-weight: bold;'>".$_FILES['upfile']['name']."</span> has been uploaded successfully.";

} catch (RuntimeException $e) {

    $error_upload = $e->getMessage();

}

// ----------------------------
// XML Reading functions
// ----------------------------
$python = simplexml_load_file($filenamepath);

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
if ($error_upload == "") {
   echo "<div class='general'>\n";
   echo "This page has converted a Test Plan XML File from TestLink 1.9.12 into <a href='https://nose.readthedocs.org' target='blank'>Nosetests</a> Python scripts using the 'class' method.\n";
   echo "<br />\n";
   echo "Source : ";
   echo $correct_upload;
   echo "<br />\n";
   echo "Project : ";
   echo $python->testproject->name;
   echo "<br />\n";
   echo "Platform : ";
   echo $python->platform->name;
   echo "<br />\n";
   echo "Build : ";
   echo $python->build->name;
   echo "<br />\n";
   echo "</div>\n";

   echo "<div class='subtitle'>\n";
   echo "Python Code";
   echo "</div>\n";

   echo "<pre style='text-align: center;'>";
   $filename = "TestSuites_AllCases.py";
   echo "Download all the classes in one file <a href='exportPythonFile.php?testsuiteid=all&testcaseid=all&filename=".$filename."&path=".$filenamepath."'>".$filename."</a>";
   echo "</pre>\n";
   // Loop on the test suites
   foreach($python->testsuites->testsuite as $suite) {
      echo "<div class='subtitle'>\n";
      echo "Test Suite ".$suite["name"];
      echo "</div>\n";
      $testSuiteOrder = (int) $suite->node_order;
      $testSuiteId = (string) $suite->node_order;
      echo "<pre style='text-align: center;'>";
      $filename = "TestSuite".sprintf('%03d', $testSuiteOrder)."_AllCases.py";
      echo "Download all the classes of this Test Suite in one file <a href='exportPythonFile.php?testsuiteid=".$testSuiteId."&testcaseid=all&filename=".$filename."&path=".$filenamepath."'>".$filename."</a>";
      echo "</pre>\n";
      // Loop on the test cases
      foreach($suite->testcase as $case) {
         echo "<pre>";
         echo "Test Case Name : ";
         echo $case["name"];
         echo "\n";
         echo "Test Case ID : ";
         $testCaseId = $case["internalid"];
         echo $testCaseId;
         echo "\n";
         echo "Test Case Filename : ";
         $filename = "TestSuite".sprintf('%03d', $testSuiteOrder)."_Case".sprintf('%03d', $testCaseId).".py";
         echo "<a href='exportPythonFile.php?testsuiteid=".$testSuiteId."&testcaseid=".$testCaseId."&filename=".$filename."&path=".$filenamepath."'>".$filename."</a>";
         echo "\n";
         
         echo "<code>\n";
         echo class_python("TestCase".$case["internalid"]);
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
         foreach($case->steps->step as $step) {
            echo function_python("test_".(string) $step->step_number);
            $tmpStr = extract_tagsAndSpecialChar((string) $step->actions);
            echo comment_def_python($tmpStr);
            echo "\t\t".keyword_python("asset")." False ".span_color("#Expected result"." ".extract_tagsAndSpecialChar((string) $step->expectedresults), "#31B404");
            echo "\n\n";
         }
         echo "</code></pre>\n";
      }
   }
   
} else {
   echo "<div class='general'>\n";
   echo "This page has not converted a Test Suite XML File from TestLink 1.9.12 into <a href='https://nose.readthedocs.org' target='blank'>Nosetests</a> Python scripts using the 'class' method.\n";
   echo "<br />\n";
   echo "Source : ";
   echo "<span style='color: red; font-weight: bold;'>".$error_upload."</span>\n";
   echo "</div>\n";
}
echo "</body>\n";

echo "</html>\n";

?>