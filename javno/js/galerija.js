// Povećanje fotografija u galeriji lokacije
// Klik na fotografiju otvara je preko stranice u <dialog>, a strelicama se lista kroz sve fotografije.
(() => {
  const dijalog = document.querySelector(".povecanje");
  const poveznice = Array.from(document.querySelectorAll("a.galerija__stavka"));

  if (
    !dijalog ||
    poveznice.length === 0 ||
    typeof dijalog.showModal !== "function"
  ) {
    return;
  }

  const slika = dijalog.querySelector(".povecanje__slika");
  const brojac = dijalog.querySelector(".povecanje__brojac");
  const prethodna = dijalog.querySelector(".povecanje__gumb--prethodna");
  const sljedeca = dijalog.querySelector(".povecanje__gumb--sljedeca");
  const zatvori = dijalog.querySelector(".povecanje__gumb--zatvori");
  let trenutna = 0;

  // Prikazuje fotografiju s rednim brojem broj. Nakon zadnje dolazi prva, a prije prve zadnja.
  const prikazi = (broj) => {
    trenutna = (broj + poveznice.length) % poveznice.length;
    const poveznica = poveznice[trenutna];

    slika.src = poveznica.href;
    slika.alt = poveznica.querySelector("img").alt;
    brojac.textContent = `${trenutna + 1} / ${poveznice.length}`;
  };

  poveznice.forEach((poveznica, broj) => {
    poveznica.addEventListener("click", (dogadaj) => {
      dogadaj.preventDefault();
      prikazi(broj);
      dijalog.showModal();
    });
  });

  prethodna?.addEventListener("click", () => prikazi(trenutna - 1));
  sljedeca?.addEventListener("click", () => prikazi(trenutna + 1));
  zatvori.addEventListener("click", () => dijalog.close());

  // Strelice na tipkovnici listaju fotografije (Esc zatvara dijalog sam)
  dijalog.addEventListener("keydown", (dogadaj) => {
    if (poveznice.length < 2) {
      return;
    }

    if (dogadaj.key === "ArrowLeft") {
      prikazi(trenutna - 1);
    } else if (dogadaj.key === "ArrowRight") {
      prikazi(trenutna + 1);
    }
  });

  // Klik na prazan dio dijaloga (izvan fotografije i gumba) zatvara ga
  dijalog.addEventListener("click", (dogadaj) => {
    if (dogadaj.target === dijalog) {
      dijalog.close();
    }
  });
})();
