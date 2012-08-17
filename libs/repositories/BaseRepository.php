<?php
abstract class BaseRepository
{

    public function query($name, array $params = null, $array=false)
    {
        if (!strpos($name, '.')) $name = "shop.$name";
        if ($params) $params = (object) $params;
        $function = 'executeQuery' . ($array ? 'Array' : '');
        $output = $function($name, $params);
        return self::check($output);
    }

    public static function check($output)
    {
        if (!is_object($output)) throw new Exception('A valid query output is expected here');
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
    }

}