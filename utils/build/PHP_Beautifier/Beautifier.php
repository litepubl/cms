<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * Contents Php_Beautifier class and make some tests
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category  PHP
 * @package   PHP_Beautifier
 * @author    Claudio Bustos <cdx@users.sourceforge.com>
 * @copyright 2004-2010 Claudio Bustos
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   CVS: $Id:$
 * @link      http://pear.php.net/package/PHP_Beautifier
 * @link      http://beautifyphp.sourceforge.net
 */
error_reporting(E_ALL);
// Before all, test the tokenizer extension
if (!extension_loaded('tokenizer')) {
    throw new Exception("Compile php with tokenizer extension. Use --enable-tokenizer or don't use --disable-all on configure.");
}
require_once 'pear/PEAR.php';
require_once 'PEAR/Exception.php';
/**
 * Require PHP_Beautifier_Filter
 */
require_once 'Beautifier/Filter.php';
/**
 * Require PHP_Beautifier_Filter_Default
 */
require_once 'Beautifier/Filter/Default.filter.php';
/**
 * Require PHP_Beautifier_Common
 */
require_once 'Beautifier/Common.php';
/**
 * Require Log
 */
require_once 'pear/Log.php';
/**
 * Require Exceptions
 */
require_once 'Beautifier/Exception.php';
/**
 * Require StreamWrapper
 */
require_once 'Beautifier/StreamWrapper.php';
/**
 * PHP_Beautifier
 *
 * Class to beautify php code
 * How to use:
 * # Create a instance of the object
 * # Define the input and output files
 * # Optional: Set one or more Filter. They are processed in LIFO order (last in, first out)
 * # Process the file
 * # Get it, save it or show it.
 *
 * <code>
 * $oToken = new PHP_Beautifier(); // create a instance
 * $oToken->addFilter('ArraySimple');
 * $oToken->addFilter('ListClassFunction'); // add one or more filters
 * $oToken->setInputFile(__FILE__); // nice... process the same file
 * $oToken->process(); // required
 * $oToken->show();
 * </code>
 *
 * @todo create a web interface.
 * @category  PHP
 * @package   PHP_Beautifier
 * @author    Claudio Bustos <cdx@users.sourceforge.com>
 * @copyright 2004-2010 Claudio Bustos
 * @license   http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version   Release: @package_version@
 * @link      http://pear.php.net/package/PHP_Beautifier
 * @link      http://beautifyphp.sourceforge.net
 */
class PHP_Beautifier implements PHP_Beautifier_Interface
{
    // public
    
    /**
     * Tokens created by the tokenizer
     * @var array
     */
    public $aTokens = array();
    /**
     * Tokens codes assigned to method on Filter
     * @var array
     */
    public $aTokenFunctions = array();
    /**
     * Token Names
     * @var array
     */
    public $aTokenNames = Array();
    /**
     * Stores the output
     * @var array
     */
    public $aOut = array();
    /**
     * Contains the assigment of modes
     * @var array
     * @see setMode()
     * @see unsetMode()
     * @see getMode()
     */
    public $aModes = array();
    /**
     * List of availables modes
     * @var array
     */
    public $aModesAvailable = array(
        'ternary_operator',
        'double_quote'
    );
    /**
     * Settings for the class
     * @var array
     */
    public $aSettings = array();
    /**
     * Index of current token
     * @var int
     */
    public $iCount = 0;
    /**
     * Chars for indentation
     * @var int
     */
    public $iIndentNumber = 4;
    /**
     * Level of array nesting
     * @var int
     */
    public $iArray = 0;
    /**
     * Level of ternary operator nesting
     * @var int
     */
    public $iTernary = 0;    
    /**
     * Level of parenthesis nesting
     * @var int
     */
    public $iParenthesis = 0;
    /**
     * Level of verbosity (debug)
     * @var int
     */
    public $iVerbose = false;
    /**
     * Name of input file
     * @var string
     */
    public $sInputFile = '';
    /**
     * Name of output file
     * @var string
     */
    public $sOutputFile = '';
    /**
     * Type of newline
     * @var string
     */
    public $sNewLine = PHP_EOL;
    /**
     * Type of whitespace to use for indent
     * @var string
     */
    public $sIndentChar = ' ';
    /**
     * Save the last whitespace used. Use only for Filter! (i miss friends of C++ :( )
     * @var string
     */
    public $currentWhitespace = '';
    /**
     * Association $aTokens=>$aOut
     * @var array
     */
    public $aAssocs = array();
    /**
     * Current token. Could be changed by a filter (See Lowercase)
     * @var array
     */
    public $aCurrentToken = array();
    
    // private
    
    /**
     * type of file
     */
    private $sFileType = 'php';
    /**
     * Chars of indent
     * @var int
     */
    private $iIndent = 0;
    /**
     * @var int
     */
    private $aIndentStack = array();
    /** Text to beautify */
    private $sText = '';
    /** Constant for last Control */
    private $iControlLast;
    /** References to PHP_Beautifier_Filter's */
    private $aFilters = array();
    /**
     * Stack with control construct
     */
    private $aControlSeq = array();
    /**
     * List of construct that start control structures
     */
    private $aControlStructures = array();
    /**
     * List of Control for parenthesis
     */
    private $aControlParenthesis = array();
    /** 
     * List of construct that end control structures
     */
    private $aControlStructuresEnd = array();
    /** Dirs for Filters */
    private $aFilterDirs = array();
    /** Flag for beautify/no beautify mode */
    private $bBeautify = true;
    /** Log */
    private $oLog;
    /** Before new line holder */
    private $sBeforeNewLine = null;
    /** Activate or deactivate 'no delete previous space' */
    private $bNdps = false;
    /** Mark the begin of the end of a DoWhile sequence **/
    private $doWhileBeginEnd;
    // Methods
    
