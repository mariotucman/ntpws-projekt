<?php

// GET /v1/kategorije
function api_kategorije_popis()
{
    api_odgovor(kategorije_sve());
}
