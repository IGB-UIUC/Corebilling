<?php

/**
 * Class Article
 * Class used to manage the news column of the page
 * can load news articles, edit and delete
 */
class Article
{
    private $db;
    
    private $userId;
    private $title;
    private $description;
    private $articleId;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }

    public function __destruct()
    {

    }

    /** Create a new new article
     * @param $userId
     * @param $title
     * @param $description
     */
    public function create($userId, $title, $description)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;

        $queryAddArticle = "INSERT INTO articles (created,text,title,user_id)VALUES(NOW(),:description, :title, :user_id)";

        $addArticle = $this->db->prepare($queryAddArticle);
        $addArticle->execute(array(':description' => $description, ':title' => $title, ':user_id' => $userId));
        $this->articleId = $this->db->lastInsertId();
    }

    /**Load an article using the article ID
     * @param $articleId
     */
    public function load($articleId)
    {
        $queryLoadArticle = "SELECT * FROM articles WHERE id=:article_id";
        $loadArticle = $this->db->prepare($queryLoadArticle);
        $loadArticle->execute(array(':article_id' => $articleId));
        $loadArticleArr = $loadArticle->fetch(PDO::FETCH_ASSOC);
        if ($loadArticleArr) {
            $this->articleId = $articleId;
            $this->title = $loadArticleArr['title'];
            $this->description = $loadArticleArr['text'];
            $this->userId = $loadArticleArr['user_id'];
        }
    }

    /** Delete and article from the database
     * @param $articleId
     */
    public static function removeArticle($db, $articleId)
    {
        $queryDeleteArticle = "DELETE FROM articles WHERE id=:article_id limit 1";
        $deleteArticle = $db->prepare($queryDeleteArticle);
        $deleteArticle->execute(array('article_id' => $articleId));
    }

    /**
     * Update an article after setters were used to change the variables
     */
    public function update()
    {
        $queryUpdateArticle = "UPDATE articles SET created=NOW(), text=:description, title=:title, user_id=:user_id WHERE id=:articleid";
        $updateArticle = $this->db->prepare($queryUpdateArticle);
        $updateArticle->execute(array(':description' => $this->description, ':title' => $this->title, ':user_id' => $this->userId, ':articleid' => $this->articleId));
    }

    /** Get a list of all articles
     * @return PDOStatement
     */
    public static function getAllArticles($db)
    {
        $queryArticleList = "SELECT * FROM articles ORDER BY created DESC";
        $articleListArr = $db->query($queryArticleList);

        return $articleListArr;
    }

    /**
     * @param mixed $title
     */
    public function setTitle($title)
    {
        $this->title = $title;
    }

    /**
     * @return mixed
     */
    public function getTitle()
    {
        return $this->title;
    }

    /**
     * @param mixed $userId
     */
    public function setUserid($userId)
    {
        $this->userId = $userId;
    }

    /**
     * @return mixed
     */
    public function getUserid()
    {
        return $this->userId;
    }

    /**
     * @param mixed $description
     */
    public function setDescription($description)
    {
        $this->description = $description;
    }

    /**
     * @return mixed
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * @param mixed $articleId
     */
    public function setArticleId($articleId)
    {
        $this->articleId = $articleId;
    }

    /**
     * @return mixed
     */
    public function getArticleId()
    {
        return $this->articleId;
    }
}

?>