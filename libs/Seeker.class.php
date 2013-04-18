<?php

    /* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

    /**
     * This is a command line search tool for text files in a DIR.
     *
     * This program is free software; you can redistribute it and/or modify
     * it under the terms of the GNU General Public License as published by
     * the Free Software Foundation; either version 2 of the License, or
     * (at your option) any later version.
     *
     * This program is distributed in the hope that it will be useful,
     * but WITHOUT ANY WARRANTY; without even the implied warranty of
     * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
     * GNU General Public License for more details.
     *
     * You should have received a copy of the GNU General Public License along
     * with this program; if not, write to the Free Software Foundation, Inc.,
     * 59 Temple Place - Suite 330, Boston, MA 02111-1307, USA.
     * http://www.gnu.org/copyleft/gpl.html
     *
     * @package     Seeker
     * @author      Nicholas Dunnaway
     * @copyright   2007 php|uber.leet
     * @license     http://www.gnu.org/copyleft/gpl.html
     * @link        http://uber.leetphp.com
     * @version     CVS: $Id: Seeker.class.php,v 1.2 2007/04/02 17:03:11 ndunnaway Exp $
     * @since       File available since Release 1.01
     *
     */


    error_reporting(E_ALL); // Show all Errors

    /**
     * This is the Seeker Class
     *
     * @package Seeker
     */
    class Seeker
    {
        /**
         * This controls if debug is printed or not.
         *
         * @var bool
         * @access Public
         */
        var $bDebug = false;

        /**
         * This controls if more detailed info is printed.
         *
         * @var bool
         * @access Public
         */
        var $bVerbose = false;

        /**
         * This is either a string or an array to the path we want to search.
         *
         * If an array is passed each location in the array is
         * searched one at a time.
         *
         * @var mixed
         * @access Public
         */
        var $mHayStack = null;

        /**
         * This is either a string, array, or filename.
         *
         * If it is a filename then the file is opened and each line in
         * the file is used as the needle.
         *
         * If this is an array then each item in the array is used as
         * the needle one at a time.
         *
         * @var mixed
         * @access Public
         */
        var $mNeedle = null;

        /**
         * This is set by the OS the Script is running on.
         *
         * @var string
         * @access Private
         */
        var $sPathSlash = null;

        /**
         * This is the name of the file that the results are written to.
         * The user running the program will need write access to this file.
         *
         * @var string
         * @access Public
         */
        var $sResultFile = null;

        /**
         * When calling the script from the Windows CLI it does not allow for UNC
         * paths to be the "working path" do it defaults to c:\winnt or c:\windows
         * (Just where ever windows is installed).
         *
         * We use the Absolute Path to keep the user from writing to the windows
         * folder.
         *
         * @var string
         * @access Private
         */
        var $sAbsolutePath = null;

        /**
         * This breaks down an array passed via the needle and sends each part
         * as a string to the StringSearch() function.
         *
         * @param array $aNeedle
         * @access Private
         */
        function ArraySearch($aNeedle)
        {
            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg(__FUNCTION__ . '(' . $aNeedle . ') Called.');
            }

            // Validate Needle
            if (!isset($aNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $aNeedle not set.' . "\n");
            }

            // Validate Needle is an array.
            if (!is_array($aNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $aNeedle not an array.' . "\n");
            }

            // Needle is an array. We need to break it down.
            foreach ($aNeedle as $value)
            {
                // Check if needle is a string.
                if (is_string($value))
                {
                    $this->StringSearch($value);
                }
                elseif (is_array($value)) // Check if we have another array.
                {
                    // Recursive
                    $this->ArraySearch($value);
                }
                else
                {
                    die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() Value passed in $aNeedle not a string or an array.' . "\n");
                }
            }
        }

        /**
         * This validates that both the needle and haystack are set to some value.
         *
         * @access Private
         */
        function CheckIsSet()
        {
            // Validate Needle
            if (!isset($this->mNeedle))
            {
                die('Error: Needle not set.' . "\n");
            }

            // Validate Haystack
            if (!isset($this->mHayStack))
            {
                die('Error: Haystack not set.' . "\n");
            }

            // Validate Result File
            if (!isset($this->sResultFile))
            {
                die('Error: Result File is not set.' . "\n");
            }
            return;
        }

        /**
         * This checks if the Dir passed is a valid Dir.
         *
         * @param string $sDir
         * @access Private
         */
        function CheckDir($sDir)
        {
            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg(__FUNCTION__ . '(' . $sDir . ') Called.');
            }

            // Validate $sDir
            if (!isset($sDir))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $sDir not set.' . "\n");
            }

            // Check if we have an array. If so we need to break it down.
            if (is_array($sDir))
            {
                foreach ($sDir as $value) {
                	$this->CheckDir($value); // Recursion
                }
                return;
            }

            // Validate $sDir is an dir.
            if (!is_dir($sDir))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $sDir . ') $sDir is a ' . var_dump_ret($sDir) . 'This is not a valid dir.' . "\n");
            }

            return;
        }

        /**
         * This Function takes the needle and searches each file in a haystack.
         *
         * @param string $sNeedle
         * @param string $sHayStack
         * @access Private
         */
        function ExtractData($sNeedle, $sHayStack)
        {
            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg(__FUNCTION__ . '(' . $sNeedle . ', ' . $sHayStack . ') Called.');
            }

            // Validate Needle
            if (!isset($sNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $sNeedle not set.' . "\n");
            }

            // Validate Needle is string.
            if (!is_string($sNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $sNeedle . ') $sNeedle not a string.' . "\n");
            }

            // Validate Haystack
            if (!isset($sHayStack))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $sHayStack not set.' . "\n");
            }

            // Check if Haystack is a array
            if (is_array($sHayStack))
            {
                foreach ($sHayStack as $value) {
                    $this->ExtractData($sNeedle, $value); // Recursion
                }
                return;
            }

            // Validate Haystack is string.
            if (!is_string($sHayStack))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $sHayStack . ') $sHayStack not a string.' . "\n");
            }

            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg('Opening Dir: (' . $sHayStack . ') for input.');
            }

            $dp_DirList = opendir($sHayStack); // Open the Dir for reading.

			while (($sFileName = readdir($dp_DirList)) !== false)
			{
                // We want to skip . and ..
                if ($sFileName != '.' && $sFileName != '..' && !is_dir($sHayStack . $this->sPathSlash . $sFileName))
                {
                    // This does not print by default. Only if the user
                    // enables verbose or debug mode.
                    if ($this->bVerbose || $this->bDebug)
                    {
				        printMsg('Searching File: (' . $sHayStack . $this->sPathSlash . $sFileName . ')');
                    }

                    // Open File.
        			$fp_Haystack = fopen($sHayStack . $this->sPathSlash . $sFileName, "r") or die(printMsg('Error: Unable to read file. (' . $sFileName . ')'));
        			while(!feof($fp_Haystack))
        			{
        				$sHaystackData = fgets($fp_Haystack, 4096);  // Read File
        				if (!empty($sHaystackData))
        				{
        				    // Search the Haystack Date for the Needle.
                            if (strpos($sHaystackData, $sNeedle) !== false)
                            {
                                // The needle was found.
                                // This does not print by default. Only if the user enables verbose
                                // or debug mode.
                                if ($this->bVerbose || $this->bDebug)
                                {
            				        printMsg('Needle: (' . $sNeedle . ') was found in file (' . $sHayStack . $this->sPathSlash . $sFileName . ')');
                                }

                                // There was a match. We are going to record this to a file.
                                $this->WriteToFile(trim($sNeedle) . "\t" . $sHayStack . $this->sPathSlash . $sFileName . "\t" . trim($sHaystackData) . "\r\n");

                                // This does not print by default. Only if the user enables debug mode.
                                if ($this->bDebug)
                                {
            				        printMsg('Data that matched needle: ' . $sHaystackData);
                                }

                            }
        				}
        			}
                    // Close File.
                    fclose($fp_Haystack);
                }
			}
			closedir($dp_DirList);
        }

        /**
         * This breaks down a file passed via the needle and sends each part
         * as a string to the StringSearch() function.
         *
         * @param array $fNeedle
         * @access Private
         */
        function FileSearch($fNeedle)
        {
            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg(__FUNCTION__ . '(' . $fNeedle . ') Called.');
            }

            // Validate Needle
            if (!isset($fNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $fNeedle not set.' . "\n");
            }

            // Validate Needle is a file.
            if (!is_file($fNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $fNeedle . ') $fNeedle not a file.' . "\n");
            }

            // This does not print by default. Only if the user enables debug mode.
            if ($this->bVerbose || $this->bDebug)
            {
                printMsg('Opening File: (' .$fNeedle . ') for input.');
            }

            // Open File.
			$fp_Needle = fopen($fNeedle, "r") or die(printMsg('Error: Unable to read file. (' . $fNeedle . ')'));
			while(!feof($fp_Needle))
			{
				$sNeedle = fgets($fp_Needle, 4096);  // Read File
			    $sNeedle = trim($sNeedle);           // Remove White Space.
			    $sNeedle = trim($sNeedle, "\n");     // Remove New Lines.
				if (!empty($sNeedle))
				{
                    $this->StringSearch($sNeedle);   // Pass the Needle to the SearchString()
				}
			}
            // Close File.
            fclose($fp_Needle);
        }

        /**
         * This is called by the user once Haystack and Needle are set.
         * This starts the searching.
         *
         * @access Public
         */
        function Search()
        {
            $this->CheckIsSet(); // Check that all values were passed.
            $this->CheckDir($this->mHayStack); // Check that haystack is a valid dir.

            // Check if needle is an array.
            if (is_array($this->mNeedle))
            {
                $this->ArraySearch($this->mNeedle);
                return;
            }

            // Check if needle is an file.
            if (is_file($this->mNeedle))
            {
                $this->FileSearch($this->mNeedle);
                return;
            }

            // Check if needle is a string.
            if (is_string($this->mNeedle))
            {
                $this->StringSearch($this->mNeedle);
                return;
            }

            die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $this->mNeedle is a ' . var_dump_ret($this->mNeedle) . 'This is not a valid value.' . "\n");
        }

        /**
         * Constructor
         *
         * @access Private
         * @return Seeker
         */
        function Seeker()
        {
            // Set a default name for the result file.
            $this->sResultFile = 'Seeker.Result.' . date('M-d-Y_zHis') . '.txt' ;
//            $this->bDebug = true;
//            $this->bVerbose = true;

            // We need to figure out what OS we are on.
            if (isset($_ENV['OS']) && strpos($_ENV['OS'], 'Windows') !== false)
            {
                // This does not print by default. Only if the user enables debug mode.
                if ($this->bDebug)
                {
                    printMsg('The OS is Windows.');
                }
                $this->sPathSlash = '\\';
            }
            elseif (isset($_ENV['PWD']) && strpos($_ENV['PWD'], '/') !== false)
            {
                // This does not print by default. Only if the user enables debug mode.
                if ($this->bDebug)
                {
                    printMsg('The OS is *nix.');
                }
                $this->sPathSlash = '/';
            }
            else
            {
                // This does not print by default. Only if the user enables debug mode.
                if ($this->bDebug)
                {
                    printMsg('Unable to determine the OS type. Assuming *nix.');
                }
                $this->sPathSlash = '/';
            }

            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg('Using (' . $this->sPathSlash . ') as the path slash.');
            }

            $this->sAbsolutePath = '\\\\helpdesk00506\c$\Inetpub\scripts\seeker\\';

            return;
        }

        /**
         * When the Needle is a string we will use this function.
         *
         * @access Private
         * @param string $sNeedle
         */
        function StringSearch($sNeedle)
        {
            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg(__FUNCTION__ . '(' . $sNeedle . ') Called.');
            }

            $this->CheckIsSet(); // Check that all values were passed.

            // Validate Needle
            if (!isset($sNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $sNeedle not set.' . "\n");
            }

            // Validate Needle is string.
            if (!is_string($sNeedle))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $sNeedle . ') $sNeedle not a string.' . "\n");
            }

            printMsg('Searching for (' . $sNeedle . ')');

            // If Haystack is an array we want to loop over it.
            if (is_array($this->mHayStack))
            {
                foreach ($this->mHayStack as $value) {
                	$this->ExtractData($sNeedle, $value); // Extract the Data.
                }
                return;
            }

            // Both the needle and haystack are a string.
            $this->ExtractData($sNeedle, $this->mHayStack);
            return;
        }

        /**
         * This Writes to the Output file.
         *
         * @param string $sMessage
         * @access Private
         */
        function WriteToFile($sMessage)
        {
            // This does not print by default. Only if the user enables debug mode.
            if ($this->bDebug)
            {
                printMsg(__FUNCTION__ . '(' . $sMessage . ') Called.');
            }

            // Validate message was set.
            if (!isset($sMessage))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $sMessage not set.' . "\n");
            }

            // Validate Output File is set.
            if (!is_string($sMessage))
            {
                die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $sMessage . ') $sMessage not a string.' . "\n");
            }

            // Validate Output File is set.
            if (!isset($this->sResultFile))
            {
                die('Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $this->sResultFile not set.' . "\n");
            }

            // Validate Output File is a valid file name.
            if (!is_string($this->sResultFile))
            {
                die('Error: ' . __CLASS__ . '::' . __FUNCTION__ . '(' . $this->sResultFile . ') $this->sResultFile not a valid filename.' . "\n");
            }

            // Everything looks ok. Now we need to open the file for writing.
            if (!$fp_write = fopen($this->sAbsolutePath . $this->sResultFile, 'a')) {
                die('Unable to open or create ' . $this->sResultFile . "\n");
            }

            // File is open. Lets write the message to it.
            if (fwrite($fp_write, $sMessage) === FALSE) {
               die('Unable to write to ' . $this->sResultFile);
            }

			fclose($fp_write); // Close File.

        }

    }

    /**
     * This captues and returns the result of var_dump().
     *
     * This was taken from http://us2.php.net/manual/en/function.var-dump.php
     * edwardzyang at thewritingpot dot com posted this for people to use.
     *
     * @param mixed $mixed
     * @return string
     */
    function var_dump_ret($mixed = null)
    {
      ob_start();
      var_dump($mixed);
      $content = ob_get_contents();
      ob_end_clean();
      return $content;
    }

    /**
     * This prints a message to the CLI.
     *
     * @param string $sMessage
     */
    function printMsg($sMessage)
    {
        // Validate Message
        if (!isset($sMessage))
        {
            die('Script Error: ' . __CLASS__ . '::' . __FUNCTION__ . '() $sMessage not set.' . "\n");
        }
        echo $sMessage . "\n";
    }

?>