    /**
     * Constructor.
     * Assing values to {@link $aControlStructures},{@link $aControlStructuresEnd},
     * {@link $aTokenFunctions}
     * 
     * @access public
     * @return void
     */
    public function __construct() 
    {
        $this->aControlStructures = array(
            T_CLASS,
            T_FUNCTION,
            T_IF,
            T_ELSE,
            T_ELSEIF,
            T_WHILE,
            T_DO,
            T_FOR,
            T_FOREACH,
            T_SWITCH,
            T_DECLARE,
            T_TRY,
            T_CATCH
        );
        $this->aControlStructuresEnd = array(
            T_ENDWHILE,
            T_ENDFOREACH,
            T_ENDFOR,
            T_ENDDECLARE,
            T_ENDSWITCH,
            T_ENDIF
        );
        $aPreTokens = preg_grep('/^T_/', array_keys(get_defined_constants()));
        foreach ($aPreTokens as $sToken) {
            $this->aTokenNames[constant($sToken) ] = $sToken;
            $this->aTokenFunctions[constant($sToken) ] = $sToken;
        }
        if (!defined('T_NAMESPACE')) { // like pre 5.3.0
            define('T_NAMESPACE', 377);
        }
        $aTokensToChange = array(
            /* QUOTES */
            '"' => "T_DOUBLE_QUOTE",
            "'" => "T_SINGLE_QUOTE",
            /* PUNCTUATION */
            '(' => 'T_PARENTHESIS_OPEN',
            ')' => 'T_PARENTHESIS_CLOSE',
            ';' => 'T_SEMI_COLON',
            '{' => 'T_OPEN_BRACE',
            '}' => 'T_CLOSE_BRACE',
            ',' => 'T_COMMA',
            '?' => 'T_QUESTION',
            ':' => 'T_COLON',
            '=' => 'T_ASSIGMENT',
            '<' => 'T_EQUAL',
            '>' => 'T_EQUAL',
            '.' => 'T_DOT',
            '[' => 'T_OPEN_SQUARE_BRACE',
            ']' => 'T_CLOSE_SQUARE_BRACE',
            /* OPERATOR*/
            '+' => 'T_OPERATOR',
            '-' => 'T_OPERATOR',
            '*' => 'T_OPERATOR',
            '/' => 'T_OPERATOR',
            '%' => 'T_OPERATOR',
            '&' => 'T_OPERATOR',
            '|' => 'T_OPERATOR',
            '^' => 'T_OPERATOR',
            '~' => 'T_OPERATOR',
            '!' => 'T_OPERATOR_NEGATION',
            T_SL => 'T_OPERATOR',
            T_SR => 'T_OPERATOR',
            T_OBJECT_OPERATOR => 'T_OBJECT_OPERATOR',
            /* INCLUDE */
            T_INCLUDE => 'T_INCLUDE',
            T_INCLUDE_ONCE => 'T_INCLUDE',
            T_REQUIRE => 'T_INCLUDE',
            T_REQUIRE_ONCE => 'T_INCLUDE',
            /* LANGUAGE CONSTRUCT */
            T_FUNCTION => 'T_LANGUAGE_CONSTRUCT',
            T_PRINT => 'T_LANGUAGE_CONSTRUCT',
            T_RETURN => 'T_LANGUAGE_CONSTRUCT',
            T_ECHO => 'T_LANGUAGE_CONSTRUCT',
            T_NEW => 'T_LANGUAGE_CONSTRUCT',
            T_CLASS => 'T_LANGUAGE_CONSTRUCT',
            T_VAR => 'T_LANGUAGE_CONSTRUCT',
            T_GLOBAL => 'T_LANGUAGE_CONSTRUCT',
            T_THROW => 'T_LANGUAGE_CONSTRUCT',
            /* CONTROL */
            T_IF => 'T_CONTROL',
            T_DO => 'T_CONTROL',
            T_WHILE => 'T_CONTROL',
            T_SWITCH => 'T_CONTROL',
            /* ELSE */
            T_ELSEIF => 'T_ELSE',
            T_ELSE => 'T_ELSE',
            /* ACCESS PHP 5 */
            T_INTERFACE => 'T_ACCESS',
            T_FINAL => 'T_ACCESS',
            T_ABSTRACT => 'T_ACCESS',
            T_PRIVATE => 'T_ACCESS',
            T_PUBLIC => 'T_ACCESS',
            T_PROTECTED => 'T_ACCESS',
            T_CONST => 'T_ACCESS',
            T_STATIC => 'T_ACCESS',
            /* COMPARATORS */
            T_PLUS_EQUAL => 'T_ASSIGMENT_PRE',
            T_MINUS_EQUAL => 'T_ASSIGMENT_PRE',
            T_MUL_EQUAL => 'T_ASSIGMENT_PRE',
            T_DIV_EQUAL => 'T_ASSIGMENT_PRE',
            T_CONCAT_EQUAL => 'T_ASSIGMENT_PRE',
            T_MOD_EQUAL => 'T_ASSIGMENT_PRE',
            T_AND_EQUAL => 'T_ASSIGMENT_PRE',
            T_OR_EQUAL => 'T_ASSIGMENT_PRE',
            T_XOR_EQUAL => 'T_ASSIGMENT_PRE',
            T_DOUBLE_ARROW => 'T_ASSIGMENT',
            T_SL_EQUAL => 'T_EQUAL',
            T_SR_EQUAL => 'T_EQUAL',
            T_IS_EQUAL => 'T_EQUAL',
            T_IS_NOT_EQUAL => 'T_EQUAL',
            T_IS_IDENTICAL => 'T_EQUAL',
            T_IS_NOT_IDENTICAL => 'T_EQUAL',
            T_IS_SMALLER_OR_EQUAL => 'T_EQUAL',
            T_IS_GREATER_OR_EQUAL => 'T_EQUAL',
            /* LOGICAL*/
            T_LOGICAL_OR => 'T_LOGICAL',
            T_LOGICAL_XOR => 'T_LOGICAL',
            T_LOGICAL_AND => 'T_LOGICAL',
            T_BOOLEAN_OR => 'T_LOGICAL',
            T_BOOLEAN_AND => 'T_LOGICAL',
            /* SUFIX END */
            T_ENDWHILE => 'T_END_SUFFIX',
            T_ENDFOREACH => 'T_END_SUFFIX',
            T_ENDFOR => 'T_END_SUFFIX',
            T_ENDDECLARE => 'T_END_SUFFIX',
            T_ENDSWITCH => 'T_END_SUFFIX',
            T_ENDIF => 'T_END_SUFFIX',
        );
        if (version_compare(PHP_VERSION, '5.3.0', '>=')) {
            $aTokensToChange[T_NAMESPACE] = 'T_INCLUDE';
            $aTokensToChange[T_USE] = 'T_INCLUDE';
        }
        foreach ($aTokensToChange as $iToken => $sFunction) {
            $this->aTokenFunctions[$iToken] = $sFunction;
        }
        $this->addFilterDirectory(dirname(__FILE__) . '/Beautifier/Filter');
        $this->addFilter('Default');
        $this->oLog = PHP_Beautifier_Common::getLog();
    }

