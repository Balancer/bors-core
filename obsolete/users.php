<?php

function get_ip_nick()
{
        if(!empty($_SERVER['REMOTE_ADDR']))
            $addrs[] = $_SERVER['REMOTE_ADDR'];

        if(!empty($_SERVER['HTTP_X_FORWARDED_FOR']))
            $addrs[] = 'fw:'.$_SERVER['HTTP_X_FORWARDED_FOR'];

        if(!empty($_SERVER['HTTP_VIA']))
            $addrs[] = 'vi:'.$_SERVER['HTTP_VIA'];

        if(!empty($_SERVER['HTTP_PROXY_USER']))
            $addrs[] = 'pus:'.$_SERVER['HTTP_PROXY_USER'];

        if(!empty($_SERVER['HTTP_PROXY_CONNECTION']))
            $addrs[] = 'cn:'.$_SERVER['HTTP_PROXY_CONNECTION'];

        return join('|', $addrs);
}

 require_once('funcs/users/'.config('user_engine')'.php');
