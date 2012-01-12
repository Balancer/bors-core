<?php

function date_yesterday($time = 0) { return strtotime(date('Y-m-d', $time ? $time : time()).' -1 day'); }
