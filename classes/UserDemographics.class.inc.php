<?php
class UserDemographics {
    const EDULEVEL_UNDERGRAD = "Undergraduate";
    const EDULEVEL_GRAD = "Graduate";
    const EDULEVEL_POSTDOC = "Postdoc";
    const EDULEVEL_FACULTY = "Faculty";
    const EDULEVEL_OTHER = "Other";

    const GENDER_FEMALE = "Female";
    const GENDER_MALE = "Male";
    const GENDER_OTHER = "Other";

    const UNDERREP_YES = "Yes";
    const UNDERREP_NO = "No";

    static function allEduLevels(){
        return array(self::EDULEVEL_UNDERGRAD,self::EDULEVEL_GRAD,self::EDULEVEL_POSTDOC,self::EDULEVEL_FACULTY,self::EDULEVEL_OTHER);
    }

    static function allGenders(){
        return array(self::GENDER_FEMALE,self::GENDER_MALE,self::GENDER_OTHER);
    }

    static function allUnderrepOptions(){
        return array(self::UNDERREP_YES,self::UNDERREP_NO);
    }

    private $db;
    private $user_id;
    private $edulevel;
    private $gender;
    private $underrep;

	/**
	 * UserDemographics constructor.
	 * @param PDO $db
	 * @param null|integer $id
	 */
	public function __construct(PDO $db, $id = null) {
        $this->db = $db;
        $this->user_id = 0;
        $this->edulevel = "";
        $this->gender = "";
        $this->underrep = "";

        if($id !== null){
            $this->load($id);
        }
    }

	/**
	 * Loads the demographic info for the user with the given id into the object
	 * @param integer $id user id to load
	 * @return bool whether load was successful
	 */
    public function load($id){
        $query = "SELECT * from user_demographics where user_id=?";
        $params = array($id);
        $stmt = $this->db->prepare($query);
        $stmt->execute($params);
        $demoArr = $stmt->fetch(PDO::FETCH_ASSOC);

        if($demoArr){
            $this->user_id = $demoArr['user_id'];
            $this->edulevel = $demoArr['edu_level'];
            $this->gender = $demoArr['gender'];
            $this->underrep = $demoArr['underrepresented'];
            return true;
        } else {
            $this->user_id = $id;
            $this->edulevel = "";
            $this->gender = "";
            $this->underrep = "";
            return false;
        }
    }

	/**
	 * Updates the database with the current values stored in the object
	 * @return bool
	 */
	public function update(){
        $query = "insert into user_demographics (user_id,edu_level,gender,underrepresented)
                  values (:id,:edu_level,:gender,:underrep)
                  on duplicate key update user_id=:id, edu_level=:edu_level, gender=:gender, underrepresented=:underrep";
        $params = array(':id'=>$this->user_id, ':edu_level'=>$this->edulevel, ':gender'=>$this->gender, ':underrep'=>$this->underrep);
        $stmt = $this->db->prepare($query);
        return $stmt->execute($params);
    }

    // Getters and Setters
    /**
     * @return int
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * @return string
     */
    public function getEdulevel()
    {
        return $this->edulevel;
    }

    /**
     * @param string $edulevel
     */
    public function setEdulevel($edulevel)
    {
        $this->edulevel = $edulevel;
    }

    /**
     * @return string
     */
    public function getGender()
    {
        return $this->gender;
    }

    /**
     * @param string $gender
     */
    public function setGender($gender)
    {
        $this->gender = $gender;
    }

    /**
     * @return string
     */
    public function getUnderrep()
    {
        return $this->underrep;
    }

    /**
     * @param string $underrep
     */
    public function setUnderrep($underrep)
    {
        $this->underrep = $underrep;
    }

}