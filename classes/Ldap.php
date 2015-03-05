<?php
/**
 * class.ldap.php4
 * Provides an object orientated LDAP wrapper
 * 
 * @author Shannon Wynter {@link http://fremnet.net/contact}
 * @version 0.2
 * @copyright Copyright &copy; 2006, Shannon Wynter
 * @link http://fremnet.net
 * 
 * This is simply an object orientated wrapper for the PHP LDAP functions.
 * 
 * I've thrown in the children function
 * 
 * This program is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 * 
 * ChangeLog
 * -----------
 * version 0.2, 2006-11-17, Shannon Wynter {@link http://fremnet.net/contact}
 *  - Fixed modReplace function, was calling ldap_mod_del
 *
 * version 0.1, 2006-06-22, Shannon Wynter {@link http://fremnet.net/contact}
 *  - Initial release
 * 
 * Notes
 * -----------
 * I've not included the reference related functions as they're not documented.
 */

/**
 * Class ldap.
 * 
 * PHP4 class to wrap around the main LDAP functions
 * 
 * Makes use of class LdapResults which in turn makes use of class LdapResultEntry
 *
 */
class Ldap {

	/**
	 * Array of server IP address or hostname (add ports with <space>port)
	 * EG: array('localhost 10138')
	 * 
	 * I know it's strange to use <space> as a port separator, but we don't
	 * want to be splitting up a ldap:// url
	 * 
	 * @access public
	 * @var array
	 */
	var $server;

	/**
	 * The version of LDAP we'll be using
	 * 
	 * Should be 2 or 3
	 * 
	 * @access public
	 * @var integer
	 */
	var $version;

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
	 * Constructor - Creates a new instance of the ldap class
	 *
	 * @param mixed $ldapServer A 'server[ port]' or an array('server[ port]');
	 * @param integer $ldapVersion The version of LDAP we'll be using

	 * @return ldap
	 */
	function __construct($ldapServer, $ldapVersion=3) {
		if (is_array($ldapServer)) {
			$this->server = $ldapServer;
		} else {
			$this->server = array($ldapServer);
		}
		$this->version = $ldapVersion;
	}

	/**
	 * Creates a connection to the LDAP server which will be used for all future access
	 * 
	 * Will loop through all the servers in $this->server until it finds one it can connect to
	 * 
	 * @link http://www.php.net/ldap_connect
	 * @return boolean Success
	 */
	function connect() {
		foreach ($this->server as $server) {
			list($host,$port) = explode(' ',$server);
			if (empty($port)) {
				$port = null;
			}
			$this->connection = @ldap_connect($host,$port);
			if ($this->connection) {
				$this->setOption(LDAP_OPT_PROTOCOL_VERSION, $this->version);
				return true;
			}
		}
		return false;
	}

