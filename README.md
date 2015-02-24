# testlink-nosetests-addon
My contribution to TestLink. This php add-on (to put anywhere on any php website) is able to convert XML test plans (tests to execute) from TestLink into Python nosetests classes.
#how to ?
You add the project in a directory www.yourtool.local/nosetest. You browe then into www.yourtool.local/nosetest/importPython.php. You select the file to convert. It should be a Testlink Test Plan XMl File (go to "Test Execution" and click on "Export Test Plan"). It works with TestLink 1.9.12. The file uploaded will be saved into the directory "nosetest/upload". Then you have the Python nosetests scripts templates ready.
