
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
barva.addEventListener("change",zmenHlavniObrazek)
barva.addEventListener("change",vypisObrazky)
barva.addEventListener("change",vypisSkladem)


let velikost = document.querySelector("form select[name=velikost]");
velikost.addEventListener("change",vypisSkladem)




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

let ostatniObrazky= document.querySelectorAll("img[alt=ostatni]")

function vypisObrazky() {
    let obrazkyBarvyProduktu = document.querySelectorAll(".obrazky-produktu option[data-barva=\"" + barva.value + "\"]")
    
    let obrazky = document.querySelector(".obrazky-produktu div")
    obrazky.replaceChildren("")
    
    obrazkyBarvyProduktu.forEach((el,index) => {
        let img = document.createElement("img")
        img.src = el.value
        img.alt = "ostatni"
        
        if (document.querySelector(".hlavni-obrazek img").getAttribute("src") == el.value) {
            img.style.border = "1px solid #111111BB"
            indexHlavnihoObrazku = index
        }
        
        
        obrazky.appendChild(img);
    })

    ostatniObrazky = document.querySelectorAll("img[alt=ostatni]")
}


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

function vypisSkladem() {
    let skladem = document.querySelector("form datalist option[data-barva=\"" + barva.value +"\"][data-velikost=\"" + velikost.value + "\"]")

    let p = document.querySelector("main form p")

    if(skladem.getAttribute("data-skladem").toLowerCase().includes("není")) {
        p.style.color = "red"
    } else {
        p.style.color = "green"
    }

    p.innerHTML = skladem.getAttribute("data-skladem");
}

let sipky = document.querySelectorAll('img[alt=sipka_ikona]')
for (let index = 0; index < ostatniObrazky.length; index++) {
    if (ostatniObrazky[index].src == hlavniObrazek.src) {
        indexHlavnihoObrazku = index;
        let indexHlavnihoObrazku = 0;
        break;
    }
}

vypisVelikosti()
vypisObrazky()
vypisSkladem()



sipky[0].addEventListener("click",function() {
    
    ostatniObrazky[indexHlavnihoObrazku].style.border = "unset"
    if (indexHlavnihoObrazku > 0) {
        indexHlavnihoObrazku--;
    } else {
        indexHlavnihoObrazku = ostatniObrazky.length - 1
    }
    hlavniObrazek.src = ostatniObrazky[indexHlavnihoObrazku].src;
    hlavniObrazek.src = ostatniObrazky[indexHlavnihoObrazku].src;
    ostatniObrazky[indexHlavnihoObrazku].style.border = "1px solid #111111BB"
})

sipky[1].addEventListener("click",function(){
    ostatniObrazky[indexHlavnihoObrazku].style.border = "unset"
    if (indexHlavnihoObrazku + 1 < ostatniObrazky.length) {
        indexHlavnihoObrazku++;
    } else {
        indexHlavnihoObrazku = 0
    }
    hlavniObrazek.src = ostatniObrazky[indexHlavnihoObrazku].src;
    ostatniObrazky[indexHlavnihoObrazku].style.border = "1px solid #111111BB"
})


    
