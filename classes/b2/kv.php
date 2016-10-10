<?php

$key_value_engine = \B2\Cfg::get('host.key_value_engine', 'bors_kv_sqlite');

eval("class b2_kv extends $key_value_engine { };");

