
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

let vsechnyObrazkyProduktu = document.querySelectorAll(".obrazky-produktu option")
let hlavniObrazek = document.createElement("img")

let src = Array.from(vsechnyObrazkyProduktu).filter(el => {
    return el.value.includes("main")
},vsechnyObrazkyProduktu)

hlavniObrazek.setAttribute("data-barva",src[0].getAttribute("data-barva"))
hlavniObrazek.src = src[0].value;

document.querySelector(".hlavni-obrazek").appendChild(hlavniObrazek);

let barvyProduktu = []

document.querySelectorAll("form datalist option").forEach((el) => {
    if(!barvyProduktu.includes(el.getAttribute("data-barva"))) {
        barvyProduktu.push(el.getAttribute("data-barva"))
    }
})

let barvaHlavnihoObrazku = barvyProduktu[barvyProduktu.indexOf(hlavniObrazek.getAttribute("data-barva"))];

barvyProduktu.splice(barvyProduktu.indexOf(barvaHlavnihoObrazku),1)
barvyProduktu.unshift(barvaHlavnihoObrazku)

barvyProduktu.forEach(el => {
    let option = document.createElement("option")
    option.value = el
    option.innerHTML = el
    document.querySelector("form select").appendChild(option)
});



let barva = document.querySelector("form select[name=barva]")
barva.addEventListener("change",vypisVelikosti)

function vypisVelikosti() {
    let velikosti = document.querySelector("form select[name=velikost]")
    velikosti.replaceChildren("");
    
    let velikostiBarvy = document.querySelectorAll("form datalist option[data-barva=\"" + barva.value +"\"]")
    
    velikostiBarvy.forEach(el => {
        let option = document.createElement("option")
        option.value = el.getAttribute("data-velikost")
        option.innerHTML = el.getAttribute("data-velikost")
        velikosti.appendChild(option)
    })
}


function vypisObrazky() {
    let obrazkyBarvyProduktu = document.querySelectorAll(".obrazky-produktu option[data-barva=\"" + barva.value + "\"]")

    let obrazky = document.querySelector(".obrazky-produktu div")
    obrazky.replaceChildren("")

    obrazkyBarvyProduktu.forEach(el => {
        let img = document.createElement("img")
        img.src = el.value
        
        if (document.querySelector(".hlavni-obrazek img").getAttribute("src") == el.value) {
            img.style.border = "1px solid #11111155"
        }


        obrazky.appendChild(img)
    })
}

barva.addEventListener("change",vypisObrazky)

function zmenHlavniObrazek() {
    hlavniObrazek.setAttribute("data-barva",barva.value)

    let novyHlavniObrazek = false;
    
    vsechnyObrazkyProduktu.forEach(element => {
        
        if (element.getAttribute("data-barva") == hlavniObrazek.getAttribute("data-barva") && !novyHlavniObrazek) {
            hlavniObrazek.src = element.value
            novyHlavniObrazek = true
        }
    });
}

barva.addEventListener("change",zmenHlavniObrazek)

vypisVelikosti()
vypisObrazky()




