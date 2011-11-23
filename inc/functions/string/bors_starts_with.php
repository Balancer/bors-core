<?php

// Смотри тесты в benchmarks/starts_end_with.php
function bors_starts_with($haystack, $needle, $case=true)
{
   if($case)
       return strpos($haystack, $needle, 0) === 0;

   return stripos($haystack, $needle, 0) === 0;
}
