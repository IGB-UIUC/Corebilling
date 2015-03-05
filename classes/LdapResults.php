<?php

/**
 * class LdapResults
 *
 * PHP4 class to act as an object wrapper around a ldap result resource
 *
 * Makes use of class LdapResultEntry
 *
 */
class LdapResults
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
     * The parent LDAP object
     *
     * @access private
     * @var ldap
     */
    var $ldap;

    /**
     * The LDAP result resource
     *
     * @access private
     * @var resource
     */
    var $result;

    /**
     * Constructor - Creates a new instance of the LdapResults class
     *
     * @param ldap $ldap The parent ldap object
     * @param resource $result An active result resource
     * @return LdapResults
     */
    function __construct($ldap, $result)
    {
        $this->ldap = $ldap;
        $this->result = $result;
    }

    function __destruct()
    {

    }

    /**
     * Returns the LdapResultEntry for the first entry on success and FALSE on error.
     *
     * Entries in the LDAP result are read sequentially using the ldap_first_entry()
     * and ldap_next_entry() functions.
     *
     * $entry = $obj->firstEntry() returns an ldapentry for first entry in the result.
     * You then call $entry->nextEntry()
     *
     * @link http://www.php.net/ldap_first_entry
     * @return LdapResultEntry
     */
    function firstEntry()
    {
        if ($entry = @ldap_first_entry($this->ldap->connection, $this->result)) {
            return new LdapResultEntry($this->ldap->connection, $entry);
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Returns a complete result information in a multi-dimensional array
     * on success and FALSE on error.
     *
     * @link http://www.php.net/ldap_get_entries
     * @return array
     */
    function getEntries()
    {
        if ($array = @ldap_get_entries($this->ldap->connection, $this->result)) {
            return $array;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Returns the number of entries or FALSE on error
     *
     * @link http://www.php.net/ldap_count_entries
     * @return integer
     */
    function countEntries()
    {
        if ($count = @ldap_count_entries($this->ldap->connection, $this->result)) {
            return $count;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Sort LDAP results
     *
     * @link http://www.php.net/ldap_sort
     * @param unknown_type $sortFilter
     * @return unknown
     */
    function sortEntries($sortFilter)
    {
        if (@ldap_sort($this->ldap->connection, $this->result, $sortFilter)) {
            return true;
        }
        $this->setErrVars();
        return false;
    }

    /**
     * Frees up the memory allocated internally to store the result
     *
     * @link http://www.php.net/ldap_free_result
     * @return boolean success
     */
    function free()
    {
        if (@ldap_free_result($this->result)) {
            return true;
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
        $this->ldapErrno = ldap_errno($this->ldap->connection);
        $this->ldapError = ldap_error($this->ldap->connection);
    }
}

?>