    /**
     * getTokenName 
     * 
     * @param mixed $iToken Token
     *
     * @access public
     * @return void
     */
    public function getTokenName($iToken) 
    {
        if (!$iToken) {
            throw new Exception("Token $iToken doesn't exists");
        }
        return $this->aTokenNames[$iToken];
    }

    /**
     * Start the log for debug
     * 
     * @param string $sFile  Filename
     * @param mixed  $iLevel Debug Level. See {$link Log}
     *
     * @access public
     * @return void
     */
    public function startLog($sFile = 'php_beautifier.log', $iLevel = PEAR_LOG_DEBUG) 
    {
        @unlink($sFile);
        $oLogFile = Log::factory('file', $sFile, 'php_beautifier', array(), PEAR_LOG_DEBUG);
        $this->oLog->addChild($oLogFile);
    }

    /**
     * Add a filter directory
     * 
     * @param mixed $sDir Paht to directory
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public function addFilterDirectory($sDir) 
    {
        $sDir = PHP_Beautifier_Common::normalizeDir($sDir);
        if (file_exists($sDir)) {
            array_push($this->aFilterDirs, $sDir);
        } else {
            throw new Exception_PHP_Beautifier_Filter("Path '$sDir' doesn't exists");
        }
    }
    /**
     * Return an array with all the Filter Dirs
     * 
     * @access public
     * @return array List of Filter Directories
     */
    public function getFilterDirectories() 
    {
        return $this->aFilterDirs;
    }
    /**
     * addFilterObject 
     * 
     * @param PHP_Beautifier_Filter $oFilter PHP_Beautifier_Filter Object
     *
     * @access public
     * @return void
     */
    public function addFilterObject(PHP_Beautifier_Filter $oFilter) 
    {
        array_unshift($this->aFilters, $oFilter);
        return true;
    }

    /**
     * Add a Filter to the Beautifier
     * The first argument is the name of the file of the Filter.
     * 
     * @param mixed $mFilter   Name of the Filter
     * @param array $aSettings Settings for the Filter
     *
     * @tutorial PHP_Beautifier/Filter/Filter2.pkg#use
     * @access public
     * @return bool true if Filter is loaded, false if the same filter was loaded previously
     * @throws Exception
     */
    public function addFilter($mFilter, $aSettings = array()) 
    {
        if ($mFilter instanceOf PHP_Beautifier_Filter) {
            return $this->addFilterObject($mFilter);
        }
        $sFilterClass = 'PHP_Beautifier_Filter_' . $mFilter;
        if (!class_exists($sFilterClass)) {
            $this->addFilterFile($mFilter);
        }
        $oTemp = new $sFilterClass($this, $aSettings);
        // verify if same Filter is loaded
        if (in_array($oTemp, $this->aFilters, true)) {
            return false;
        } elseif ($oTemp instanceof PHP_Beautifier_Filter) {
            $this->addFilterObject($oTemp);
        } else {
            throw new Exception_PHP_Beautifier_Filter("'$sFilterClass' isn't a subclass of 'Filter'");
        }
    }

    /**
     * Removes a Filter
     * 
     * @param mixed $sFilter Name of the Filter
     *
     * @access public
     * @return bool true if Filter is removed, false otherwise
     */
    public function removeFilter($sFilter) 
    {
        $sFilterName = strtolower('PHP_Beautifier_Filter_' . $sFilter);
        foreach ($this->aFilters as $sId => $oFilter) {
            if (strtolower(get_class($oFilter)) == $sFilterName) {
                unset($this->aFilters[$sId]);
                return true;
            }
        }
        return false;
    }

    /**
     * Return the Filter Description
     * 
     * @param mixed $sFilter Name of the filter
     *
     * @access public
     * @return mixed string or false
     * @see PHP_Beautifier_Filter::__toString();
     */
    public function getFilterDescription($sFilter) 
    {
        $aFilters = $this->getFilterListTotal();
        if (in_array($sFilter, $aFilters)) {
            $this->addFilterFile($sFilter);
            $sFilterClass = 'PHP_Beautifier_Filter_' . $sFilter;
            $oTemp = new $sFilterClass($this, array());
            return $oTemp;
        } else {
            return false;
        }
    }

    /**
     * Add a new filter to the processor.
     * The system will process the filter in LIFO order
     * 
     * @param mixed $sFilter Name of the filter
     *
     * @access private
     * @see process()
     * @return bool
     * @throws  Exception
     */
    private function addFilterFile($sFilter) 
    {
        $sFilterClass = 'PHP_Beautifier_Filter_' . $sFilter;
        if (class_exists($sFilterClass)) {
            return true;
        }
        foreach ($this->aFilterDirs as $sDir) {
            $sFile = $sDir . $sFilter . '.filter.php';
            if (file_exists($sFile)) {
                include_once $sFile;
                if (class_exists($sFilterClass)) {
                    return true;
                } else {
                    throw new Exception_PHP_Beautifier_Filter("File '$sFile' exists,but doesn't exists filter '$sFilterClass'");
                }
            }
        }
        throw new Exception_PHP_Beautifier_Filter("Doesn't exists filter '$sFilter'");
    }

    /**
     * Get the names of the loaded filters
     * 
     * @access public
     * @return array list of Filters
     */
    public function getFilterList() 
    {
        foreach ($this->aFilters as $oFilter) {
            $aOut[] = $oFilter->getName();
        }
        return $aOut;
    }

    /**
     * Get the list of all available Filters in all the include Dirs
     * 
     * @access public
     * @return array list of Filters
     */
    public function getFilterListTotal() 
    {
        $aFilterFiles = array();
        foreach ($this->aFilterDirs as $sDir) {
            $aFiles = PHP_Beautifier_Common::getFilesByPattern($sDir, ".*?\.filter\.php");
            array_walk(
                $aFiles,
                array(
                    $this,
                    'getFilterList_FilterName'
                )
            );
            $aFilterFiles = array_merge($aFilterFiles, $aFiles);
        }
        sort($aFilterFiles);
        return $aFilterFiles;
    }

