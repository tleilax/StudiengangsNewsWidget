<?php
namespace StudiengangsNews;

use PDO;
use DBManager;

/**
 * Defines a single studiengangs news entry.
 *
 * @author Chris Schierholz <chris.schierholz1@uni-oldenburg.de>
 */
class Entry extends \SimpleORMap
{
    /**
     * Configures the model.
     *
     * Connects to author through User model and provides additional fields
     * for the entry's number of views and the information whether the entry
     * has been seen / is new for the current user.
     *
     * @param Array $config Configuration array
     */
    public static function configure($config = array())
    {
            $config['db_table'] = 'studiengang_news_entries';
        $config['has_one']['author'] = array(
            'class_name'  => 'User',
            'assoc_foreign_key' => 'user_id',
            'foreign_key' => 'user_id',
        );
        $config['additional_fields']['is_new'] = array(
            'get' => function ($item) {
                return !object_get_visit($item->id, 'news', '', '', $GLOBALS['user']->id);
            },
            'set' => function ($item, $field, $value) {
                object_set_visit($item->id, 'news', $GLOBALS['user']->id);
                object_add_view($item->id);
            },
        );
        $config['additional_fields']['views'] = array(
            'get' => function ($item) {
                return object_return_views($item->id);
            },
            'set' => false,
        );
        parent::configure($config);
    }

