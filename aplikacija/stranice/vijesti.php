<?php

// Odabir županije i grada/općine
// Ako je odabran samo grad, županija se postavlja prema njemu
function odabrano_podrucje($zupanije)
{
    $zupanija_id = get_broj('zupanija');
    $grad_opcina_id = get_broj('grad_opcina');

    if (!in_array($zupanija_id, array_map('intval', array_column($zupanije, 'id')), true)) {
        $zupanija_id = 0;
    }

    if ($grad_opcina_id > 0) {
        $grad_opcina = grad_opcina_po_id($grad_opcina_id);

        if ($grad_opcina === null || ($zupanija_id > 0 && (int) $grad_opcina['zupanija_id'] !== $zupanija_id)) {
            $grad_opcina_id = 0;
        } else {
            $zupanija_id = (int) $grad_opcina['zupanija_id'];
        }
    }

    return [$zupanija_id, $grad_opcina_id];
}

function stranica_vijesti()
{
    $zupanije = zupanije_sve();
    [$zupanija_id, $grad_opcina_id] = odabrano_podrucje($zupanije);

    prikazi('vijesti', [
        'vijesti' => vijesti_javne($zupanija_id, $grad_opcina_id),
        'zupanije' => $zupanije,
        'gradovi_opcine' => gradovi_opcine_svi(),
        'odabrana_zupanija' => $zupanija_id,
        'odabrani_grad_opcina' => $grad_opcina_id,
    ]);
}