    /**
     * Receive a path to a filter and replace it with the name of filter
     * 
     * @param mixed &$sFile File name
     *
     * @access private
     * @return void
     */
    private function getFilterList_FilterName(&$sFile) 
    {
        $aMatch=array();
        preg_match("/\/([^\/]*?)\.filter\.php/", $sFile, $aMatch);
        $sFile = $aMatch[1];
    }
    /**
     * getIndentChar 
     * 
     * @access public
     * @return void
     */
    public function getIndentChar() 
    {
        return $this->sIndentChar;
    }
    /**
     * getIndentNumber 
     * 
     * @access public
     * @return void
     */
    public function getIndentNumber() 
    {
        return $this->iIndentNumber;
    }
    /**
     * getIndent 
     * 
     * @access public
     * @return void
     */
    public function getIndent() 
    {
        return $this->iIndent;
    }
    /**
     * getNewLine 
     * 
     * @access public
     * @return void
     */
    public function getNewLine() 
    {
        return $this->sNewLine;
    }

    /**
     * Character used for indentation
     * 
     * @param string $sChar Usually ' ' or "\t"
     *
     * @access public
     * @return void
     */
    public function setIndentChar($sChar) 
    {
        $this->sIndentChar = $sChar;
    }

    /**
     * Number of characters for indentation
     * 
     * @param int $iIndentNumber Usually 4 for space or 1 for tabs
     *
     * @access public
     * @return void
     */
    public function setIndentNumber($iIndentNumber) 
    {
        $this->iIndentNumber = $iIndentNumber;
    }

    /**
     * Character used as a new line
     * 
     * @param string $sNewLine ussualy "\n", "\r\n" or "\r"
     *
     * @access public
     * @return void
     */
    public function setNewLine($sNewLine) 
    {
        $this->sNewLine = $sNewLine;
    }

    /**
     * Set the file for beautify
     * 
     * @param string $sFile Path to file
     *
     * @access public
     * @return void
     * @throws Exception
     */
    public function setInputFile($sFile) 
    {
        $bCli = (php_sapi_name() == 'cli');
        if (strpos($sFile, '://') === false and !file_exists($sFile) and !($bCli and $sFile == STDIN)) {
            throw new Exception("File '$sFile' doesn't exists");
        }
        $this->sText = '';
        $this->sInputFile = $sFile;
        $fp = ($bCli and $sFile == STDIN) ? STDIN : fopen($sFile, 'r');
        do {
            $data = fread($fp, 8192);
            if (strlen($data) == 0) {
                break;
            }
            $this->sText.= $data;
        } while (true);
        if (!($bCli and $fp == STDIN)) {
            fclose($fp);
        }
        return true;
    }

    /**
     * Set the output file for beautify
     * 
     * @param string $sFile Path to file
     *
     * @access public
     * @return void
     */
    public function setOutputFile($sFile) 
    {
        $this->sOutputFile = $sFile;
    }

    /**
     * Save the beautified code to output file
     * 
     * @param string $sFile path to file. If null, {@link $sOutputFile} if exists, throw exception otherwise
     *
     * @access public
     * @return void
     * @see setOutputFile();
     * @throws Exception
     */
    public function save($sFile = null) 
    {
        $bCli = (php_sapi_name() == 'cli');
        if (!$sFile) {
            if (!$this->sOutputFile) {
                throw new Exception("Can't save without a output file");
            } else {
                $sFile = $this->sOutputFile;
            }
        }
        $sText = $this->get();
        $fp = ($bCli and $sFile == STDOUT) ? STDOUT : @fopen($sFile, "w");
        if (!$fp) {
            throw new Exception("Can't save file $sFile");
        }
        fputs($fp, $sText, strlen($sText));
        if (!($bCli and $sFile == STDOUT)) {
            fclose($fp);
        }
        $this->oLog->log("Success: $sFile saved", PEAR_LOG_INFO);
        return true;
    }

    /**
     * Set a string for beautify
     * 
     * @param string $sText Must be preceded by open tag
     *
     * @access public
     * @return void
     */
    public function setInputString($sText) 
    {
        $this->sText = $sText;
    }
     
    /**
     * Reset all properties
     * 
     * @access private
     * @return void
     */
    private function resetProperties() 
    {
        $aProperties = array(
            'aTokens' => array() ,
            'aOut' => array() ,
            'aModes' => array() ,
            'iCount' => 0,
            'iIndent' => 0
            /*$this->iIndentNumber*/
            ,
            'aIndentStack' => array(
                /*$this->iIndentNumber*/
            ) ,
            'iArray' => 0,
            'iParenthesis' => 0,
            'currentWhitespace' => '',
            'aAssocs' => array() ,
            'iControlLast' => null,
            'aControlSeq' => array() ,
            'bBeautify' => true
        );
        foreach ($aProperties as $sProperty => $sValue) {
            $this->$sProperty = $sValue;
        }
    }

