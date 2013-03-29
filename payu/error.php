<?php

// powrót z PayU w wyniku błędu

include(dirname(__FILE__) . '/../../config/config.inc.php');
include(dirname(__FILE__) . '/../../header.php');
include(dirname(__FILE__) . '/payu.php');

$errors = array(
    // lista blędów systemu PayU
    '100' => 'brak parametru pos_id',
    '101' => 'brak parametru session_id',
    '102' => 'brak parametru ts',
    '103' => 'brak parametru sig',
    '104' => 'brak parametru desc',
    '105' => 'brak parametru client_ip',
    '106' => 'brak parametru first_name',
    '107' => 'brak parametru last_name',
    '108' => 'brak parametru street',
    '109' => 'brak parametru city',
    '110' => 'brak parametru post_code',
    '111' => 'brak parametru amount',
    '112' => 'błędny numer konta bankowego',
    '113' => 'brak parametru email',
    '114' => 'brak numeru telefonu',
    '200' => 'inny chwilowy błąd',
    '201' => 'inny chwilowy błąd bazy danych',
    '202' => 'pos o podanym identyfikatorze jest zablokowany',
    '203' => 'niedozwolona wartość pay_type dla danego pos_id',
    '204' => 'podana metoda płatności (wartość pay_type) jest chwilowo zablokowana dla danego pos_id, np.&nbsp;przerwa konserwacyjna bramki płatniczej',
    '205' => 'kwota transakcji mniejsza od wartości minimalnej',
    '206' => 'kwota transakcji większa od wartości maksymalnej',
    '207' => 'przekroczona wartość wszystkich transakcji dla jednego klienta w&nbsp;ostatnim przedziale czasowym',
    '208' => 'pos działa w&nbsp;wariancie ExpressPayment lecz nie nastąpiła aktywacja tego wariantu współpracy (czekamy na zgodę działu obsługi klienta)',
    '209' => 'błędny numer pos_id lub pos_auth_key',
    '211' => 'nieprawidłowa waluta transakcji',
    '500' => 'transakcja nie istnieje',
    '501' => 'brak autoryzacji dla danej transakcji',
    '502' => 'transakcja rozpoczęta wcześniej',
    '503' => 'autoryzacja do transakcji była już przeprowadzana',
    '504' => 'transakcja anulowana wcześniej',
    '505' => 'transakcja przekazana do odbioru wcześniej',
    '506' => 'transakcja już odebrana',
    '507' => 'błąd podczas zwrotu środków do klienta',
    '508' => 'klient zrezygnował z&nbsp;płatności',
    '599' => 'błędny stan transakcji, np.&nbsp;nie można uznać transakcji kilka razy lub inny, prosimy o&nbsp;kontakt',
    '999' => 'inny błąd krytyczny — prosimy o&nbsp;kontakt'
);

global $smarty;

$smarty->assign('payu_error', (isset($errors[$_GET['error']]) ?
    $errors[$_GET['error']] :
    'nierozpoznany błąd'
));

// wyświetlenie błędu PayU
$smarty->display(dirname(__FILE__) . '/error.tpl');

include_once(dirname(__FILE__) . '/../../footer.php');

