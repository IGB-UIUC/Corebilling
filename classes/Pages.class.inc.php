<?php
/**
 * Created by PhpStorm.
 * User: nevoband
 * Date: 5/20/14
 * Time: 1:34 PM
 *
 * Object manages website page names to allow for better integration with permissions and dynamic navigation
 */

class Pages {

    private $sqlDataBase;
    private $pages;
    private $default;
    private $navigationPages;
    private $showNavigation;

    public function __construct(PDO $sqlDataBase)
    {
        $this->sqlDataBase = $sqlDataBase;
        $this->LoadPages();
    }

    public function __destruct()
    {

    }

    /**
     * Load all available pages from database to this object
     */
    private function LoadPages()
    {
        $queryPages = "SELECT * FROM pages order by id";
        $pagesInfo = $this->sqlDataBase->prepare($queryPages);
        $pagesInfo->execute();
        $pagesArr = $pagesInfo->fetchAll(PDO::FETCH_ASSOC);
        $this->pages = array();
        $this->navigationPages = array();
        foreach($pagesArr as $id=>$page) {
            $this->pages[$page['page_name']]=array('id'=>$page['id'],'file'=>$page['file_name']);
            $this->navigationPages[$page['page_name']]=$page['show_navigation'];
            // TODO un-hard-code this value
            if($page['id'] == 1){
	            $this->SetDefaultPage($page['page_name']);
            }
        }
    }

    /**Get a page id given a page name
     * @param $pageName
     * @return mixed
     */
    public function GetPageId($pageName)
    {
        return $this->pages[$pageName]['id'];
    }
    
    public function GetPageFile($pageName){
	    return $this->pages[$pageName]['file_name'];
    }

    /**Get a list of available pages
     * @return mixed
     */
    public function GetPagesList()
    {
        return $this->pages;
    }

    /**Set the default page
     * @param $pageName
     */
    public function SetDefaultPage($pageName)
    {
        $this->default = $pageName;
    }

    /**Get all navigation pages
     * @return mixed
     */
    public function GetNavigationPages()
    {
        return $this->navigationPages;
    }

    /**Get the default page
     * @return int
     */
    public function GetDefaultPage()
    {
        return $this->default;
    }
} 