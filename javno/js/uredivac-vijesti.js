// Quill WYSIWYG editor za sadržaj vijesti
// Tekstualno polje se skriva, a pri slanju obrasca u njega se upisuje HTML iz editora.
// Ako se Quill ne učita onda se radi s običnim tekstualnim poljem.
(() => {
  const polje = document.getElementById("sadrzaj");

  const { Quill } = window;

  if (!polje || !Quill) {
    return;
  }

  const obrazac = polje.form;
  const pomoc = document.getElementById("sadrzaj-pomoc");

  // Editor se stavlja odmah ispod tekstualnog polja, koje se skriva
  const uredivac = document.createElement("div");
  uredivac.className = "uredivac";
  polje.after(uredivac);
  polje.hidden = true;

  // Uputa o praznom retku vrijedi samo za obično tekstualno polje
  if (pomoc) {
    pomoc.hidden = true;
  }

  const quill = new Quill(uredivac, {
    theme: "snow",
    // Samo oblikovanja koja dopušta ocisti_html() na serveru
    formats: ["header", "bold", "italic", "link", "list", "blockquote"],
    modules: {
      toolbar: [
        [{ header: 2 }, { header: 3 }],
        ["bold", "italic", "link"],
        [{ list: "bullet" }, { list: "ordered" }],
        ["blockquote"],
        ["clean"],
      ],
    },
  });

  // Postojeći sadržaj (pri uređivanju ili nakon greške) prelazi iz polja u editor
  quill.setContents(quill.clipboard.convert({ html: polje.value }), "silent");

  const naziviGumba = {
    'ql-header[value="2"]': "Podnaslov",
    'ql-header[value="3"]': "Manji podnaslov",
    "ql-bold": "Podebljano",
    "ql-italic": "Kurziv",
    "ql-link": "Poveznica",
    'ql-list[value="bullet"]': "Popis s točkama",
    'ql-list[value="ordered"]': "Numerirani popis",
    "ql-blockquote": "Citat",
    "ql-clean": "Ukloni oblikovanje",
  };
  const alatnaTraka = quill.getModule("toolbar").container;

  for (const [klasa, naziv] of Object.entries(naziviGumba)) {
    const gumb = alatnaTraka.querySelector(`button.${klasa}`);

    if (gumb) {
      gumb.setAttribute("aria-label", naziv);
      gumb.title = naziv;
    }
  }

  alatnaTraka.setAttribute("aria-label", "Oblikovanje teksta");

  // Područje za pisanje dobiva naziv i opis greške skrivenog polja
  quill.root.setAttribute("role", "textbox");
  quill.root.setAttribute("aria-multiline", "true");
  quill.root.setAttribute("aria-label", "Sadržaj");

  for (const atribut of ["aria-invalid", "aria-describedby"]) {
    if (polje.hasAttribute(atribut)) {
      quill.root.setAttribute(atribut, polje.getAttribute(atribut));
    }
  }

  // Kod slanja obrasca HTML iz editora se upisuje u skriveno polje.
  // Prazan editor šalje prazno polje.
  // Quill razmake piše kao &nbsp; pa se vraćaju u obične razmake.
  obrazac.addEventListener("submit", () => {
    polje.value =
      quill.getText().trim() === ""
        ? ""
        : quill.getSemanticHTML().replaceAll("&nbsp;", " ");
  });
})();
