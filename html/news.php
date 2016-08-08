<?php
require_once 'includes/header.inc.php';
$access = $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pages->GetPageId('Latest News'));
if($access == AccessControl::PERM_DISALLOW){
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

if (isset($_GET['delete']) && $access == AccessControl::PERM_ADMIN) {
	$article->RemoveArticle($_GET['delete']);
}


if (isset($_POST['applyEdit']) && $access == AccessControl::PERM_ADMIN) {
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

if (isset($_POST['createNew']) && $access == AccessControl::PERM_ADMIN) {

	$title = $_POST['title'];
	$bodyText = $_POST['text'];
	$article->CreateArticle($authenticate->getAuthenticatedUser()->GetUserId(), $title, $bodyText);
}
$articlesList = $article->GetArticles();
?>


	<h3>Latest News</h3>

<?php
// Admin controls
if ($access == AccessControl::PERM_ADMIN) {
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
			echo "<input type=\"submit\" class=\"btn btn-primary\" name=\"applyEdit\" value=\"Edit Article\">";
		} else {
			echo "<input type=\"submit\" class=\"btn btn-primary\" name=\"createNew\" value=\"Create New Article\">";
		}
?>
	</div>
</form>
<?php
}

foreach ($articlesList as $id => $articleInfo) { 
	$formattedText = str_replace("\n", "<br/>", $articleInfo['text']);
?>
<div class="panel panel-default">
	<div class="panel-heading"><h4><b><?php echo $articleInfo['title'];?></b></h4></div>
	<div class="panel-body">
		<?php echo $formattedText; ?>
	</div>
	<div class="panel-footer">
		<?php
		if ($access == AccessControl::PERM_ADMIN) {
			echo "<a href=\"news.php?edit=" . $articleInfo['id'] . "\">Edit</a> | <a href=\"news.php?delete=" . $articleInfo['id'] . "\">Delete</a> | ";
		}
		$user->LoadUser($articleInfo['user_id']);
		echo "<small>".$user->GetFirst() . " " . $user->GetLast() . " | " . $user->GetEmail() . " | " . $articleInfo['created'] . "</small>";
		?>
	</div>
</div>
	<?php
	}
	
require_once 'includes/footer.inc.php';
?>

