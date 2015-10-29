<?php
require_once 'includes/header.inc.php';

$adminType = 1;
$selectedArticle = 0;

$article = new Articles($sqlDataBase);
$user = new User($sqlDataBase);

if (isset($_GET['edit'])) {
    $selectedArticle = $_GET['edit'];
    $article->LoadArticle($selectedArticle);
}

if (isset($_GET['delete']) && $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Latest News')) == AccessControl::PERM_ADMIN) {
    $article->RemoveArticle($_GET['delete']);
}


if (isset($_POST['applyEdit']) && $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Latest News')) == AccessControl::PERM_ADMIN) {

    $title = $_POST['title'];
    $bodyText = $_POST['text'];
    $articleId = $_POST['editArticleId'];
    $article->LoadArticle($articleId);
    $article->setTitle($title);
    $article->setDescription($bodyText);
    $article->setUserid($authenticate->getAuthenticatedUser()->GetUserId());
    $article->UpdateArticle();
    $article = new Articles($sqlDataBase);

}

if (isset($_POST['createNew']) && $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Latest News')) == AccessControl::PERM_ADMIN) {

    $title = $_POST['title'];
    $bodyText = $_POST['text'];
    $article->CreateArticle($authenticate->getAuthenticatedUser()->GetUserId(), $title, $bodyText);
}
$articlesList = $article->GetArticles();
?>

<div class="alert alert-info">
    <h4>Latest News</h4>
    <p>Please check the following articles for updates to our instruments and maintenance schedule.</p>
</div>

    <?php
    if ($accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Latest News')) == AccessControl::PERM_ADMIN) {

        echo "<form name=\"articlesForms\" action=\"index.php?view=".$pages->GetPageId('Latest News')."\" method=\"post\">";
        echo "<div class=\"well\">";
        echo "<div class=\"form-group\">
                <label for=\"newsTitle\">Title:</label><br>
                <input type=\"text\" size=\"60\" value=\"".$article->getTitle()."\" name=\"title\" class=\"form-control\">
              </div>
              <div class=\"form-group\">
                <label for=\"newsContent\">Content:</label>
                <textarea name=\"text\" style=\"width:100%; height:100px;\" class=\"form-control\">".$article->getDescription()."</textarea>
              </div>
              <input type=\"hidden\" name=\"editArticleId\" value=".$article->getArticleId().">";

        if($article->getArticleId())
        {
            echo "<input type=\"submit\" class=\"btn btn-primary\" name=\"applyEdit\" value=\"Edit Article\"><br><br>";
        }
        else
        {
		    echo "<input type=\"submit\" class=\"btn btn-primary\" name=\"createNew\" value=\"Create New Article\"><br><br>";
        }

        echo "</div>
        </form>";
    }

    foreach ($articlesList as $id => $articleInfo) {
        echo "<div class=\"panel panel-default\">
                <div class=\"panel-heading\"><h4><b>" . $articleInfo['title'] . "</b></h4></div>
		      <div class=\"panel-body\">
				" . $articleInfo['text'] . "
		      </div>";

        echo "<div class=\"panel-footer\">";
        if ($accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Latest News')) == AccessControl::PERM_ADMIN) {
            echo "<a href=\"index.php?view=" . $pages->GetPageId('Latest News') . "&edit=" . $articleInfo['id'] . "\">Edit</a> | <a href=\"index.php?view=" . $pages->GetPageId('Latest News') . "&delete=" . $articleInfo['id'] . "\">Delete</a> | ";
        }
        $user->LoadUser($articleInfo['user_id']);
        echo "<small>".$user->GetFirst() . " " . $user->GetLast() . " | " . $user->GetEmail() . " | " . $articleInfo['created'] . "</small>
        </div>
	</div>";
    }
    
require_once 'includes/footer.inc.php';
?>

