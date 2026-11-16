<?php

function kategorije_sve()
{
    return db_svi_retci('SELECT id, naziv FROM kategorije ORDER BY naziv');
}
