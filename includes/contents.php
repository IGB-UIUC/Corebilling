<div class="col-md-10">
        <?php
        //Set page from GET view parameter
        if (isset($_GET['view'])) {
            $pageSelected = $_GET['view'];
        }
        else
        {
            //Set default page to load when user logs in from config.php file
            $pageSelected = $pages->GetPageId(DEFAULT_PAGE);
        }

        //verify the user is verified by the authentication system and that the user has permission to view the page they are requesting
        $userAccessLevel = $accessControl->GetPermissionLevel($authenticate->getAuthenticatedUser()->GetUserId(), AccessControl::RESOURCE_PAGE, $pageSelected);

        if ($authenticate->isVerified()
            && ($userAccessLevel == AccessControl::PERM_ALLOW
            || $userAccessLevel == AccessControl::PERM_ADMIN
            || $userAccessLevel == AccessControl::PERM_SUPERVISOR))
        {
            switch ($pageSelected) {
                case $pages->GetPageId('Latest News'):
                    include 'includes/news.php';
                    break;
                case $pages->GetPageId('Calendar'):
                    include 'includes/calendar_fullcalendar.php';
                    break;
                case $pages->GetPageId('User Billing'):
                    include 'includes/user_billing.php';
                    break;
                case $pages->GetPageId('Edit Groups'):
                    include 'includes/edit_groups.php';
                    break;
                case $pages->GetPageId('Devices In Use'):
                    include 'includes/in_use.php';
                    break;
                case $pages->GetPageId('Statistics'):
                    include 'includes/dev_statistics.php';
                    break;
                case $pages->GetPageId('Edit Devices'):
                    include 'includes/edit_device.php';
                    break;
                case $pages->GetPageId('Edit Users'):
                    include 'includes/edit_users.php';
                    break;
                case $pages->GetPageId('Facility Billing'):
                    include "includes/facility_billing.php";
                    break;
                case $pages->GetPageId('Edit Permissions'):
                    include "includes/edit_permissions.php";
                    break;
                case $pages->GetPageId('Edit Departments'):
                    include "includes/edit_departments.php";
                    break;
                default:
                    include "includes/login.php";
                    break;
            }
        } else {
            //Default denied page if user is not verified
            include "includes/login.php";
        }
        ?>
</div>