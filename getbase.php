<?php

namespace getbasefutebol;

require_once "Integracao.php";

error_reporting(E_ERROR | E_PARSE);

function debug($value)
{
    echo "<pre>";
    print_r($value);
    echo "</pre>";
}

$url = urldecode($_GET['url']);
$resp = Validador::get_html($url);
//$url = "https://br.sportingbet.com/services/CouponTemplate.mvc/GetCoupon?couponAction=EVENTCLASSCOUPON&sportIds=102&marketTypeId=&eventId=&bookId=&eventClassId=190183&sportId=102&eventTimeGroup=ETG_NextFewHours_0_0";
echo $resp;
//echo $resp;

