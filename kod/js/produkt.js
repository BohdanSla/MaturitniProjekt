
let ikonaMenu = document.querySelector("header nav img:first-child");
ikonaMenu.addEventListener("click",otevriPostraniMenu);

let tmavaPlocha = document.querySelector("aside");
let postranniMenu = tmavaPlocha.querySelector("nav");

if (window.innerWidth > 650) {
    postranniMenu.style.left = "-240px";
} else {
    postranniMenu.style.top = "-320px";
}


window.addEventListener("resize",zmenPolohuPostrannihoMenu)

function zmenPolohuPostrannihoMenu() {
    console.log(this.innerWidth);
    if (this.innerWidth > 650) {
        if (tmavaPlocha.style.visibility == "visible") {
            postranniMenu.style.left = "0";
        } else {
            postranniMenu.style.top = "0";
            postranniMenu.style.left = "-240px";
        }
    } else {
        if (tmavaPlocha.style.visibility == "visible") {
            postranniMenu.style.top = "0";
        } else {
            postranniMenu.style.left = "0";
            postranniMenu.style.top = "-320px";
        }
    }
}


function otevriPostraniMenu() {
    if (window.innerWidth > "650") {
        if (getComputedStyle(postranniMenu).getPropertyValue("left") == "-240px") {
            postranniMenu.style.left = "0";
            tmavaPlocha.style.visibility = "visible";
        } else  {
            postranniMenu.style.left = "-240px";
            tmavaPlocha.style.visibility = "hidden";
        }
    } else {
        if (getComputedStyle(postranniMenu).getPropertyValue("top") == "0px") {
            postranniMenu.style.top = "-320px";
            tmavaPlocha.style.visibility = "hidden";
        } else  {
            postranniMenu.style.top = "0";
            tmavaPlocha.style.visibility = "visible";
        }
    }
}


let Vsechnyobrazky = document.querySelectorAll(".ostatni-obrazky option");

let hlavniObrazek = document.createElement("img")
let src = Array.from(Vsechnyobrazky).filter((el) => {
    return el.value.includes("main")
})
hlavniObrazek.src = src[0].value
document.querySelector(".galerie").insertBefore(hlavniObrazek,document.querySelector(".ostatni-obrazky"))

let barvyProduktu = []

obrazky.forEach((el)=> {
    if(!barvyProduktu.includes(el.getAttribute("data-barva"))) {
        if (el.value.includes("main")) {
            barvyProduktu.unshift(el.getAttribute("data-barva"))
        } else {
            barvyProduktu.push(el.getAttribute("data-barva"))
        }
    }
})

barvyProduktu.forEach((el) => {
    let barva = document.createElement("option")
    barva.value = el
    barva.innerHTML = el
    
    document.querySelector("form select").appendChild(barva)        
})

let obrazkyProduktu = document.querySelector(".obrazky-produktu div")

Vsechnyobrazky.filter((el) => {
    if (el.getAttribute("data-barva") == document.querySelector("form select").options[document.querySelector("form select").selectedIndex]) {
        
    }
})