	/**
	 * Starts TLS over our connection if we're using version 3 of the LDAP protocol
	 * 
	 * Note: TLS and SSL are mutually exclusive
	 * 
	 * @link http://www.php.net/ldap_start_tls
	 * @return false
	 */
	function startTLS() {
		if ($this->version != 3) {
			$this->ldapError = 'Not using LDAP Protocol version 3, TLS is not supported.';
			$this->ldapErrno = -1;
			return false;
		}
		if (@ldap_start_tls($this->connection)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Closes the active connection to the LDAP server
	 *
	 * @link http://www.php.net/ldap_close
	 * @return boolean Success
	 */
	function close() {
		if (@ldap_close($this->connection)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Binds to the LDAP directory with specified RDN and password.
	 * 
	 * $shortName and $password are option, if not specified an anonymous bind is attempted.
	 *
	 * Note: I have added a check to make sure the password is passed when shortName is.
	 *
	 * @link http://www.php.net/ldap_bind
	 * @param string[optional] $shortName The DN to authenticate with
	 * @param string[optional] $password The password to authenticate with
	 * @return boolean Success
	 */
	function bind($dn=null, $password=null) {
		if (!is_null($dn) && (is_null($password) || empty($password))) {
			$this->ldapErrno=-1;
			$this->ldapError="Please specify a password when binding as a user";
			return false;
		}

		if (@ldap_bind($this->connection,$dn,$password)) {

			return true;
		}
		$this->setErrVars();
		return false;
	}
	
	/**
	 * Sets the value of the specified option to be $value. Returns TRUE on 
	 * success or FALSE on failure
	 *
	 * For information about the options and values, please see the link
	 * @link http://www.php.net/ldap_set_option
	 * @param integer $option The option you intend to set
	 * @param mixed $value The value to set
	 * @return boolean Success
	 */
	function setOption($option, $value) {
		if (@ldap_set_option($this->connection,$option,$value)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}
	
	/**
	 * Gets the value of the specified option and returns it or FALSE on failure
	 *
	 * For information about the options and values, please see the link
	 * @link http://www.php.net/ldap_get_option
	 * @param integer $option
	 * @return boolean Success
	 */
	function getOption($option) {
		$val = null;
		if (@ldap_get_option($this->connection,$option,$val)) {
			return $val;
		}
		$this->setErrVars();
		return false;
	}
	
	/**
	 * Performs the search for a specified filter on the directory with the 
	 * scope of LDAP_SCOPE_SUBTREE. This is equivalent to searching the entire
	 * directory. $base_dn specifies the base DN for the directory.
	 *
	 * Only $base_dn and $filter are required
	 * 
	 * @link http://www.php.net/ldap_search
	 * @param string $base_dn
	 * @param string $filter
	 * @param array[optional] $attrs
	 * @param int[optional] $attrsonly
	 * @param int[optional] $sizelimit
	 * @param int[optional] $timelimit
	 * @param int[optional] $deref
	 * @return LdapResults
	 */
	function searchSubtree($base_dn, $filter, $attrs=null, $attrsonly=null, $sizelimit=null, $timelimit=null, $deref=null) {
		if ($result = @ldap_search($this->connection,$base_dn,$filter,$attrs,$attrsonly,$sizelimit,$timelimit,$deref)) {
			return new LdapResults($this,$result);
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Performs the search for a specified filter on the directory with the 
	 * scope of LDAP_SCOPE_ONELEVEL. 
	 * 
	 * LDAP_SCOPE_ONELEVEL means that the search should only return information that
	 * is at the level immediately below the $base_dn given in the call.
	 * (Equivalent to typing "ls" and getting a list of files and folders in the
	 * current working directory.)
	 *
	 * Only $base_dn and $filter are required
	 * 
	 * @link http://www.php.net/ldap_list
	 * @param string $base_dn
	 * @param string $filter
	 * @param array[optional] $attrs
	 * @param int[optional] $attrsonly
	 * @param int[optional] $sizelimit
	 * @param int[optional] $timelimit
	 * @param int[optional] $deref
	 * @return LdapResults
	 */
	function searchOneLevel($base_dn, $filter, $attrs=null, $attrsonly=null, $sizelimit=null, $timelimit=null, $deref=null) {
		if ($result = @ldap_read($this->connection,$base_dn,$filter,$attrs,$attrsonly,$sizelimit,$timelimit,$deref)) {
			return new LdapResults($this,$result);
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Performs the search for a specified filter on the directory with the 
	 * scope of LDAP_SCOPE_BASE. So it is equivalent to reading an entry from the directory.
	 * 
	 * Only $base_dn and $filter are required
	 * 
	 * @link http://www.php.net/ldap_read
	 * @param string $base_dn
	 * @param string $filter
	 * @param array[optional] $attrs
	 * @param int[optional] $attrsonly
	 * @param int[optional] $sizelimit
	 * @param int[optional] $timelimit
	 * @param int[optional] $deref
	 * @return LdapResults
	 */
	function searchBase($base_dn, $filter, $attrs=null, $attrsonly=null, $sizelimit=null, $timelimit=null, $deref=null) {
		if ($result = @ldap_list($this->connection,$base_dn,$filter,$attrs,$attrsonly,$sizelimit,$timelimit,$deref)) {
			return new LdapResults($this,$result);
		}
		$this->setErrVars();
		return false;
	}
	
	/**
	 * Add attribute values to current attributes
	 * 
	 * This function adds attribute(s) to the specified $shortName. It performs the modification at
	 * the attribute level as opposed to the object level.
	 * 
	 * @link http://www.php.net/ldap_mod_add
	 * @param string $dn The DN you want to update
	 * @param array $entry The data you want to add
	 * @return boolean Success
	 */
	function modAdd($dn,$entry) {
		if (@ldap_mod_add($this->connection,$dn,$entry)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Delete attribute values from the current attributes
	 *
	 * This function removes attribute(s) from the specified $shortName. It performs the modification
	 * at the attribute level as opposed to the object level.
	 * 
	 * @link http://www.php.net/ldap_mod_del
	 * @param string $dn The DN you want to update
	 * @param array $entry The data you want to delete
	 * @return boolean Success
	 */
	function modDel($dn,$entry) {
		if (@ldap_mod_del($this->connection,$dn,$entry)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}	

	/**
	 * Replace attribute values with new ones
	 * 
	 * This function replaces attribute(s) from the specified $shortName. It performs the modification
	 * at the attribute level as opposed to the object level.
	 *
	 * @link http://www.php.net/ldap_mod_replace
	 * @param string $dn the DN you want to update
	 * @param array $entry the data you want to replace
	 * @return boolean Success
	 */
	function modReplace($dn,$entry) {
		if (@ldap_mod_replace($this->connection,$dn,$entry)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}	

	/**
	 * Modify an LDAP entry
	 *
	 * Used to modify entries in the LDAP directory. The DN of the entry added is specified by $shortName.
	 * Array $entry specifies the information about the entry. The values in the entries are
	 * indexed by individual attributes. In case of multiple values for an attribute, they are
	 * indexed using integers starting with 0
	 * 
	 * @link http://www.php.net/ldap_modify
	 * @param string $dn The DN we're modifying
	 * @param array $entry
	 * @return boolean Success
	 */
	function modify($dn,$entry) {
		if (@ldap_modify($this->connection,$dn,$entry)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}	

	/**
	 * Add entries to the LDAP directory
	 *
	 * Used to add entries in the LDAP directory. The DN of the entry added is specified by $shortName.
	 * Array $entry specifies the information about the entry. The values in the entries are
	 * indexed by individual attributes. In case of multiple values for an attribute, they are
	 * indexed using integers starting with 0
	 * 
	 * @link http://www.php.net/ldap_add
	 * @param string $dn The DN we're adding
	 * @param array $entry
	 * @return boolean Success
	 */
	function add($dn,$entry) {
		if (@ldap_add($this->connection,$dn,$entry)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Delete an entry from the LDAP directory
	 *
	 * @link http://www.php.net/ldap_delete
	 * @param string $dn The entry we're deleting
	 * @return boolean Success
	 */
	function delete($dn) {
		if (@ldap_delete($this->connection,$dn)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}
	
	/**
	 * Modify the name of an entry
	 *
	 * The entry specified by $shortName is renamed/moved. The new RDN is specified by $newrdn and the
	 * parent/superior entry is specified by $newparent. If the parameter $deleteoldrdn is TRUE
	 * the old RDN value(s) is removed, else the old RDN value(s) is retained as non-distinguished
	 * values of the entry.
	 * 
	 * @link http://www.php.net/ldap_rename
	 * @param string $dn The entry to be renamed/moved
	 * @param string $newrdn The new RDN
	 * @param string $newparent The DN of the new parent
	 * @param boolean $deleteoldrdn Do we delete the old RDN?
	 * @return boolean Success
	 */
	function rename($dn, $newrdn, $newparent, $deleteoldrdn) {
		if ($this->version != 3) {
			$this->ldapErrno = -1;
			$this->ldapError = "ldap_rename requires version 3 of the LDAP protocol";
			return false;
		}
		if (@ldap_rename($this->connection, $dn, $newrdn, $newparent, $deleteoldrdn)) {
			return true;
		}
		$this->setErrVars();
		return false;
	}

	/**
	 * Compare the value of attribute found in entry specified with $shortName
	 * 
	 * Used to compare the value of attr to the value of same attribute in the LDAP directory
	 * entry specified with $shortName.
	 * 
	 * Returns TRUE if value matches, otherwise returns FALSE. Returns -1 on error.
	 * 
	 * @link http://www.php.net/ldap_compare
	 * @param string $dn The DN which we are comparing
	 * @param string $attr The attribute to check
	 * @param string $value The value to check for
	 * @return mixed
	 */
	function compare($dn, $attr, $value) {
		$result = @ldap_compare($this->connection, $dn, $attr, $value);
		if ($result === -1) {
			$this->setErrVars();
		}
		return $result;
	}
	
	/**
	 * Get an array full of immediate children for the node specified by $shortName
	 *
	 * Returns array if successful, otherwise returns FALSE
	 * 
	 * @param string $dn The base shortName that we want to look for children under
	 * @return array
	 */
	function children($dn) {
		$returning = false;
		if ($result = $this->searchBase($dn,"objectClass=*",array("shortName"))) {
			if ($entry = $result->firstEntry()) {
				$returning = array($entry->getDN());
				while ($entry->nextEntry()) {
					$returning[] = $entry->getDN();
				}
			}
		}
		return $returning;
	}
	
	/**
	 * Helper function: Set the error variables
	 * 
	 * I'm so slack...
	 */
	function setErrVars() {
		$this->ldapErrno = ldap_errno($this->connection);
		$this->ldapError = ldap_error($this->connection);
	}
}