    /**
     * Process the string or file to beautify
     * 
     * @access public
     * @return bool true on success
     * @throws Exception
     */
    public function process() 
    {
        $this->oLog->log('Init process of ' . (($this->sInputFile) ? 'file ' . $this->sInputFile : 'string'), PEAR_LOG_DEBUG);
        $this->resetProperties();
        // if file type is php, use token_get_all
        // else, use a class named PHP_Beautifier_Tokenizer_XXX
        // instanced with the text and get the tokens with
        // getTokens()
        if ($this->sFileType == 'php') {
            $this->aTokens = token_get_all($this->sText);
        } else {
            $sClass = 'PHP_Beautifier_Tokenizer_' . ucfirst($this->sFileType);
            if (class_exists($sClass)) {
                $oTokenizer = new $sClass($this->sText);
                $this->aTokens = $oTokenizer->getTokens();
            } else {
                throw new Exception("File type " . $this->sFileType . " not implemented");
            }
        }
        $this->aOut = array();
        $iTotal = count($this->aTokens);
        $iPrevAssoc = false;
        // Send a signal to the filter, announcing the init of the processing of a file
        foreach ($this->aFilters as $oFilter) {
            $oFilter->preProcess();
        }
        for ($this->iCount = 0 ; $this->iCount < $iTotal ; $this->iCount++) {
            $aCurrentToken = $this->aTokens[$this->iCount];
            if (is_string($aCurrentToken)) {
                $aCurrentToken = array(
                    0 => $aCurrentToken,
                    1 => $aCurrentToken
                );
            }
            // ArrayNested->off();
            $sTextLog = PHP_Beautifier_Common::wsToString($aCurrentToken[1]);
            // ArrayNested->on();
            $sTokenName = (is_numeric($aCurrentToken[0])) ? token_name($aCurrentToken[0]) : '';
            $this->oLog->log("Token:" . $sTokenName . "[" . $sTextLog . "]", PEAR_LOG_DEBUG);
            $this->controlToken($aCurrentToken);
            $iFirstOut = count($this->aOut); //5
            $bError = false;
            $this->aCurrentToken=$aCurrentToken;
            if ($this->bBeautify) {
                foreach ($this->aFilters as $oFilter) {
                    $bError = true;
                    if ($oFilter->handleToken($this->aCurrentToken) !== false) {
                        $this->oLog->log('Filter:' . $oFilter->getName(), PEAR_LOG_DEBUG);
                        $bError = false;
                        break;
                    }
                }
            } else {
                $this->add($aCurrentToken[1]);
            }
            $this->controlTokenPost($aCurrentToken);
            $iLastOut = count($this->aOut);
            // set the assoc
            if (($iLastOut-$iFirstOut) > 0) {
                $this->aAssocs[$this->iCount] = array(
                    'offset' => $iFirstOut
                );
                if ($iPrevAssoc !== false) {
                    $this->aAssocs[$iPrevAssoc]['length'] = $iFirstOut-$this->aAssocs[$iPrevAssoc]['offset'];
                }
                $iPrevAssoc = $this->iCount;
            }
            if ($bError) {
                throw new Exception("Can'process token: " . var_dump($aCurrentToken));
            }
        } // ~for
        // generate the last assoc
        if (count($this->aOut) == 0) {
            if ($this->sFile) {
                throw new Exception("Nothing on output for " . $this->sFile . "!");
            } else {
                throw new Exception("Nothing on output!");
            }
        }
        $this->aAssocs[$iPrevAssoc]['length'] = (count($this->aOut) -1) -$this->aAssocs[$iPrevAssoc]['offset'];
        // Post-processing
        foreach ($this->aFilters as $oFilter) {
            $oFilter->postProcess();
        }
        $this->oLog->log('End process', PEAR_LOG_DEBUG);
        return true;
    }

    /**
     * Get the reference to {@link $aOut}, based on the number of the token
     * 
     * @param int $iIndex Token number
     *
     * @access public
     * @return mixed false array or false if token doesn't exists
     */
    public function getTokenAssoc($iIndex) 
    {
        return (array_key_exists($iIndex, $this->aAssocs)) ? $this->aAssocs[$iIndex] : false;
    }

    /**
     * Get the output for the specified token
     * 
     * @param int $iIndex Token number
     *
     * @access public
     * @return mixed string or false if token doesn't exists
     */
    public function getTokenAssocText($iIndex) 
    {
        if (!($aAssoc = $this->getTokenAssoc($iIndex))) {
            return false;
        }
        return (implode('', array_slice($this->aOut, $aAssoc['offset'], $aAssoc['length'])));
    }

    /**
     * Replace the output for specified token
     * 
     * @param int    $iIndex Token Number
     * @param string $sText  Replace text
     *
     * @access public
     * @return bool
     */
    public function replaceTokenAssoc($iIndex, $sText) 
    {
        if (!($aAssoc = $this->getTokenAssoc($iIndex))) {
            return false;
        }
        $this->aOut[$aAssoc['offset']] = $sText;
        for ($x = 0 ; $x < $aAssoc['length']-1 ; $x++) {
            $this->aOut[$aAssoc['offset']+$x+1] = '';
        }
        return true;
    }

    /**
     * Return the function for a token constant or string.
     * 
     * @param mixed $sTokenType Token constant or string
     *
     * @access public
     * @return mixed name of function or false
     */
    public function getTokenFunction($sTokenType) 
    {
        return (array_key_exists($sTokenType, $this->aTokenFunctions)) ? strtolower($this->aTokenFunctions[$sTokenType]) : false;
    }

