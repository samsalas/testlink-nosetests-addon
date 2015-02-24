# testlink-nosetests-addon
My contribution to TestLink. This php add-on (to put anywhere on any tool based on php, like testlink or mantis) is able to convert XML test plans (tests to execute) from TestLink into Python nosetests classes.
#How to?
You add the project in a directory yourtool.local/nosetest. You browe then into yourtool.local/nosetest/importPython.php. You select the file to convert. It should be a Testlink Test Plan XML File (go to "Test Execution" and click on "Export Test Plan"). It works with TestLink 1.9.12. The file uploaded will be saved into the directory "nosetest/upload". Then you have the Python nosetests scripts templates ready.
# What is the point?
The point of the addon is to provide easyly a Python testing script from the Test Plan to execute to the coding of the execution in Python. It is then possible to export the noestests results into JUnit format and Cobertura to benefit from the results.
# Nosetest / Jenkins
To use nosetests : <code>nosetests -v testplan.py</code>
To link the results to jenkins, use the XML files produced by the command <code>nosetests -v testplan.py --with-xunit --with-xcoverage</code>. More details are in the headerPython.txt file.
