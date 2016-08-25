<?php

$searchData = file_get_contents('http://localhost:9200/time-spent/_search?q=*&pretty');

echo $searchData;