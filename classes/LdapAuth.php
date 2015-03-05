<?php

/**
 * Class LdapAuth
 * Used to authenticate with LDAP using the Ldap.php class
 */
class LdapAuth
{
	private $host="";
	private $peopleDN="";
	private  $groupDN="";
	private $port="";
	private $error="No Error Detected";
    private $ldap;
	
	public function __construct($host,$peopleDN,$groupDN,$port)
	{
		$this->host = $host;
		$this->peopleDN = $peopleDN;
		$this->groupDN = $groupDN;
        $this->port = $port;


	}
	
	public function __destruct()
	{
        if($this->ldap)
        {
            $this->ldap->close();
        }

	}

    /** Authenticate with LDAP given username and password
     *
     * @param $username
     * @param $password
     * @return bool
     */
    public function Authenticate($username,$password)
	{
        //Format username login for ldap search
        $userNameString = "CN=".$username.",".$this->peopleDN;

        $this->ldap = new Ldap($this->host." ".$this->port);

        //Set protocols for AD connection
        $this->ldap->setOption(LDAP_OPT_PROTOCOL_VERSION,3);
        $this->ldap->setOption(LDAP_OPT_REFERRALS,0);

        //attempt to connect to ldap server
        if($this->ldap->connect())
        {
            //Bind using the username and password given
            if($this->ldap->bind($userNameString,$password))
            {
                //Search filter for the given group to check membership for
                $searchGroupFilter = "(memberOf=".$this->groupDN.")";

                //Search filter for the member attribute of the group
                $searchMembersFilter = array("member");

                //Run the search on ldap
                $groupSearchResults = $this->ldap->searchSubtree($userNameString,$searchGroupFilter,$searchMembersFilter);
                $entries = $groupSearchResults->getEntries();
                if($groupSearchResults->countEntries() !='')
                {
                    //Return true if a result was returned when the group was searched for the username
                    if($groupSearchResults->getEntries())
                    {
                        return true;
                    }
                }
            }
        }

        //If any error has occurred then set to the error variable
        $this->error = $this->ldap->ldapError;

        return false;
	}

    //Getters and setters
    /**
     * @return string
     */
    public function getError()
    {
        return $this->error;
    }





}


?>
