// Filtar županija -> grad/općina
// u izborniku gradova ostaju samo gradovi odabrane županije
(() => {
  const zupanija = document.getElementById("zupanija");
  const grad = document.getElementById("grad_opcina");

  if (!zupanija || !grad) {
    return;
  }

  // Sve grupe (jedna po županiji) se pamte, a u izborniku ostaju samo one koje odgovaraju odabiru
  const grupe = Array.from(grad.querySelectorAll("optgroup"));

  function prikaziGradove() {
    const odabrani = grad.value;

    for (const grupa of grupe) {
      if (zupanija.value === "" || grupa.dataset.zupanija === zupanija.value) {
        grad.appendChild(grupa);
      } else if (grupa.parentNode === grad) {
        grad.removeChild(grupa);
      }
    }

    // Ako odabrani grad nije u novoj županiji, odabir se vraća na "Svi gradovi i općine"
    grad.value = odabrani;
    if (grad.value !== odabrani) {
      grad.value = "";
    }
  }

  // Odabir grada bez odabrane županije postavlja njegovu županiju
  function postaviZupaniju() {
    const opcija = grad.options[grad.selectedIndex];

    if (
      opcija &&
      opcija.parentNode.tagName === "OPTGROUP" &&
      zupanija.value === ""
    ) {
      zupanija.value = opcija.parentNode.dataset.zupanija;
      prikaziGradove();
    }
  }

  zupanija.addEventListener("change", prikaziGradove);
  grad.addEventListener("change", postaviZupaniju);
  prikaziGradove();
})();
