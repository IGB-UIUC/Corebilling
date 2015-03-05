<div class="col-md-2">
    <div class="panel panel-default">
        <div class="panel-heading">

        </div>
        <div class="pane-body">
            <ul class="nav nav-pills nav-stacked">
                <?php
                if (isset($_GET['view'])) {
                    $view = $_GET['view'];
                } else {
                    $view = DEFAULT_PAGE;
                }

                foreach ($pages->GetPagesList() as $pageName => $pageId)
                {
                    //If user is allowed to view the page then add it to the navigation options
                    if ($accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pageId) != AccessControl::PERM_DISALLOW) {
                        $cssClass = "";
                        //Mark page as active on navigation if it is the selected page
                        if ($view == $pageId) {
                            $cssClass = "class=active";
                        }
                        echo "<li " . $cssClass . "><a href=\"./index.php?view=" . $pageId . "\">" . $pageName . " </a></li>";
                    }
                }
                ?>
            </ul>
        </div>
        <div class="panel-footer">
            </div>
    </div>
</div>
