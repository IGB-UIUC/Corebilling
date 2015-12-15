<?php

/**
 * Class Articles
 * Class used to manage the news column of the page
 * can load news articles, edit and delete
 */
class Articles
{
    private $sqlDataBase;
    private $userId;
    private $title;
    private $description;
    private $articleId;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
    }

    public function __destruct()
    {

    }

    /** Create a new new article
     * @param $userId
     * @param $title
     * @param $description
     */
    public function CreateArticle($userId, $title, $description)
    {
        $this->userId = $userId;
        $this->title = $title;
        $this->description = $description;

        $queryAddArticle = "INSERT INTO articles (created,text,title,user_id)VALUES(NOW(),:description, :title, :user_id)";

        $addArticle = $this->sqlDataBase->prepare($queryAddArticle);
        $addArticle->execute(array(':description' => $description, ':title' => $title, ':user_id' => $userId));
        $this->articleId = $this->sqlDataBase->lastInsertId();
    }

    /**Load an article using the article ID
     * @param $articleId
     */
    public function LoadArticle($articleId)
    {
        $queryLoadArticle = "SELECT * FROM articles WHERE id=:article_id";
        $loadArticle = $this->sqlDataBase->prepare($queryLoadArticle);
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
    public function RemoveArticle($articleId)
    {
        $queryDeleteArticle = "DELETE FROM articles WHERE id=:article_id";
        $deleteArticle = $this->sqlDataBase->prepare($queryDeleteArticle);
        $deleteArticle->execute(array('article_id' => $articleId));
    }

    /**
     * Update an article after setters were used to change the variables
     */
    public function UpdateArticle()
    {
        $queryUpdateArticle = "UPDATE articles SET created=NOW(), text=:description, title=:title, user_id=:user_id WHERE id=:articleid";
        $updateArticle = $this->sqlDataBase->prepare($queryUpdateArticle);
        $updateArticle->execute(array(':description' => $this->description, ':title' => $this->title, ':user_id' => $this->userId, ':articleid' => $this->articleId));
    }

    /** Get a list of all articles
     * @return PDOStatement
     */
    public function GetArticles()
    {
        $queryArticleList = "SELECT * FROM articles ORDER BY created DESC";
        $articleListArr = $this->sqlDataBase->query($queryArticleList);

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