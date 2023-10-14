let nadpisy = document.querySelector("main").querySelectorAll("h2");
nadpisy = Array.from(nadpisy).map(e => e.offsetWidth);
let plochy = document.querySelector("main").querySelectorAll(".bili-prostor");

for (let index = 0; index < plochy.length; index++) {
    let sirka = nadpisy[index] + 80;
    plochy[index].style.width = sirka + "px";
}
