<?php


/**
 * @author  <mlunzena@uos.de>
 *
 * @property int     $id
 * @property string  $type
 * @property string  $sub_type
 * @property int     $parent_id
 * @property Block   $parent
 * @property Block[] $children
 * @property string  $seminar_id
 * @property \Course $course
 * @property string  $title
 * @property int     $position
 * @property int     $publication_date
 * @property int     $chdate
 * @property int     $mkdate
 */
class SeminarTab extends \SimpleORMap implements \Serializable
{

    public $errors = array();

     protected static function configure($config = [])
     {
        $config['db_table'] = 'seminar_tabs';

        $config['belongs_to']['course'] = array(
            'class_name'  => '\\Course',
            'foreign_key' => 'seminar_id');

        parent::configure($config);
    }

    /**
     * Sets the seminar id of the block just before storing it to the database
     * if the current course information is stored in the session.
     */
    protected function ensureSeminarId()
    {
        // TODO: (mlunzena) we cannot be sure to have a
        // SessionSeminar, so we must get that value somewhere else
        if ($this->seminar_id === null && isset($GLOBALS['SessionSeminar'])) {
            $this->seminar_id = $GLOBALS['SessionSeminar'];
        } elseif ($this->seminar_id === '') {
            // workaround to allow blocks that are not associated with a course
            $this->seminar_id = null;
        }
    }

    /**
     * Calculates the position of a new block by counting the already existing
     * blocks on the same level.
     */
    protected function ensurePositionId()
    {
        if ($this->parent_id !== null && $this->position === null) {
            $this->position = static::countBySQL(
                'parent_id = ? ORDER BY position ASC',
                array($this->parent_id)
            );
        }
    }

    public function getAncestors()
    {
        $ancestors = array();
        $cursor = $this;
        while ($cursor->parent) {
            $ancestors[] = $cursor->parent;
            $cursor = $cursor->parent;
        }

        return array_reverse($ancestors);
    }

    public function nextSibling()
    {
        return static::findOneBySQL('parent_id = ? AND position > ? ORDER BY position ASC', array($this->parent_id, $this->position));
    }

    public function previousSibling()
    {
        return static::findOneBySQL('parent_id = ? AND position < ? ORDER BY position DESC', array($this->parent_id, $this->position));
    }

    public static function findByParent_id($id)
    {
        return static::findBySQL('parent_id = ? ORDER BY position ASC', array($id));
    }


    public static function findCourseware($cid)
    {
        return current(self::findBySQL('seminar_id = ? AND parent_id IS NULL LIMIT 1', array($cid)));
    }

    /**
     * Find all Block of given types in a single course.
     *
     * @param string $cid    the ID of the course
     * @param mixed  $types  either a string containing a single block type
     *                       or an array of strings containing block types
     *
     * @return array  an array of Block instances of those types in
     *                that course
     */

    public static function findInCourseByType($cid, $type)
    {
        if (!is_array($type)) {
            $types = (array) $type;
        }

        return static::findBySQL('seminar_id = ? AND tab IN (?) ORDER BY position ASC',
                                 array($cid, $type));
    }


    // enumerate all known structural block classes
    private static $structural_block_classes = array('Courseware', 'Chapter', 'Subchapter', 'Section');

    /**
     * Return all known structural block classes.
     *
     * @return array  all known structure classes
     */
    public static function getStructuralBlockClasses()
    {
        return self::$structural_block_classes;
    }

    /**
     * Returns whether this block is a structural block.
     *
     * @return bool  `true` if it is a structural block, `false` otherwise
     */
    public function isStructuralBlock()
    {
        return in_array($this->type, self::$structural_block_classes);
    }

    /**
     * checks, if block is valid
     *
     * @return boolean true or false
     */
    function validate() {
        if (!strlen(trim($this->title))) {
            $this->errors[] = "Title may not be empty.";
            return false;
        }
        return true;
    }

    /**
     * Remove associated Fields on delete.
     */
    function destroyFields()
    {
        Field::deleteBySQL('block_id = ?', array($this->id));
    }

    /**
     * Remove associated UserProgress on delete.
     */
    function destroyUserProgress()
    {
        UserProgress::deleteBySQL('block_id = ?', array($this->id));
    }

    /**
     * Reflects changes in position if a block on one level is deleted.
     */
    public function updatePositionsAfterDelete()
    {
        if (!$this->parent) {
            return;
        }

        $db = \DBManager::get();
        $stmt = $db->prepare(sprintf(
            'UPDATE
              %s
            SET
              position = position - 1
            WHERE
              parent_id = :parent_id AND
              position > :position',
            $this->db_table
        ));
        $stmt->bindValue(':parent_id', $this->parent->id);
        $stmt->bindValue(':position', $this->position);
        $stmt->execute();
    }

    /**
     * Update child sorting
     *
     * @param array $positions the new sort order
     */
    function updateChildPositions($positions)
    {
        $query = sprintf(
            'UPDATE %s SET position = FIND_IN_SET(id, ?) WHERE parent_id = ?',
            $this->db_table);
        $args = array(join(',', $positions), $this->id);

        $db = \DBManager::get();
        $st = $db->prepare($query);
        $st->execute($args);
    }

    /**
     *
     * @param int $timestamp
     * @return boolean
     */
    function isPublished($timestamp = null)
    {
        if (is_null($timestamp)) {
            $timestamp = time();
        }

        // check if parent blocks are published
        if ($this->parent && !$this->parent->isPublished($timestamp)) {
            return false;
        }

        // check if block is published
        return $this->publication_date <= $timestamp;
    }

    public function serialize()
    {
        if ($this->isDirty()) {
            throw new \RuntimeException('Cannot serialize dirty Block instances.');
        }

        return serialize(array($this->content, $this->is_new));
    }

    public function unserialize($serialized)
    {
        static::__construct();

        list($data, $is_new) = unserialize($serialized);
        $this->setData($data, true);
        $this->setNew($is_new);
    }

    public function callbackToMetrics($callback_type)
    {
        if ($this->type) {
            $metric = sprintf('moocip.block.%s.%s',
                              strtolower($this->type),
                              substr(strtolower($callback_type), strlen('after_')));
            \Metrics::increment($metric);
        }
    }
}
