let nadpisy = document.querySelector("main").querySelectorAll("h2");
nadpisy = Array.from(nadpisy).map(e => e.offsetWidth);
let plochy = document.querySelector("main").querySelectorAll(".bili-prostor");

for (let index = 0; index < plochy.length; index++) {
    let sirka = nadpisy[index] + 80;
    plochy[index].style.width = sirka + "px";
}

let ikonaMenu = document.querySelector("header nav img:first-child");
ikonaMenu.addEventListener("click",otevriPostraniMenu);

let postrannniMenu = document.querySelector(".kategorie");
let a = document.querySelector("aside");

function otevriPostraniMenu() {
    if (getComputedStyle(postrannniMenu).getPropertyValue("left") == "-240px") {
        postrannniMenu.style.left = "0";
        a.style.visibility = "visible";
    } else  {
        postrannniMenu.style.left = "-240px";
        a.style.visibility = "hidden";
    }
}



let levaSipka = document.querySelector(".sipky-dopruceny-produkt img");
let pravaSipka = document.querySelector(".sipky-dopruceny-produkt img:last-child");