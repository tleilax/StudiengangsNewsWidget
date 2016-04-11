<?php
namespace StudiengangsNews;

use PDO;
use DBManager;

/**
 * Class StudyCourse
 * @package StudiengangsNews
 * @author Chris Schierholz<chris.schierholz1@uni-oldenburg.de>
 */
class StudyCourse {
    private $study_courses;

    /**
     * StudyCourse constructor.
     *
     * @param $fk_ids
     * @param array $abschluss_ids
     * @param array $studiengang_ids
     */
    public function __construct($fk_ids = [], $abschluss_ids = [], $studiengang_ids = [])
    {
        $fk_ids = (empty($fk_ids))? [1, 2, 3, 4, 5, 6] : $fk_ids;
        $fk_ids = array_map(function ($val) { return 'x000000000000000000000000000000' . intval($val); } , $fk_ids);
        $values = [$fk_ids];
        $query = "SELECT COUNT(*) as count, fk_id, fach_id, abschluss_id, abschluss.name AS abschluss_name,
                  studiengaenge.name AS fach_name
                  FROM mod_zuordnung INNER JOIN abschluss using (abschluss_Id)
                  INNER JOIN studiengaenge ON fach_id = studiengang_id WHERE fk_id IN (?)";
        if(!empty($abschluss_ids)) {
            $query .= " AND abschluss_id IN (?)";
            $values[] = $abschluss_ids;
        }
        if(!empty($studiengang_ids)) {
            $query .= " AND fach_id IN (?)";
            $values[] = $studiengang_ids;
        }
        $query .= " GROUP BY fach_id, abschluss_id ORDER BY studiengaenge.name, abschluss.name;";
        $statement = DBManager::get()->prepare($query);
        $statement->execute($values);
        $result = $statement->fetchAll(PDO::FETCH_ASSOC);
        $this->study_courses = ($result)? $result : array();
    }

    /**
     * Returns the subjects
     * @return array
     */
    public function getSubjects()
    {
        $result = array();
        foreach($this->study_courses as $row) {
            $result[$row['fach_id']] = $row['fach_name'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getDegrees()
    {
        $result = array();
        foreach($this->study_courses as $row) {
            $result[$row['abschluss_id']] = $row['abschluss_name'];
        }
        return $result;
    }

    /**
     * @return array
     */
    public function getAbschlussIDs()
    {
        $ids = array();
        foreach($this->study_courses as $study_course) {
            $ids[] = $study_course['abschluss_id'];
        }
        return $ids;
    }

    /**
     * @return array
     */
    public function getStudiengangIDs()
    {
        $ids = array();
        foreach($this->study_courses as $study_course) {
            $ids[] = $study_course['fach_id'];
        }
        return $ids;
    }

    /**
     * Returns the FK_IDs
     * @return array
     */
    public function getFacultyIDs()
    {
        $fk_ids = [];
        foreach($this->study_courses as $study_course) {
            if(!in_array($study_course['fk_id'], $fk_ids)) {
                $fk_ids[] = $study_course ['fk_id'];
            }
        }
        return $fk_ids;
    }

    /**
     * Maps IDs -> Name in an array-esque form for abschlüsse / studiengänge.
     * @return array
     */
    public function toArray()
    {
        foreach($this->study_courses as $row) {
            $abschluesse[$row['abschluss_id']] = $row['abschluss_name'];
            $studiengaenge[$row['fach_id']] = $row['fach_name'];
        }
        return compact("abschluesse", "studiengaenge");
    }

    /**
     * @return array
     */
    public function get()
    {
        return $this->study_courses;
    }
}