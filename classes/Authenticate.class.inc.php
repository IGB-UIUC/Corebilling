<?php

/**
 * Class Authenticate
 *
 * Used to authenticate a user using ldap and set session variables and authentication keys.
 */
class Authenticate {
    private $db;
    
    private $ldapAuth;
    private $authenticatedUser;
    private $logonError;
    private $verified;
    
    private $user_id = null;
    private $key = null;
    public $lastActivity = null;
    public $sessMethod = null;

    public function __construct(PDO $db, LdapAuth $ldapAuth)
    {
        $this->db = $db;
        $this->ldapAuth = $ldapAuth;
        $this->verified = false;
        $this->authenticatedUser = new User($this->db);
    }

    public function __destruct()
    {

    }

    /** Log user in using their username and password
     * @param $userName
     * @param $password
     * @return bool
     */
    public function Login($userName, $password)
    {
        $this->logonError = "";

        //Check if user has access by checking LDAP
        if ($this->ldapAuth->Authenticate ( $userName, $password) ) {
            $userId = User::exists($this->db,$userName);
            if ($userId)
            {
                //If user is in the system then load this user
                $this->authenticatedUser->load($userId);
            } else {
	            // If user is not in the system, deny them.
	            $this->verified=false;
	            $this->logonError = "Unauthorized user.";
	            return false;
            }

            //Generate a secure key for user
            $this->authenticatedUser->updateSecureKey();
            $this->SetSession($this->authenticatedUser->getSecureKey(), $this->authenticatedUser->getId() );
            $this->verified = true;

            return true;

        } else {
            $this->logonError =$this->ldapAuth->getError();
            //$this->logonError = $this->logonError. "Incorrect user name or password.";
        }

        $this->verified=false;
        return false;
    }

    /**
     * Logout user by removing their session information and marking them as unverified
     */
    public function Logout()
    {
        $this->UnsetSEssion();
        $this->verified = false;
    }

    /** Verify the user via their session so we don't have to check LDAP every time
     *  if the session has expired then force logout the user by removing their session information
     * @return bool
     */
    public function VerifySession()
    {
	    $this->load();
        if($this->user_id != null) {
            if(time() - $this->lastActivity < LOGIN_TIMEOUT) {
                $this->authenticatedUser = new User ( $this->db );
                $this->authenticatedUser->load($this->user_id);

                if($this->authenticatedUser->getSecureKey() == $this->key)
                {
                    $this->authenticatedUser->updateSecureKey();
                    $this->SetSession($this->authenticatedUser->getSecureKey(), $this->authenticatedUser->getId());
                }
                $this->verified = true;
                return true;
            }
        }
        $this->UnsetSession();
        $this->verified=false;
        return false;
    }

    /**Sets the session informtion
     * @param $secureKey
     * @param $userId
     */
    public function SetSession($secureKey,$userId)
    {
        $_SESSION ['coreapp_user_id'] = $userId;
        $_SESSION ['coreapp_key'] = $secureKey;
        $_SESSION ['coreapp_created'] = time();
        
		setcookie('coreapp_user_id', $userId, time()+LOGIN_TIMEOUT);
		setcookie('coreapp_key', $secureKey, time()+LOGIN_TIMEOUT);
		setcookie('coreapp_created', time(), time()+LOGIN_TIMEOUT);
    }
    
    public function load(){
	    if(isset($_SESSION['coreapp_user_id'])){
		    $this->user_id = $_SESSION['coreapp_user_id'];
		    $this->key = $_SESSION['coreapp_key'];
		    $this->lastActivity = $_SESSION['coreapp_created'];
		    
		    $this->sessMethod = 'session';
	    } else if(isset($_COOKIE['coreapp_user_id'])) {
		    $this->user_id = $_COOKIE['coreapp_user_id'];
		    $this->key = $_COOKIE['coreapp_key'];
		    $this->lastActivity = $_COOKIE['coreapp_created'];
		    
		    $this->sessMethod = 'cookie';
	    }
    }

    /**
     * Removes session information when the user logs out or expired login
     */
    public function UnsetSession()
    {
        unset ( $_SESSION ['coreapp_user_id'] );
        unset ( $_SESSION ['coreapp_key'] );
        unset ( $_SESSION ['coreapp_created'] );
        
        setcookie('coreapp_user_id', "", time()-3600);
        setcookie('coreapp_key', "", time()-3600);
        setcookie('coreapp_created', "", time()-3600);
    }

    /**
     * Returns an encrypted & utf8-encoded
     */
    private function encrypt($pure_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $encrypted_string = mcrypt_encrypt(MCRYPT_BLOWFISH, $encryption_key, utf8_encode($pure_string), MCRYPT_MODE_ECB, $iv);
        return $encrypted_string;
    }

    /**
     * Returns decrypted original string
     */
    private function decrypt($encrypted_string, $encryption_key) {
        $iv_size = mcrypt_get_iv_size(MCRYPT_BLOWFISH, MCRYPT_MODE_ECB);
        $iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
        $decrypted_string = mcrypt_decrypt(MCRYPT_BLOWFISH, $encryption_key, $encrypted_string, MCRYPT_MODE_ECB, $iv);
        return $decrypted_string;
    }

    /**
     * @return mixed
     */
    public function getAuthenticatedUser()
    {
        return $this->authenticatedUser;
    }

    /**
     * @return mixed
     */
    public function getLogonError()
    {
        return $this->logonError;
    }

    /**
     * @return boolean
     */
    public function isVerified()
    {
        return $this->verified;
    }
}