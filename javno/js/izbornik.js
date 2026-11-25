// Mobilni izbornik: ispod 1024px CSS skriva izbornik i račun iza hamburger gumba.
// Skrivanje vrijedi samo uz klasu zaglavlje--js koju dodaje ova skripta, pa bez JS-a izbornik ostaje vidljiv.
(() => {
  const zaglavlje = document.querySelector(".zaglavlje");
  const gumb = document.querySelector(".zaglavlje__gumb");
  const natpis = document.querySelector(".zaglavlje__natpis");

  if (!zaglavlje || !gumb || !natpis) {
    return;
  }

  zaglavlje.classList.add("zaglavlje--js");

  // Klasa zaglavlje--otvoreno prikazuje izbornik i u CSS-u mijenja ikonu (izbornik ili zatvori).
  // Skriveni natpis daje gumbu naziv.
  const postavi = (otvoreno) => {
    zaglavlje.classList.toggle("zaglavlje--otvoreno", otvoreno);
    natpis.textContent = otvoreno ? "Zatvori" : "Izbornik";
  };

  gumb.addEventListener("click", () => {
    postavi(!zaglavlje.classList.contains("zaglavlje--otvoreno"));
  });

  // Tipka Escape zatvara otvoreni izbornik i vraća fokus na gumb
  document.addEventListener("keydown", (dogadaj) => {
    if (dogadaj.key === "Escape" && zaglavlje.classList.contains("zaglavlje--otvoreno")) {
      postavi(false);
      gumb.focus();
    }
  });
})();
