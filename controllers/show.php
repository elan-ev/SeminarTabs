<?php




class ShowController extends StudipController {

    public function __construct($dispatcher)
    {
        parent::__construct($dispatcher);
        $this->plugin = $dispatcher->plugin;

    }

    public function before_filter(&$action, &$args) {
	$this->set_layout($GLOBALS['template_factory']->open('layouts/base_without_infobox'));
       if (Navigation::hasItem('/course/admin')) {
            Navigation::activateItem('/course/admin/seminar_tabs');
        } else if (Navigation::hasItem('/admin/course/seminar_tabs')) {
            Navigation::activateItem('/admin/course/seminar_tabs');
        } 

	$this->course = Course::findCurrent();
	 if (!$this->course) {
            throw new CheckObjectException(_('Sie haben kein Objekt gewählt.'));
        }
	
	 $this->ignore_tabs = array('modules');
	 $this->course_id = $this->course->id;
	 $this->sem = Seminar::getInstance($this->course_id);
        $sem_class = $GLOBALS['SEM_CLASS'][$GLOBALS['SEM_TYPE'][$this->sem->status]['class']];
        $sem_class || $sem_class = SemClass::getDefaultSemClass();
        $this->studygroup_mode = $SEM_CLASS[$SEM_TYPE[$this->sem->status]["class"]]["studygroup_mode"];
    }

    public function index_action() {
	
	global $perm;

	$this->courseadmin = $perm->have_studip_perm('tutor', $this->course_id);


	//Tabs und zugehörige Einstellung laden
		$position = 100;
		foreach( Navigation::getItem('course') as $key=>$tab){
		    //systemtabs anlegen/abfragen
		    $query = "SELECT title FROM `system_tabs` WHERE tab IN (:key)" ;
		    $statement = DBManager::get()->prepare($query);
		    $statement->execute(array('key' => $key));
        	    $orig_title = $statement->fetchAll(PDO::FETCH_ASSOC);
		    
		    if (!$orig_title[0]){
			$values = array('id' => md5($key), 'tab' => $key, 'title' => $tab->getTitle());
			$query = "INSERT INTO `system_tabs` (`id`, `tab`, `title`) VALUES (:id, :tab, :title)" ;
			$statement = DBManager::get()->prepare($query);
			$statement->execute($values);
			$orig_title[0]['title'] = $tab->getTitle();
		    }
			
		    if(!in_array($tab->getTitle(), $this->ignore_tabs)){
		    	$block = SeminarTab::findOneBySQL('seminar_id = ? AND tab IN (?) ORDER BY position ASC',
                                 array($this->course_id, $key) );
			if ($block && !in_array($key, $this->ignore_tabs)){
		    		$this->tabs[] = array('tab' => $block->getValue('tab'), 
						 'title' => $block->getValue('title'),
					  	 'position' => $block->getValue('position'),
						 'orig_title' => $orig_title[0]['title']
						);
			} else if (!in_array($key, $this->ignore_tabs)){
			      $this->tabs[] = array('tab' => $key,
						 'title' => $tab->getTitle(), 
						 'position' => $position,
						 'orig_title' => $orig_title[0]['title']
					  );
			}
			$position++;
		    } 
		}
	 $this->tabs = $this->array_sort($this->tabs, 'position', SORT_ASC);

    }
	
     public function save_action() {
	
	$this->tabs = $_POST;
	$tab_count = intval($this->tabs['tab_num']);


	$order = explode(',',$this->tabs['new_order']);
	$position = 1;
	foreach($order as $o){
	    $this->tabs['tab_position_'. $o] = $position;
	    $position++;
	}
	

	for ($i = 0; $i < $tab_count; $i++){

		$block = new SeminarTab();
		
		//falls noch kein Eintrag existiert: anlegen
		if (!SeminarTab::findOneBySQL('seminar_id = ? AND tab IN (?) ORDER BY position ASC',
                                 array($this->course_id,$this->tabs['tab_title_'. $i]))){
			$block->setData(array(
            		'seminar_id' => $this->course_id,
           		'tab'       => $this->tabs['tab_title_'. $i],
			'title'       => $this->tabs['new_tab_title_'. $i],
			'position'       => $this->tabs['tab_position_'. $i]
        		));	

        		$block->store();
		} 

		//falls ein Eintrag existiert: anpassen
		else {
			$block = SeminarTab::findOneBySQL('seminar_id = ? AND tab IN (?) ORDER BY position ASC',
                                 array($this->course_id,$this->tabs['tab_title_'. $i]));
			$block->setValue('title', $this->tabs['new_tab_title_'. $i]);
			$block->setValue('position', $this->tabs['tab_position_'. $i]);
			$block->store();

		}
	}


    }


    public function overview_action() {
		
    }

     public function settings_action() {
	
    }  
  
    function array_sort($array, $on, $order=SORT_ASC)
    {
    $new_array = array();
    $sortable_array = array();

    if (count($array) > 0) {
        foreach ($array as $k => $v) {
            if (is_array($v)) {
                foreach ($v as $k2 => $v2) {
                    if ($k2 == $on) {
                        $sortable_array[$k] = $v2;
                    }
                }
            } else {
                $sortable_array[$k] = $v;
            }
        }

        switch ($order) {
            case SORT_ASC:
                asort($sortable_array);
            break;
            case SORT_DESC:
                arsort($sortable_array);
            break;
        }

        foreach ($sortable_array as $k => $v) {
            $new_array[$k] = $array[$k];
        }
    }

    return $new_array;
    }
  
  function url_for($to)
    {
        $args = func_get_args();

        # find params
        $params = array();
        if (is_array(end($args))) {
            $params = array_pop($args);
        }

        # urlencode all but the first argument
        $args = array_map('urlencode', $args);
        $args[0] = $to;

        return PluginEngine::getURL($this->dispatcher->plugin, $params, join('/', $args));
    } 

}
