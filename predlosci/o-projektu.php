<?php
$naslov = 'O projektu';
$opis = 'Što je Mjestopis, odakle su podaci i od čega je napravljen';
$aktivno = 'o-projektu';
?>
<article class="o-projektu">
    <section class="o-projektu__odjeljak">
        <h2 class="o-projektu__naslov">Što je Mjestopis</h2>
        <p>Podaci o tome što posjetiti u Hrvatskoj raspršeni su po stranicama turističkih zajednica, društvenim mrežama i kartama. Mjestopis ih okuplja na jednom mjestu: lokacije, događanja, vijesti i vremensku prognozu za cijelu Hrvatsku.</p>
    </section>

    <section class="o-projektu__odjeljak">
        <h2 class="o-projektu__naslov">Što se ovdje može</h2>
        <ul class="o-projektu__popis">
            <li>pregledati lokacije (znamenitosti, parkove, plaže, staze, vidikovce…) po županiji, gradu ili općini i vrsti;</li>
            <li>pronaći nadolazeća događanja i pročitati vijesti iz gradova i općina;</li>
            <li>vidjeti prognozu za lokaciju i za događanja na otvorenom;</li>
            <li>nakon <a href="index.php?stranica=registracija">registracije</a> ocjenjivati lokacije i slagati vlastiti plan putovanja po danima.</li>
        </ul>
    </section>

    <section class="o-projektu__odjeljak">
        <h2 class="o-projektu__naslov">Odakle su podaci</h2>
        <ul class="o-projektu__popis">
            <li>županije, gradovi, općine: popis Državnog zavoda za statistiku;</li>
            <li>vremenska prognoza: <a href="https://open-meteo.com">Open-Meteo</a> (CC BY 4.0);</li>
            <li>ikone vremena: <a href="https://lucide.dev">Lucide</a> (ISC licenca);</li>
            <li>lokacije, događanja i vijesti upisuju urednici Mjestopisa.</li>
        </ul>
    </section>

    <section class="o-projektu__odjeljak">
        <h2 class="o-projektu__naslov">Tehnologije</h2>
        <ul class="o-projektu__popis">
            <li>PHP i MySQL;</li>
            <li>HTML, SCSS i JavaScript;</li>
            <li>uređivač vijesti <a href="https://quilljs.com">Quill</a> i čišćenje HTML-a sa Symfony HtmlSanitizerom.</li>
        </ul>
    </section>

    <section class="o-projektu__odjeljak">
        <h2 class="o-projektu__naslov">Projekt</h2>
        <p>Mjestopis je izrađen kao projekt za kolegij Napredne tehnike projektiranja web servisa.</p>
    </section>
</article>
