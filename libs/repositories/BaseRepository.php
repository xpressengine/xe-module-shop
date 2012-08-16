<?php
abstract class BaseRepository
{

    public static function check($output)
    {
        if (!is_object($output)) throw new Exception('A valid query output is expected here');
        if (!$output->toBool()) throw new Exception($output->getMessage(), $output->getError());
        return $output;
    }

    public static function getMemberSrl()
    {
        $logged_info = Context::get('logged_info');
        return $logged_info->member_srl;
    }

    public static function getGuestSrl()
    {
        return null;
    }

}