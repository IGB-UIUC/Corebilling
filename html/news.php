<?php
require_once 'includes/header.inc.php';

$article = new Articles($db);
$user = new User($db);

$articlesList = $article->GetArticles();
?>

<div>
	<?php if($login_user->isAdmin()){ ?>
	<a href="edit_news.php" class="btn btn-xs btn-primary pull-right"><span class="glyphicon glyphicon-plus"></span> Add News</a>
	<?php } ?>
	<h3>Latest News</h3>
</div>

<?php

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
		if ($login_user->isAdmin()) {
			echo "<a href=\"edit_news.php?edit=" . $articleInfo['id'] . "\">Edit</a> | <a href=\"edit_news.php?delete=" . $articleInfo['id'] . "\">Delete</a> | ";
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

