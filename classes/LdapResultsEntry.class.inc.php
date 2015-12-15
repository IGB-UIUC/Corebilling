<?php

/**
 * Class LdapResultEntry
 *
 * PHP4 wrapper around an ldap entry resource
 *
 */
class LdapResultEntry
{

    /**
     * The last error code that was returned by the LDAP server
     *
     * @access public
     * @var integer
     */
    var $ldapErrno;

    /**
     * The last error string that was returned by the LDAP server
     *
     * @access public
     * @var integer
     */
    var $ldapError;

    /**
     * The LDAP connection resource
     *
     * @access private
     * @var resource
     */
    var $connection;

    /**
     * The LDAP entry resource
     *
     * @access private
     * @var resource
     */
    var $entry;

    /**
     * The &ber_identifier used in get*Attribute functions
     *
     * @access private
     * @var integer
     */
    var $berid;

    /**Constructor
     * @param $connection
     * @param $entry
     */
    function __construct($connection, $entry)
    {
        $this->connection = $connection;
        $this->entry = $entry;
    }

    function __destruct()
    {

    }

    /**
     * Loads the next ldap entry
     *
     * If there are no more entries (or an error) it returns false.
     *
     * @link http://www.php.net/ldap_next_entry
     * @return boolean Success
     */
    function nextEntry()
    {
        if ($this->entry = @ldap_next_entry($this->connection, $this->entry)) {
            return true;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Used to simplify reading the attributes and values from an entry in the search result.
     * The return value is a multi-dimensional array of attributes and values
     *
     * Returns a complete entry information in a multi-dimensional array on success
     * and FALSE on error.
     *
     * @link http://www.php.net/ldap_get_attributes
     * @return mixed
     */
    function getAttributes()
    {
        if ($attr = @ldap_get_attributes($this->connection, $this->entry)) {
            return $attr;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Used to find out the DN of an entry in the result
     *
     * Returns the DN of the result or FALSE on error
     *
     * @link http://www.php.net/ldap_get_dn
     * @return string
     */
    function getDN()
    {
        if ($dn = @ldap_get_dn($this->connection, $this->entry)) {
            return $dn;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Get all the binary values from the entry
     *
     * Used to read all the values of the attribute in the entry
     *
     * I renamed the get_values_len to getValuesBin as it seemed more logical
     *
     * Returns an array of values for the attribute on success and FALSE  on error.
     *
     * @link http://www.php.net/ldap_get_values_len
     * @param string $attr The attribute you want to read
     * @return array
     */
    function getValuesBin($attr)
    {
        if ($arr = @ldap_get_values_len($this->connection, $this->entry, $attr)) {
            return $arr;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Get all values from the entry
     *
     * Used to read all the values of the attribute in the entry
     *
     * Returns an array of values for the attribute on success and FALSE  on error.
     *
     * @link http://www.php.net/ldap_get_values
     * @param string $attr The attribute you want to read
     * @return array
     */
    function getValues($attr)
    {
        if ($arr = @ldap_get_values($this->connection, $this->entry, $attr)) {
            return $arr;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Return the name of the first attribute
     *
     * Returns the name of the first attribute in the entry on success or failure on error
     *
     * @link http://www.php.net/ldap_first_attribute
     * @return string
     */
    function getFirstAttribute()
    {
        unset($this->berid); // Make sure we start over, might not be needed
        if ($string = @ldap_first_attribute($this->connection, $this->entry, $this->berid)) {
            return $string;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Return the name of the next attribute
     *
     * Returns the next attribute in the entry on success or FALSE on error
     *
     * @link http://www.php.net/ldap_next_attribute
     * @return unknown
     */
    function getNextAttribute()
    {
        if (!isset($this->berid) || empty($this->berid)) {
            $this->ldapErrno = -1;
            $this->ldapError = "You must call getFirstAttribute before you can getNextAttribute";
            return false;
        }
        if ($string = @ldap_next_attribute($this->connection, $this->entry, $this->berid)) {
            return $string;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Helper function: Set the error variables
     *
     * I'm so slack...
     */
    function setErrVars()
    {
        $this->ldapErrno = ldap_errno($this->connection);
        $this->ldapError = ldap_error($this->connection);
    }
}

?>