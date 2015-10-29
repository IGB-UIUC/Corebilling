</div>
<?php			if ($authenticate->isVerified()) { ?>
			<div class="col-md-2 col-md-pull-10">
	            <ul class="nav nav-pills nav-stacked">
	                <?php
	                if (isset($_GET['view'])) {
	                    $view = $_GET['view'];
	                } else {
	                    $view = DEFAULT_PAGE;
	                }
	
	                foreach ($pages->GetPagesList() as $pageName => $page){
	                    //If user is allowed to view the page then add it to the navigation options
	                    if ($accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $page['id']) != AccessControl::PERM_DISALLOW) {
	                        $cssClass = "";
	                        //Mark page as active on navigation if it is the selected page
	                        if ($view == $page['id']) {
	                            $cssClass = "class=active";
	                        }
	                        echo "<li " . $cssClass . "><a href=\"".$page['file']."\">" . $pageName . " </a></li>";
	                    }
	                }
	                ?>
	            </ul>
		    </div>
		    <?php } ?>
		</div>
	</body>
</html>
