<?php
  function quote_oanda_currency($code, $base = DEFAULT_CURRENCY) {
	if ($code=='RUR') $code = 'RUB';

	if ($base=='RUR') $base = 'RUB';

    $page = file('http://www.oanda.com/convert/fxdaily?value=1&redirected=1&exch=' . $code .  '&format=CSV&dest=Get+Table&sel_list=' . $base);

    $match = array();

    preg_match('/(.+),(\w{3}),([0-9.]+),([0-9.]+)/i', implode('', $page), $match);

    if (sizeof($match) > 0) {
      return $match[3];
    } else {
      return false;
    }
  }

  function quote_xe_currency($to, $from = DEFAULT_CURRENCY) {
    $page = file('http://www.xe.net/ucc/convert.cgi?Amount=1&From=' . $from . '&To=' . $to);

    $match = array();

    preg_match('/[0-9.]+\s*' . $from . '\s*=\s*([0-9.]+)\s*' . $to . '/', implode('', $page), $match);

    if (sizeof($match) > 0) {
      return $match[1];
    } else {
      return false;
    }
  }

  function quote_rbc_currency($to, $from = DEFAULT_CURRENCY) {
	if ($from=='RUR') $from = 'BASE';
	if ($to=='RUR') $to = 'BASE';

    $page = file('http://conv.rbc.ru/convert.shtml?mode=calc&tid_from=' . $from . '&tid_to=' . $to . '&summa=1');

    $match = array();

    preg_match('/����\:<\/TD>\s+<TD><B>([^<]+)<\/B><\/TD><\/TR>/i', implode('', $page), $match);

    if (sizeof($match) > 0) {
      return $match[1];
    } else {
      return false;
    }
  }
?>