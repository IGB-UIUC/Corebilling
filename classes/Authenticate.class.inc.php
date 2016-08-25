<?php

/**
 * Class Authenticate
 *
 * Used to authenticate a user using ldap and set session variables and authentication keys.
 */
class Authenticate {
    private $sqlDataBase;
    private $ldapAuth;
    private $authenticatedUser;
    private $logonError;
    private $verified;

    public function __construct(PDO $sqlDataBase, LdapAuth $ldapAuth)
    {
        $this->sqlDataBase = $sqlDataBase;
        $this->ldapAuth = $ldapAuth;
        $this->verified = false;
        $this->authenticatedUser = new User($this->sqlDataBase);
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
        if ($this->ldapAuth->Authenticate ( $userName, $password) )
        {
            $userId = $this->authenticatedUser->Exists($userName);
            if ($userId)
            {
                //If user is in the system then load this user
                $this->authenticatedUser->LoadUser($userId);
            } else {
	            // If user is not in the system, deny them.
	            $this->verified=false;
	            $this->logonError = "Unauthorized user.";
	            return false;
/*
                //If user is not in system then create a default profile for them
                $this->authenticatedUser->CreateUser($userName,'','',
                                                        $userName.'@'.DEFAULT_USER_EMAIL_DOMAIN,
                                                        DEFAULT_USER_DEPARTMENT_ID,
                                                        DEFAULT_USER_GROUP_ID,
                                                        DEFAULT_USER_RATE_ID,
                                                        DEFAULT_USER_STATUS_ID,
                                                        DEFAULT_USER_ROLE_ID);

                try{
                    $mail = new Mailer();
                    $mail->setFrom(PAGE_TITLE,ADMIN_EMAIL);
                    $mail->addRecipient('Admin',ADMIN_EMAIL);
                    $mail->fillSubject("New user created: ".$userName);
                    $mail->fillMessage("New user has logged into the ".PAGE_TITLE." website.\n Account was created for: ".$userName);
                    $mail->send();
                } catch(Exception $e)
                {
                    echo $e->getMessage();
                }
*/

            }

            //Generate a secure key for user
            $this->authenticatedUser->UpdateSecureKey();
            $this->SetSession($this->authenticatedUser->GetSecureKey(), $this->authenticatedUser->GetUserId() );
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
        if(isset($_SESSION['coreapp_user_id']))
        {
            if(time() - $_SESSION['coreapp_created'] < 7200)
            {
                $this->authenticatedUser = new User ( $this->sqlDataBase );
                $this->authenticatedUser->LoadUser($_SESSION['coreapp_user_id']);

                if($this->authenticatedUser->GetSecureKey() == $_SESSION['coreapp_key'])
                {
                    $this->authenticatedUser->UpdateSecureKey();
                    $this->SetSession($this->authenticatedUser->GetSecureKey(), $this->authenticatedUser->GetUserId());
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
    }

    /**
     * Removes session information when the user logs out or expired login
     */
    public function UnsetSession()
    {
        unset ( $_SESSION ['coreapp_user_id'] );
        unset ( $_SESSION ['coreapp_key'] );
        unset ( $_SESSION ['coreapp_created'] );
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