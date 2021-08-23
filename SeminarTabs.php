<?php
require_once dirname(__FILE__) .'/models/SeminarTab.class.php';

/**
 * SeminarTabs.class.php
 *
 * ...
 *
 * @author  <asudau@uos.de>
 */

require_once 'lib/classes/DBManager.class.php';

class SeminarTabs extends StudIPPlugin implements StandardPlugin
{
    /**
     * @var Container
     */

    public function __construct() {
        parent::__construct();

		global $perm;
		$this->course = Course::findCurrent();
	 	$this->course_id = $this->course->id;

		$this->course = Course::findCurrent();
		if ($this->course)
		{


		$this->setupStudIPNavigation();

	 	if (Navigation::hasItem('/course/admin') && $perm->have_studip_perm('dozent', $this->course_id)  ) {
            		$url = PluginEngine::getURL($this);
            		$scormItem = new Navigation(_('Inhaltselemente bearbeiten'), $url);
           		Navigation::addItem('/course/admin/seminar_tabs', $scormItem);
        	} else if (Navigation::hasItem('/admin/course/details')){
					$url = PluginEngine::getURL($this);
            		$scormItem = new Navigation(_('Inhaltselemente bearbeiten'), $url);
					Navigation::addItem('/admin/course/seminar_tabs', $scormItem);
			}
		}
    }

    // bei Aufruf des Plugins über plugin.php/mooc/...
    public function initialize ()
    {
        PageLayout::addStylesheet($this->getPluginUrl() . '/css/style.css');
        //PageLayout::addStylesheet($this->getPluginURL().'/assets/style.css');
        PageLayout::addScript($this->getPluginURL().'/js/script.js');
		$this->setupAutoload();
    }

    public function perform($unconsumed_path) {

	 //$this->setupAutoload();
        $dispatcher = new Trails_Dispatcher(
            $this->getPluginPath(),
            rtrim(PluginEngine::getLink($this, array(), null), '/'),
            'show'
        );
        $dispatcher->plugin = $this;
        $dispatcher->dispatch($unconsumed_path);

    }

    private function setupAutoload() {
        if (class_exists("StudipAutoloader")) {
            StudipAutoloader::addAutoloadPath(__DIR__ . '/models');
        } else {
            spl_autoload_register(function ($class) {
                include_once __DIR__ . $class . '.php';
            });
        }
    }

    private function setupStudIPNavigation(){

		$block = SeminarTab::findOneBySQL('seminar_id = ? ORDER BY position ASC',
                                 array($this->course_id) );
			if($block){
				$this->sortCourseNavigation();
			}

    }

    private function sortCourseNavigation(){
	global $perm;
   	$restNavigation = array();
	$newNavigation = Navigation::getItem('/course');
	foreach(Navigation::getItem('/course') as $key => $tab){
		$block = SeminarTab::findOneBySQL('seminar_id = ? AND tab IN (?) ORDER BY position ASC',
                                 array($this->getSeminarId(),$key) );
		if($block){
			$tab->setTitle($block->getValue('title'));
			if($block->getValue('tn_visible') == true || $perm->have_studip_perm('dozent', Request::get('cid')) ){
				$subNavigations[$block->getValue('position')][$key] = $tab;
			}

		} else {
		   //keine Info bezüglich Reihenfolge also hinten dran
		   //greift bei neu aktivierten Navigationselementen
		   $restNavigation[$key] = $tab;

		}

		$newNavigation->removeSubNavigation($key);
	}

	ksort($subNavigations);

	foreach($subNavigations as $subNavs){
	    foreach($subNavs as $key => $subNav){
		$newNavigation->addSubNavigation($key, $subNav);

	    }
	}
	if(count($restNavigation)>0){
	foreach($restNavigation as $key => $restNav){
		$newNavigation->addSubNavigation($key, $restNav);
	}
	}

	Navigation::addItem('/course', $newNavigation);
    }

    static function getSeminarId()
    {
        if (!Request::option('cid')) {
            if ($GLOBALS['SessionSeminar']) {
                URLHelper::bindLinkParam('cid', $GLOBALS['SessionSeminar']);
                return $GLOBALS['SessionSeminar'];
            }

            return false;
        }

        return Request::option('cid');
    }

    public function getInfoTemplate($course_id){
	return null;
    }
    public function getIconNavigation($course_id, $last_visit, $user_id){
	return null;
    }
    public function getTabNavigation($course_id){
	return null;
    }
    function getNotificationObjects($course_id, $since, $user_id){
    }



}
