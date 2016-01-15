<?php
/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */
/**
 * New Lines: Add extra new lines after o before specific contents
 *
 * PHP version 5
 *
 * LICENSE: This source file is subject to version 3.0 of the PHP license
 * that is available through the world-wide-web at the following URI:
 * http://www.php.net/license/3_0.txt.  If you did not receive a copy of
 * the PHP License and are unable to obtain it through the web, please
 * send a note to license@php.net so we can mail you a copy immediately.
 *
 * @category   PHP
 * @package    PHP_Beautifier
 * @subpackage Filter
 * @author     Claudio Bustos <cdx@users.sourceforge.com>
 * @copyright  2004-2010 Claudio Bustos
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    CVS: $Id:$
 * @link       http://pear.php.net/package/PHP_Beautifier
 * @link       http://php.apsique.com/PHP_Beautifier
 * @since      File available since Release 0.1.9
 */
/**
 * Lowercase: lowercase all control structures.
 * You should filter the code with this filter, and later parse
 * again the file with the others filters
 * Command line example:
 *
 * <code>php_beautifier --filters "Lowercase()"</code>
 *
 * @category   PHP
 * @package    PHP_Beautifier
 * @subpackage Filter
 * @author     Claudio Bustos <cdx@users.sourceforge.com>
 * @copyright  2004-2010 Claudio Bustos
 * @license    http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version    Release: @package_version@
 * @link       http://pear.php.net/package/PHP_Beautifier
 * @link       http://beautifyphp.sourceforge.net
 * @since      Class available since Release 0.1.9
 */
class PHP_Beautifier_Filter_Lowercase extends PHP_Beautifier_Filter
{
    protected $sDescription = 'Lowercase all control structures. Parse the output with another Filters';
    private $_aControlSeq = array(
        T_IF,
        T_ELSE,
        T_ELSEIF,
        T_WHILE,
        T_DO,
        T_FOR,
        T_FOREACH,
        T_SWITCH,
        T_DECLARE,
        T_CASE,
        T_DEFAULT,
        T_TRY,
        T_CATCH,
        T_ENDWHILE,
        T_ENDFOREACH,
        T_ENDFOR,
        T_ENDDECLARE,
        T_ENDSWITCH,
        T_ENDIF,
        T_INCLUDE,
        T_INCLUDE_ONCE,
        T_REQUIRE,
        T_REQUIRE_ONCE,
        T_FUNCTION,
        T_PRINT,
        T_RETURN,
        T_ECHO,
        T_NEW,
        T_CLASS,
        T_VAR,
        T_GLOBAL,
        T_THROW,
        /* CONTROL */
        T_IF,
        T_DO,
        T_WHILE,
        T_SWITCH,
        T_CASE,
        /* ELSE */
        T_ELSEIF,
        T_ELSE,
        T_BREAK,
        /* ACCESS PHP 5 */
        T_INTERFACE,
        T_FINAL,
        T_ABSTRACT,
        T_PRIVATE,
        T_PUBLIC,
        T_PROTECTED,
        T_CONST,
        T_STATIC,
        /* LOGICAL */
        T_LOGICAL_OR,
        T_LOGICAL_XOR,
        T_LOGICAL_AND,
        T_BOOLEAN_OR,
        T_BOOLEAN_AND,
    );
    private $_oLog;

    /**
     * __construct 
     * 
     * @param PHP_Beautifier $oBeaut    PHP_Beautifier Object
     * @param array          $aSettings Settings for the PHP_Beautifier
     *
     * @access public
     * @return void
     */
    public function __construct(PHP_Beautifier $oBeaut, $aSettings = array()) 
    {
        parent::__construct($oBeaut, $aSettings);
        $this->_oLog = PHP_Beautifier_Common::getLog();
    }
    
    /**
     * t_string 
     * 
     * @param mixed $sTag The tag to be procesed
     *
     * @access public
     * @return void
     */
    public function t_string($sTag)
    {
        if ($sTag=='TRUE' or $sTag=='FALSE') {
            $this->oBeaut->aCurrentToken[1]=strtolower($sTag);
        }
        return PHP_Beautifier_Filter::BYPASS;
        
    }

    /**
     * __call 
     * 
     * @param mixed $sMethod Method name
     * @param mixed $aArgs   Method arguments
     *
     * @access public
     * @return void
     */
    public function __call($sMethod, $aArgs) 
    {
        $iToken = $this->aToken[0];
        $sContent = $this->aToken[1];
        if (in_array($iToken, $this->_aControlSeq)) {
            $this->_oLog->log("Lowercase:" . $sContent, PEAR_LOG_DEBUG);
            $this->oBeaut->aCurrentToken[1]=strtolower($sContent);
            
        }
        return PHP_Beautifier_Filter::BYPASS;
    }
}
?>