    /**
     * Process a callback from the code to beautify
     * 
     * @param array $aMatch third parameter from preg_match
     *
     * @access private
     * @return bool
     * @uses controlToken()
     */
    private function processCallback($aMatch) 
    {
        if (stristr('php_beautifier', $aMatch[1]) and method_exists($this, $aMatch[3])) {
            if (preg_match("/^(set|add)/i", $aMatch[3]) and !stristr('file', $aMatch[3])) {
                eval('$this->' . $aMatch[2] . ";");
                return true;
            }
        } else {
            foreach ($this->aFilters as $iIndex => $oFilter) {
                if (strtolower(get_class($oFilter)) == 'php_beautifier_filter_' . strtolower($aMatch[1]) and method_exists($oFilter, $aMatch[3])) {
                    eval('$this->aFilters[' . $iIndex . ']->' . $aMatch[2] . ";");
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * Assign value for some variables with the information of the token
     * 
     * @param array $aCurrentToken Current token
     *
     * @access private
     * @return void
     */
    private function controlToken($aCurrentToken) 
    {
        // is a control structure opener?
        if (in_array($aCurrentToken[0], $this->aControlStructures)) {
            $this->pushControlSeq($aCurrentToken);
            $this->iControlLast = $aCurrentToken[0];
        }
        // is a control structure closer?
        if (in_array($aCurrentToken[0], $this->aControlStructuresEnd)) {
            $this->popControlSeq();
        }
        switch ($aCurrentToken[0]) {
        case T_COMMENT:
            // callback!
            $aMatch=array();
            if (preg_match("/\/\/\s*(.*?)->((.*)\((.*)\))/", $aCurrentToken[1], $aMatch)) {
                try {
                    $this->processCallback($aMatch);
                }
                catch(Exception $oExp) {
                }
            }
            break;

        case T_FUNCTION:
            $this->setMode('function');
            break;

        case T_CLASS:
            $this->setMode('class');
            break;

        case T_ARRAY:
            $this->iArray++;
            break;

        case T_WHITESPACE:
            $this->currentWhitespace = $aCurrentToken[1];
            break;

        case '{':
            if ($this->isPreviousTokenConstant(T_VARIABLE) or ($this->isPreviousTokenConstant(T_STRING) and $this->getPreviousTokenConstant(2) == T_OBJECT_OPERATOR) or $this->isPreviousTokenConstant(T_OBJECT_OPERATOR)) {
                $this->setMode('string_index');
            }
            break;

        case '(':
            $this->iParenthesis++;
            $this->pushControlParenthesis();
            break;

        case ')':
            $this->iParenthesis--;
            break;

        case '?':
            $this->setMode('ternary_operator');
            $this->iTernary++;
            break;

        case '"':
            ($this->getMode('double_quote')) ? $this->unsetMode('double_quote') : $this->setMode('double_quote');
            break;

        case T_START_HEREDOC:
            $this->setMode('double_quote');
            break;

        case T_END_HEREDOC:
            $this->unsetMode('double_quote');
            break;
        }
        if ($this->getTokenFunction($aCurrentToken[0]) == 't_include') {
            $this->setMode('include');
        }
    }
     
    /**
     * Assign value for some variables with the information of the token, after processing
     * 
     * @param array $aCurrentToken Current token
     *
     * @access private
     * @return void
     */
    private function controlTokenPost($aCurrentToken) 
    {
        switch ($aCurrentToken[0]) {
        case ')':
            if ($this->iArray) {
                $this->iArray--;
            }
            $this->popControlParenthesis();
            break;

        case '}':
            if ($this->getMode('string_index')) {
                $this->unsetMode('string_index');
            } else {
                $prevIndex = 1;
                while ($this->isPreviousTokenConstant(array(T_COMMENT, T_DOC_COMMENT), $prevIndex)) {
                    $prevIndex ++;
                }

                $this->oLog->log('end bracket:' . $this->getPreviousTokenContent($prevIndex), PEAR_LOG_DEBUG);
                
                if ($this->isPreviousTokenContent(array(';','}','{'), $prevIndex)) {
                    if (end($this->aControlSeq)!=T_DO) {
                        $this->popControlSeq();
                    } else {
                        $this->DoWhileBeginEnd=true;
                    }
                }
            }
            break;

        case ';':
            // If is a while in a do while structure
            if (isset($this->aControlSeq) && (end($this->aControlSeq)==T_WHILE)) {
                $counter = 0;
                $openParenthesis = 0;
                do {
                    $counter++;
                    $prevToken = $this->getPreviousTokenContent($counter);
                    if ($prevToken == "(") {
                        $openParenthesis++;
                    }
                } while ($prevToken!="{" && $prevToken!="while");
                if ($prevToken=="while" && $openParenthesis==1) {
                    if (isset($this->DoWhileBeginEnd) && $this->DoWhileBeginEnd) {
                        $this->popControlSeq();
                        $this->DoWhileBeginEnd=false;
                    }
                    $this->popControlSeq();
                }
            }
            break;

        case '{':
            $this->unsetMode('function');
            break;
        }
        if ($this->getTokenFunction($aCurrentToken[0]) == 't_colon') {
            if ($this->iTernary) {
                    $this->iTernary--;
            }
            if (!$this->iTernary) {
                $this->unsetMode('ternary_operator');
            }
        }
    }

    /**
     * Push a control construct to the stack
     * 
     * @param array $aToken Current token
     *
     * @access private
     * @return void
     */
    private function pushControlSeq($aToken) 
    {
        $this->oLog->log('Push Control:' . $aToken[0] . "->" . $aToken[1], PEAR_LOG_DEBUG);
        array_push($this->aControlSeq, $aToken[0]);
    }

    /**
     * Pop a control construct from the stack
     * 
     * @access private
     * @return int token constant
     */
    private function popControlSeq() 
    {
        $aEl = array_pop($this->aControlSeq);
        if ($aEl) {
            $this->oLog->log('Pop Control:' . $this->getTokenName($aEl) , PEAR_LOG_DEBUG);
        } else {
            $this->oLog->log('Pop Unknown Control', PEAR_LOG_DEBUG);
        }
        return $aEl;
    }
    /**
     * Push a new Control Instruction on the stack
     * 
     * @access private
     * @return void
     */
    private function pushControlParenthesis() 
    {
        $iPrevious = $this->getPreviousTokenConstant();
        $this->oLog->log("Push Parenthesis: $iPrevious ->" . $this->getPreviousTokenContent(), PEAR_LOG_DEBUG);
        array_push($this->aControlParenthesis, $iPrevious);
    }
    /**
     */

    /**
     * Pop the last Control instruction for parenthesis from the stack
     * 
     * @access private
     * @return int constant
     */
    private function popControlParenthesis() 
    {
        $iPop = array_pop($this->aControlParenthesis);
        $this->oLog->log('Pop Parenthesis:' . $iPop, PEAR_LOG_DEBUG);
        return $iPop;
    }

    /**
     * Set the filetype
     * 
     * @param mixed $sType Type
     *
     * @access public
     * @return void
     */
    public function setFileType($sType) 
    {
        $this->sFileType = $sType;
    }

    /**
     * Set the Beautifier on or off
     * 
     * @param mixed $sFlag true or false
     *
     * @access public
     * @return void
     */
    public function setBeautify($sFlag) 
    {
        $this->bBeautify = (bool)$sFlag;
    }

    /**
     * Show the beautified code
     * 
     * @access public
     * @return void
     */
    public function show() 
    {
        echo $this->get();
    }

    /**
     * Activate or deactivate this ominous hack
     * If you need to maintain some special whitespace
     * you can activate this hack and use (delete the space between * and /)
     * <code>/**ndps** /</code>
     * in {@link get()}, this text will be erased.
     * 
     * @param mixed $bFlag true or false
     *
     * @access public
     * @return void
     * @see removeWhitespace()
     * @see PHP_Beautifier_Filter_NewLines
     */
    public function setNoDeletePreviousSpaceHack($bFlag = true) 
    {
        $this->bNdps = $bFlag;
    }
     
    /**
     * Returns the beautified code
     * 
     * @access public
     * @see setNoDeletePreviousSpaceHack()
     * @return string
     */
    public function get() 
    {
        if (!$this->bNdps) {
            return implode('', $this->aOut);
        } else {
            return str_replace('/**ndps**/', '', implode('', $this->aOut));
        }
    }

    /**
     * Returns the value of a settings
     * 
     * @param string $sKey Name of the setting
     *
     * @access public
     * @return mixed Value of the settings or false
     */
    public function getSetting($sKey) 
    {
        return (array_key_exists($sKey, $this->aSettings)) ? $this->aSettings[$sKey] : false;
    }

    /**
     * Get the token constant for the current control construct
     * 
     * @param int $iRet Current token -'x'
     *
     * @access public
     * @return mixed token constant or false
     */
    public function getControlSeq($iRet = 0) 
    {
        $iIndex = count($this->aControlSeq) -$iRet-1;
        return ($iIndex >= 0) ? $this->aControlSeq[$iIndex] : false;
    }

    /**
     * Get the token constant for the current Parenthesis
     * 
     * @param int $iRet Current token -'x'
     *
     * @access public
     * @return mixed token constant or false
     */
    public function getControlParenthesis($iRet = 0) 
    {
        $iIndex = count($this->aControlParenthesis) -$iRet-1;
        return ($iIndex >= 0) ? $this->aControlParenthesis[$iIndex] : false;
    }
    ////
    // Mode methods
    ////
    
    /**
     * Set a mode to true
     * 
     * @param string $sKey Name of the mode
     *
     * @access public
     * @return void
     */
    public function setMode($sKey) 
    {
        $this->aModes[$sKey] = true;
    }

    /**
     * Set a mode to false
     * 
     * @param string $sKey Name of the mode
     *
     * @access public
     * @return void
     */
    public function unsetMode($sKey) 
    {
        $this->aModes[$sKey] = false;
    }

    /**
     * Get the state of a mode
     * 
     * @param string $sKey name of the mode
     *
     * @access public
     * @return bool
     */
    public function getMode($sKey) 
    {
        return array_key_exists($sKey, $this->aModes) ? $this->aModes[$sKey] : false;
    }
    /////
    // Filter methods
    /////
    

    /**
     * Add a string to the output
     * 
     * @param mixed $token String to add
     *
     * @access public
     * @return void
     */
    public function add($token) 
    {
        $this->aOut[] = $token;
    }

    /**
     * Delete the last added output(s)
     * 
     * @param int $iReps Number of outputs to drop
     *
     * @access public
     * @return void
     * @deprecated
     */
    public function pop($iReps = 1) 
    {
        for ($x = 0 ; $x < $iReps ; $x++) {
            $sLast = array_pop($this->aOut);
        }
        return $sLast;
    }

    /**
     * Add Indent to the output
     * 
     * @access public
     * @return void
     * @see $sIndentChar
     * @see $iIndentNumber
     * @see $iIndent
     */
    public function addIndent() 
    {
        $this->aOut[] = str_repeat($this->sIndentChar, $this->iIndent);
    }

    /**
     * Set a string to put before a new line
     * You could use this to put a standard comment after some sentences
     * or to add extra newlines
     * 
     * @param mixed $sText String to put
     *
     * @access public
     * @return void
     */
    public function setBeforeNewLine($sText) 
    {
        $this->sBeforeNewLine = $sText;
    }

    /**
     * Add a new line to the output
     * 
     * @access public
     * @return void
     * @see $sNewLine
     */
    public function addNewLine() 
    {
        if (!is_null($this->sBeforeNewLine)) {
            $this->aOut[] = $this->sBeforeNewLine;
            $this->sBeforeNewLine = null;
        }
        $this->aOut[] = $this->sNewLine;
    }

    /**
     * Add a new line and a indent to output
     * 
     * @access public
     * @return void
     * @see $sIndentChar
     * @see $iIndentNumber
     * @see $iIndent
     * @see $sNewLine
     */
    public function addNewLineIndent() 
    {
        if (!is_null($this->sBeforeNewLine)) {
            $this->aOut[] = $this->sBeforeNewLine;
            $this->sBeforeNewLine = null;
        }
        $this->aOut[] = $this->sNewLine;
        $this->aOut[] = str_repeat($this->sIndentChar, $this->iIndent);
    }

    /**
     * Increments the indent in X chars.
     * If param omitted, used {@link iIndentNumber }
     * 
     * @param int $iIncr increment indent in x chars
     *
     * @access public
     * @return void
     */
    public function incIndent($iIncr = false) 
    {
        if (!$iIncr) {
            $iIncr = $this->iIndentNumber;
        }
        array_push($this->aIndentStack, $iIncr);
        $this->iIndent+= $iIncr;
    }

    /**
     * Decrements the indent.
     * 
     * @access public
     * @return void
     */
    public function decIndent() 
    {
        if (count($this->aIndentStack > 1)) {
            $iLastIndent = array_pop($this->aIndentStack);
            $this->iIndent-= $iLastIndent;
        }
    }
    //
    ////
    // Methods to lookup previous, next tokens
    ////
    //
    
    /**
     * Get the 'x' significant (non whitespace)previous token
     * 
     * @param int $iPrev current-x token
     *
     * @access private
     * @return mixed array or false
     */
    private function getPreviousToken($iPrev = 1) 
    {
        for ($x = $this->iCount-1 ; $x >= 0 ; $x--) {
            $aToken = &$this->getToken($x);
            if ($aToken[0] != T_WHITESPACE) {
                $iPrev--;
                if (!$iPrev) {
                    return $aToken;
                }
            }
        }
    }

    /**
     * Get the 'x' significant (non whitespace) next token
     * 
     * @param int $iNext current+x token
     *
     * @access private
     * @return array
     */
    private function getNextToken($iNext = 1) 
    {
        for ($x = $this->iCount+1 ; $x < (count($this->aTokens) -1) ; $x++) {
            $aToken = &$this->getToken($x);
            if ($aToken[0] != T_WHITESPACE) {
                $iNext--;
                if (!$iNext) {
                    return $aToken;
                }
            }
        }
    }

    /**
     * Return true if any of the constant defined is param 1 is the previous 'x' constant
     * 
     * @param mixed $mValue integer (constant) or array of constants
     * @param int   $iPrev  current-x token
     *
     * @access public
     * @return bool
     */
    public function isPreviousTokenConstant($mValue, $iPrev = 1) 
    {
        if (!is_array($mValue)) {
            $mValue = array(
                $mValue
            );
        }
        $iPrevious = $this->getPreviousTokenConstant($iPrev);
        return in_array($iPrevious, $mValue);
    }

    /**
     * Return true if any of the content defined is param 1 is the previous 'x' content
     * 
     * @param mixed $mValue string (content) or array of contents
     * @param int   $iPrev  current-x token
     *
     * @access public
     * @return bool
     */
    public function isPreviousTokenContent($mValue, $iPrev = 1) 
    {
        if (!is_array($mValue)) {
            $mValue = array(
                $mValue
            );
        }
        $iPrevious = $this->getPreviousTokenContent($iPrev);
        return in_array($iPrevious, $mValue);
    }

    /**
     * Return true if any of the constant defined in param 1 is the next 'x' content
     * 
     * @param mixed $mValue int (constant) or array of constants
     * @param int   $iPrev  current-x token
     *
     * @access public
     * @return bool
     */
    public function isNextTokenConstant($mValue, $iPrev = 1) 
    {
        if (!is_array($mValue)) {
            $mValue = array(
                $mValue
            );
        }
        $iNext = $this->getNextTokenConstant($iPrev);
        return in_array($iNext, $mValue);
    }

    /**
     * Return true if any of the content defined is param 1 is the next 'x' content
     * 
     * @param mixed $mValue string(content) or array of contents
     * @param int   $iPrev  current-x token
     *
     * @access public
     * @return bool
     */
    public function isNextTokenContent($mValue, $iPrev = 1) 
    {
        if (!is_array($mValue)) {
            $mValue = array(
                $mValue
            );
        }
        $iNext = $this->getNextTokenContent($iPrev);
        return in_array($iNext, $mValue);
    }
     
    /**
     * Get the 'x' significant (non whitespace) previous token constant
     * 
     * @param int $iPrev current-x token
     *
     * @access public
     * @return int
     */
    public function getPreviousTokenConstant($iPrev = 1) 
    {
        $sToken = $this->getPreviousToken($iPrev);
        return $sToken[0];
    }

    /**
     * Get the 'x' significant (non whitespace) previous token text
     * 
     * @param int $iPrev current-x token
     *
     * @access public
     * @return void
     */
    public function getPreviousTokenContent($iPrev = 1) 
    {
        $mToken = $this->getPreviousToken($iPrev);
        return (is_string($mToken)) ? $mToken : $mToken[1];
    }
    /**
     * Get the 'x' significant (non comment) previous token text
     * 
     * @param int $iPrev current-x token
     *
     * @access public
     * @return void
     */
    public function getNextTokenNonCommentConstant($iPrev = 1) 
    {
        do {
            $aToken = $this->getNextToken($iPrev);
            $iPrev++;
        } while ($aToken[0] == T_COMMENT);
        return $aToken[0];
    }

    /**
     * Get the 'x' significant (non whitespace) next token constant
     * 
     * @param int $iPrev current-x token
     *
     * @access public
     * @return void
     */
    public function getNextTokenConstant($iPrev = 1) 
    {
        $sToken = $this->getNextToken($iPrev);
        return $sToken[0];
    }

    /**
     * Get the 'x' significant (non whitespace) next token text
     * 
     * @param int $iNext Current+x token
     *
     * @access public
     * @return void
     */
    public function getNextTokenContent($iNext = 1) 
    {
        $mToken = $this->getNextToken($iNext);
        return (is_string($mToken)) ? $mToken : $mToken[1];
    }

    /**
     * Return the whitespace previous to current token
     * Ex.: You have
     * '    if($a)'
     * if you call this funcion on 'if', you get '    '
     * 
     * @access public
     * @todo implements a more economic way to handle this.
     * @return string previous whitespace
     */
    public function getPreviousWhitespace() 
    {
        $sWhiteSpace = '';
        $aMatch=array();

        for ($x = $this->iCount-1 ; $x >= 0 ; $x--) {
            $this->oLog->log("sp n:$x", PEAR_LOG_DEBUG);
            $aToken = $this->getToken($x);
            if (is_array($aToken)) {
                if ($aToken[0] == T_WHITESPACE) {
                    $sWhiteSpace.= $aToken[1];
                } elseif (preg_match("/([\s\r\n]+)$/", $aToken[1], $aMatch)) {
                    $sWhiteSpace.= $aMatch[0];
                    // ArrayNested->off();
                    $this->oLog->log("+space-token-with-sp:[" . PHP_Beautifier_Common::wsToString($sWhiteSpace) . "]", PEAR_LOG_DEBUG);
                    // ArrayNested->on();
                    return $sWhiteSpace;
                }
            } else {
                $this->oLog->log("+space-token-without-sp:[" . PHP_Beautifier_Common::wsToString($sWhiteSpace) . "]", PEAR_LOG_DEBUG);
                return $sWhiteSpace;
            }
        }
        // Strange, but...
        $this->oLog->log("+space:[" . PHP_Beautifier_Common::wsToString($sWhiteSpace) . "]", PEAR_LOG_DEBUG);
        return $sWhiteSpace;
    }

    /**
     * Remove all whitespace from the previous tag
     * 
     * @access public
     * @return bool  false if previous token was short comment or heredoc
     *               (don't remove ws)
     *               true anything else.
     */
    public function removeWhitespace() 
    {
        // if the previous token was
        // - a short comment
        // - heredoc
        // don't remove whitespace!
        //
        if ($this->isPreviousTokenConstant(T_COMMENT) and preg_match("/^(\/\/|#)/", $this->getPreviousTokenContent())) { // Here for short comment
            return false;
        } elseif ($this->isPreviousTokenConstant(T_END_HEREDOC) && $this->getToken($this->iCount) != ';') { 
            return false;
        } elseif ($this->getPreviousTokenConstant(2) == T_END_HEREDOC) { // And here for heredoc
            return false;
        }
        $pop = 0;
        for ($i = count($this->aOut) -1 ; $i >= 0 ; $i--) { // go backwards
            $cNow = &$this->aOut[$i];
            if (strlen(trim($cNow)) == 0) { // only space
                array_pop($this->aOut); // delete it!
                $pop++;
            } else { // we find something!
                $cNow = rtrim($cNow); // rtrim out
                break;
            }
        }
        $this->oLog->log("-space $pop", PEAR_LOG_DEBUG);
        return true;
    }
     
    /**
     * Get a token by number
     * 
     * @param mixed $iIndex Number of the token
     *
     * @access public
     * @return array
     */
    public function &getToken($iIndex) 
    {
        if ($iIndex < 0 or $iIndex > count($this->aTokens)) {
            return false;
        } else {
            return $this->aTokens[$iIndex];
        }
    }
    /**
     * openBraceDontProcess 
     * 
     * @access public
     * @return void
     */
    public function openBraceDontProcess()
    {
        return $this->isPreviousTokenConstant(T_VARIABLE)
            or $this->isPreviousTokenConstant(T_OBJECT_OPERATOR) 
            or ($this->isPreviousTokenConstant(T_STRING) and $this->getPreviousTokenConstant(2) == T_OBJECT_OPERATOR) 
            or $this->getMode('double_quote');
    }
}
?>
