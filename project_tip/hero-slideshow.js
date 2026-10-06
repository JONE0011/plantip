(() => {
  const bg = document.getElementById("heroBg");
  const dots = document.getElementById("heroDots");
  if (!bg || !dots) return;
  const slides = [
    "images/places/doi-hua-mot.jpg",
    "images/places/thi-lo-su-2.jpg",
    "images/places/mae-moei.jpg",
    "images/places/pha-charoen.jpg",
    "images/places/thararak.jpg",
    "images/places/doi-thule.jpg",
    "images/places/lan-rao-ma.jpg",
    "images/places/doi-phawo.jpg"
  ];
  let index = 0;
  let timer;
  slides.forEach(src => { const img = new Image(); img.src = src; });
  slides.forEach((src, i) => {
    const dot = document.createElement("button");
    dot.type = "button";
    dot.className = i === 0 ? "hero-dot active" : "hero-dot";
    dot.onclick = () => show(i, true);
    dots.appendChild(dot);
  });
  const buttons = Array.from(dots.children);
  function setImage(src) {
    bg.style.backgroundImage = "linear-gradient(90deg,rgba(7,22,16,.82),rgba(7,22,16,.28) 62%,rgba(7,22,16,.1)),linear-gradient(0deg,rgba(5,16,11,.78),transparent 55%),url(\\\"" + src + "\\\")";
  }
  function show(next, manual) {
    index = (next + slides.length) % slides.length;
    bg.classList.add("is-changing");
    setTimeout(() => { setImage(slides[index]); bg.classList.remove("is-changing"); }, 350);
    buttons.forEach((b, n) => b.classList.toggle("active", n === index));
    if (manual) { clearInterval(timer); timer = setInterval(() => show(index + 1, false), 4000); }
  }
  setImage(slides[0]);
  timer = setInterval(() => show(index + 1, false), 4000);
})();
