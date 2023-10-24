let nadpisy = document.querySelector("main").querySelectorAll("h2");
nadpisy = Array.from(nadpisy).map(e => e.offsetWidth);
let plochy = document.querySelector("main").querySelectorAll(".bili-prostor");

for (let index = 0; index < plochy.length; index++) {
    let sirka = nadpisy[index] + 80;
    plochy[index].style.width = sirka + "px";
}

let ikonaMenu = document.querySelector("header nav img:first-child");
ikonaMenu.addEventListener("click",otevriPostraniMenu);

let postranniMenu = document.querySelector(".kategorie");
let tmavaPlocha = document.querySelector("aside");

function otevriPostraniMenu() {
    if (getComputedStyle(postranniMenu).getPropertyValue("left") == "-240px") {
        postranniMenu.style.left = "0";
        tmavaPlocha.style.visibility = "visible";
    } else  {
        postranniMenu.style.left = "-240px";
        tmavaPlocha.style.visibility = "hidden";
    }
}
let polozkaSlideru = 0

let doporucenyProdukty = document.querySelectorAll(".doporuceny-produkt");
for (let index = 1; index < doporucenyProdukty.length; index++) {
    doporucenyProdukty[index].style.display = "none";
    
}

let levaSipka = document.querySelector(".sipky-doporuceny-produkt img");
let pravaSipka = document.querySelector(".sipky-doporuceny-produkt .sipka:last-child img");

pravaSipka.addEventListener("click",dalsiDoporucenyProdukt);
levaSipka.addEventListener("click",predchoziDoporucenyProdukt);

let polozkySlideru = document.querySelectorAll(".polozka");

function predchoziDoporucenyProdukt() {
    polozkySlideru[polozkaSlideru].style.background = "#FFFFFF"

    doporucenyProdukty[polozkaSlideru].style.display ="none"
    if (polozkaSlideru == 0) {
        polozkaSlideru = polozkySlideru.length - 1

        polozkySlideru[polozkaSlideru].style.background = "#1c1c1c"
    } else {

        polozkySlideru[--polozkaSlideru].style.background = "#1c1c1c"

    }
    doporucenyProdukty[polozkaSlideru].style.display ="flex"
    console.log(polozkaSlideru);
}


function dalsiDoporucenyProdukt() {
    polozkySlideru[polozkaSlideru].style.background = "#FFFFFF"
    
    doporucenyProdukty[polozkaSlideru].style.display ="none"
    if (polozkaSlideru == 3) {
        polozkaSlideru = 0
        
        polozkySlideru[polozkaSlideru].style.background = "#1c1c1c"
    } else {
        
        polozkySlideru[++polozkaSlideru].style.background = "#1c1c1c"
    }
    doporucenyProdukty[polozkaSlideru].style.display ="flex"
}