    /**
     * Finds a set of entries by permission (and optionally by visible state).
     * Entries are visible when they are not yet expired.
     *
     * @param StudyCourse $study_courses
     * @param bool $only_visible Show only visible / not expired entries
     *                           (optional, defaults to true)
     * @return Array of matching entries
     */
    public static function findByStudyCourses(StudyCourse $study_courses, $only_visible = true)
    {
        if(empty($study_courses->get())) {
            return [];
        }

        $abschluss_ids = $study_courses->getAbschlussIDs();
        $studiengang_ids = $study_courses->getStudiengangIDs();

        $query = "SELECT DISTINCT e.news_id FROM studiengang_news_entries AS e
                  INNER JOIN studiengang_news_abschluss AS a ON a.news_id = e.news_id
                    AND (abschluss_id = '' OR abschluss_id IN (?))
                  INNER JOIN studiengang_news_studiengang AS s ON s.news_id = e.news_id
                    AND (studiengang_id = '' OR studiengang_id IN (?))
                  WHERE e.fk_id IN (?)";
        if ($only_visible) {
            $query .= " AND expires > UNIX_TIMESTAMP()";
        }
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$abschluss_ids, $studiengang_ids, $study_courses->getFacultyIDs()]);
        $result = $statement->fetchAll(PDO::FETCH_COLUMN);

        return self::findBySQL("news_id IN (?) ORDER BY mkdate DESC", [$result]);
    }

    /**
     * @param $user_id
     * @return array
     */
    public static function findByStudent($user_id)
    {
        $query = "SELECT DISTINCT news_id FROM studiengang_news_entries e
                  INNER JOIN studiengang_news_abschluss a using(news_id)
                  INNER JOIN studiengang_news_studiengang s using(news_id)
                  INNER JOIN user_studiengang us
                      ON  us.user_id = ?
                      AND (a.abschluss_id = '' OR a.abschluss_id = us.abschluss_id)
                      AND (s.studiengang_id = '' OR s.studiengang_id = us.studiengang_id)
                      AND (fs_qualifier = 'no_filter'
                          OR (fs_qualifier = 'equals' AND us.semester = e.fachsemester)
                          OR (fs_qualifier = 'smaller_equals' AND us.semester <= e.fachsemester)
                          OR (fs_qualifier = 'greater_equals' AND us.semester >= e.fachsemester))
                  INNER JOIN mod_zuordnung mz
                      ON us.abschluss_id = mz.abschluss_id
                      AND us.studiengang_id = mz.fach_id
                      AND mz.fk_id = e.fk_id
                  WHERE expires > UNIX_TIMESTAMP()";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$user_id]);
        $result = $statement->fetchAll(PDO::FETCH_COLUMN);

        return (empty($result)) ? [] : self::findBySQL("news_id IN (?) ORDER BY mkdate DESC", [$result]);
    }

    /**
     * Counts the number of entries that are currently visible for this study course.
     * @param $abschluss_id
     * @param $studiengang_id
     * @return mixed
     */
    public static function getEntriesCountForStudyCourse($abschluss_id, $studiengang_id, $fk_id)
    {
        $query = "SELECT COUNT(*) FROM studiengang_news_entries AS e
                  INNER JOIN studiengang_news_abschluss AS a ON a.news_id = e.news_id
                    AND (abschluss_id = '' OR abschluss_id = ?)
                  INNER JOIN studiengang_news_studiengang AS s ON s.news_id = e.news_id
                    AND (studiengang_id = '' OR studiengang_id = ?)
                  WHERE expires > UNIX_TIMESTAMP() AND e.fk_id = ?";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$abschluss_id, $studiengang_id, $fk_id]);
        return $statement->fetchColumn();
    }

    /**
     * Returns the selected Abschluss_IDs/Studiengang_IDs for this Entry.
     * @return array
     */
    public function getStudyCoursesFilter()
    {
        $query = "SELECT abschluss_id FROM studiengang_news_abschluss WHERE news_id = ? AND abschluss_id <> ''";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        $abschluss_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        $query = "SELECT studiengang_id FROM studiengang_news_studiengang WHERE news_id = ? AND studiengang_id <> ''";
        $statement = DBManager::get()->prepare($query);
        $statement->execute([$this->id]);
        $studiengang_ids = $statement->fetchAll(PDO::FETCH_COLUMN);

        return compact("abschluss_ids", "studiengang_ids");
    }

    /**
     * Sets the selected Abschluss_IDs/Studiengang_IDs for this Entry.
     * @param array $abschluss_ids
     * @param array $studiengaeng_ids
     */
    public function setStudyCourses($abschluss_ids = [], $studiengaeng_ids = [])
    {
        $statement = DBManager::get()->prepare("DELETE FROM studiengang_news_abschluss WHERE news_id = ?");
        $statement->execute([$this->id]);
        $statement = DBManager::get()->prepare("DELETE FROM studiengang_news_studiengang WHERE news_id = ?");
        $statement->execute([$this->id]);

        $query = "INSERT INTO studiengang_news_abschluss VALUES(?, ?)";
        $statement = DBManager::get()->prepare($query);
        if(empty($abschluss_ids)) {
            $statement->execute([$this->id, ""]);
        } else {
            foreach ($abschluss_ids as $id) {
                $statement->execute([$this->id, $id]);
            }
        }

        $query = "INSERT INTO studiengang_news_studiengang VALUES(?, ?)";
        $statement = DBManager::get()->prepare($query);
        if(empty($studiengaeng_ids)) {
            $statement->execute([$this->id, ""]);
        } else {
            foreach ($studiengaeng_ids as $id) {
                $statement->execute([$this->id, $id]);
            }
        }
    }

    /**
     * @param null $filter can be set manually when entry is not stored in db.
     * @return array
     */
    public function countAddressedUsers($filter = NULL)
    {
        $query= "SELECT COUNT(DISTINCT us.user_id)
                 FROM user_studiengang us
                 INNER JOIN mod_zuordnung mz
                     ON us.abschluss_id = mz.abschluss_id
                     AND us.studiengang_id = mz.fach_id
                     AND mz.fk_id IN (?)";
        $filter = (!$this->isNew())? $this->getStudyCoursesFilter() : $filter;
        $conditions = [];
        $values = [$this->fk_id];

        if(!empty($filter['abschluss_ids'])) {
            $conditions[] = "us.abschluss_id IN (?)";
            $values[] = $filter['abschluss_ids'];
        }
        if(!empty($filter['studiengang_ids'])) {
            $conditions[] = "us.studiengang_id IN (?)";
            $values[] = $filter['studiengang_ids'];
        }
        switch($this->fs_qualifier) {
            case 'equals':
                $conditions[] = "us.semester = ?";
                $values[] = $this->fachsemester;
                break;
            case 'smaller_equals':
                $conditions[] = "us.semester <= ?";
                $values[] = $this->fachsemester;
                break;
            case 'greater_equals':
                $conditions[] = "us.semester >= ?";
                $values[] = $this->fachsemester;
                break;
            default:
                break;
        }
        if(!empty($conditions)) {
            $query .= " WHERE " . implode(' AND ', $conditions);
        }

        $statement = DBManager::get()->prepare($query);
        $statement->execute($values);

        return $statement->fetchColumn();
    }

    /**
     * Overloaded delete method of the entry. Removes associated views
     * and visits.
     *
     * @return mixed false on error, otherwise the number of deleted records
     * @see SimpleORMap::delete
     */
    public function delete()
    {
        $result = parent::delete();

        // Remove views and visits
        if ($result !== false) {
            object_kill_visits(false, $this->id);
            object_kill_views($this->id);

            $statement = DBManager::get()->prepare("DELETE FROM studiengang_news_abschluss WHERE news_id = ?");
            $statement->execute([$this->id]);
            $statement = DBManager::get()->prepare("DELETE FROM studiengang_news_studiengang WHERE news_id = ?");
            $statement->execute([$this->id]);
        }
        return $result;
    }
}
