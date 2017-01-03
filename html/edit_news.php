<?php
require_once 'includes/header.inc.php';
if(!$login_user->isAdmin()){
	echo html::error_message("You do not have permission to view this page.","403 Forbidden");
	require_once 'includes/footer.inc.php';
	exit;
}

$adminType = 1;
$selectedArticle = 0;

$article = new Articles($sqlDataBase);
$user = new User($sqlDataBase);

if (isset($_GET['edit'])) {
	$selectedArticle = $_GET['edit'];
	$article->LoadArticle($selectedArticle);
}

if (isset($_GET['delete']) && $login_user->isAdmin()) {
	$article->RemoveArticle($_GET['delete']);
	header('location: news.php');
	exit();
}


if (isset($_POST['applyEdit']) && $login_user->isAdmin()) {
	$title = $_POST['title'];
	$bodyText = $_POST['text'];
	$articleId = $_POST['editArticleId'];
	$article->LoadArticle($articleId);
	$article->setTitle($title);
	$article->setDescription($bodyText);
	$article->setUserid($authenticate->getAuthenticatedUser()->GetUserId());
	$article->UpdateArticle();
	header('location: news.php');
	exit();
}

if (isset($_POST['createNew']) && $login_user->isAdmin()) {
	$title = $_POST['title'];
	$bodyText = $_POST['text'];
	$article->CreateArticle($authenticate->getAuthenticatedUser()->GetUserId(), $title, $bodyText);
	header('location: news.php');
	exit();
}
$articlesList = $article->GetArticles();
?>

<div>
	<h3><?php if($article->getArticleId()){ echo "Edit News"; } else { echo "Add News"; } ?></h3>
</div>
<?php
// Admin controls
if ($login_user->isAdmin()) {
?>
<form name="articlesForms" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="post">
	<div class="well">
		<div class="form-group">
			<label for="newsTitle">Title:</label><br>
			<input type="text" size="60" value="<?php echo $article->getTitle();?>" name="title" class="form-control">
		</div>
		<div class="form-group">
			<label for="newsContent">Content:</label>
			<textarea name="text" style="width:100%; height:100px;" class="form-control"><?php echo $article->getDescription();?></textarea>
		</div>
			<input type="hidden" name="editArticleId" value="<?php echo $article->getArticleId();?>">
<?php
		if($article->getArticleId()) {
			echo "<input type=\"submit\" class=\"btn btn-primary\" name=\"applyEdit\" value=\"Update Article\">";
		} else {
			echo "<input type=\"submit\" class=\"btn btn-primary\" name=\"createNew\" value=\"Create New Article\">";
		}
?>
	</div>
</form>
<?php
}
	
require_once 'includes/footer.inc.php';
?>

