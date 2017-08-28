<?php
namespace StudiengangsNews;

/**
 * Class Config
 * @package StudiengangsNews
 * @author Chris Schierholz<chris.schierholz1@uni-oldenburg.de>
 */
class Config {

    /**
     * Mapping of FK_ID -> correspondending Institut_id
     * @return array
     */
    public static function Get()
    {
        return [
            1 => '7cadba7e78aee20b315bb023f768245d',
            2 => 'a53966b8dfb7d449273f99eaa862c5eb',
            3 => '57abeb46e399578f83dee5d39d3c4d44',
            4 => 'c630e172a8194fab5261d5ce7fabdbee',
            5 => '9b164d33312500bd3b5bdc0287934eab',
            6 => 'aa53a9f854d6e15c3823fa9354882a4e',
        ];
    }

    public static function mapFacultyNumberToId($number)
    {
        $mapping = self::Get();
        return $mapping[$number];
    }
}
