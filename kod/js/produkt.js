
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



src = Array.from(vsechnyObrazkyProduktu).filter(el => {
    return el.value.includes("main")
},vsechnyObrazkyProduktu)

if(src.length == 0) {
    src = Array.from(vsechnyObrazkyProduktu)
}

hlavniObrazek.setAttribute("data-barva",src[0].getAttribute("data-barva"))
hlavniObrazek.src = src[0].value;

document.querySelector(".hlavni-obrazek").insertBefore(hlavniObrazek,document.querySelector("img[alt=sipka_ikona]:last-of-type"));

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
// barva.addEventListener("change",vypisSkladem)


// let velikost = document.querySelector("form select[name=velikost]");
// velikost.addEventListener("change",vypisSkladem)




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

let ostatniObrazky = document.querySelectorAll("img[alt=ostatni]")

function vypisObrazky() {
    let obrazkyBarvyProduktu = document.querySelectorAll(".obrazky-produktu option[data-barva=\"" + barva.value + "\"]")

    obrazkyBarvyProduktu = Array.from(obrazkyBarvyProduktu).sort((a, b) => {
        
        if (a.getAttribute("value").includes("main") && !b.getAttribute("value").includes("main")) {
            return -1; 
        } else if (!a.getAttribute("value").includes("main") && b.getAttribute("value").includes("main")) {
            return 1; 
        } else {
            return 0; 
        }
    });

    let obrazky = document.querySelector(".obrazky-produktu div")
    obrazky.replaceChildren("")
    
    obrazkyBarvyProduktu.forEach((el,index) => {
        let img = document.createElement("img")
        img.src = el.value
        img.alt = "ostatni"
        if (document.querySelector(".hlavni-obrazek img:nth-child(2)").getAttribute("src") == el.value) {
            img.style.border = "1px solid #111111BB"
            indexHlavnihoObrazku = index
        }
        
        obrazky.appendChild(img);
    })

    ostatniObrazky = document.querySelectorAll("img[alt=ostatni]")

    if(ostatniObrazky.length == 1) {
        sipky.forEach(element => {
            element.style.display = "none"
        });
    }
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

let zprava = document.querySelector("main .galerie-info p")

function odstranZpravu() {
    zprava.style.opacity = 0

    setTimeout(()=> {
        zprava.parentNode.removeChild(zprava)
    },1500)
}

if(zprava != null) {
    setTimeout(odstranZpravu,1500)
}


let sipky = document.querySelectorAll('img[alt=sipka_ikona]')
let indexHlavnihoObrazku = 0;

for (let index = 0; index < ostatniObrazky.length; index++) {
    if (ostatniObrazky[index].src == hlavniObrazek.src) {
        indexHlavnihoObrazku = index;
        break;
    }
}

vypisVelikosti()
vypisObrazky()



sipky[0].addEventListener("click",function() {
    
    ostatniObrazky[indexHlavnihoObrazku].style.border = "1px solid white"
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
    ostatniObrazky[indexHlavnihoObrazku].style.border = "1px solid white"
    if (indexHlavnihoObrazku + 1 < ostatniObrazky.length) {
        indexHlavnihoObrazku++;
    } else {
        indexHlavnihoObrazku = 0
    }
    hlavniObrazek.src = ostatniObrazky[indexHlavnihoObrazku].src;
    ostatniObrazky[indexHlavnihoObrazku].style.border = "1px solid #111111BB"
})




let b = document.querySelector("main .recenze form b")
let p = document.querySelector(".recenze > p")

let upravuje = false

if(b != null) {
    let hvezdy = document.querySelector(".recenze div:first-of-type .jmeno-hodnoceni b")
    let recenze = document.querySelector(".recenze div:first-of-type p");
    let formRecenze = document.querySelector("main .recenze form")
    if(formRecenze != null) {
        formRecenze.id = "recenze"
    }
    
    let odstranitRecenzi = document.createElement("input")
    odstranitRecenzi.type = "submit"
    odstranitRecenzi.name = "odstranit"
    odstranitRecenzi.value = "Odstranit recenzi"
    
    let upravaHvezdy = document.createElement("input");
    upravaHvezdy.type = "number"
    upravaHvezdy.name = "hvezdy"
    upravaHvezdy.max = 5
    upravaHvezdy.min = 0
    upravaHvezdy.setAttribute("form","recenze")
    
    let upravaRecenze = document.createElement("textarea")
    upravaRecenze.name = "recenze"
    upravaRecenze.setAttribute("form","recenze")
    
    let potvrdit = document.createElement("input");
    potvrdit.type = "submit"
    potvrdit.name = "upravit"
    potvrdit.value = "změnit"
    potvrdit.style.outline = "none"


    b.addEventListener("click",function() {
    if(!upravuje) {
        hvezdy.textContent = hvezdy.textContent.slice(0,1)
        
        upravaHvezdy.value = hvezdy.textContent
        hvezdy.replaceWith(upravaHvezdy)
    
        upravaRecenze.value = recenze.textContent
        recenze.replaceWith(upravaRecenze)
    
        b.textContent = "Zahodit změny"
    
        formRecenze.appendChild(odstranitRecenzi)
        
        formRecenze.appendChild(potvrdit)
        
        upravuje = true
    } else {
        formRecenze.removeChild(odstranitRecenzi)
    
        formRecenze.removeChild(potvrdit)
        
        b.textContent = "Upravit recenzi"
    
        upravaHvezdy.replaceWith(hvezdy)
        hvezdy.textContent = hvezdy.textContent + "/5"
    
        upravaRecenze.replaceWith(recenze)
        upravuje = false
    }
    
    })
}

if(p != null) {
    let h2 = document.createElement("h2")
    h2.textContent = "Recenze:"
    let h4 = document.createElement("h4")
    h4.textContent = "Počet hvězd (0-5):"

    let div = document.createElement("div")
    div.style.display = "flex"
    div.style.alignItems = "center"

    let napsatRecenzi = document.createElement("input")
    napsatRecenzi.type = "submit"
    napsatRecenzi.value = "Napsat recenzi"
    napsatRecenzi.name = "napsat"

    let form = document.createElement("form")
    form.method = "post"
    form.style.width = "unset"
    form.style.display = "flex"
    form.style.flexDirection = "column"
    form.style.alignItems = "flex-start"
    let input = document.createElement("input");
    input.type = "number"
    input.name = "hvezdy"
    input.max = 5
    input.min = 0
    input.style.marginLeft = "1rem"
    let textarea = document.createElement("textarea")
    textarea.name = "recenze"

    p.addEventListener("click",function() {
        if(!upravuje) {
            form.appendChild(h2)
            form.appendChild(textarea)
            form.appendChild(div)
            div.appendChild(h4)
            div.appendChild(input)
            div.appendChild(napsatRecenzi)
    
            document.querySelector(".recenze >p:first-of-type").after(form)

            p.textContent = "Zrušit"

            upravuje = true
        } else {
            upravuje = false

            p.textContent = "Napsat recenzi"

            document.querySelector(".recenze").removeChild(document.querySelector(".recenze form"))
        }
    })
}


let galerie= document.querySelector(".galerie");
let parametry= document.querySelector(".info-pridat");

let vsechnyObrazky = document.querySelector(".obrazky-produktu div");

let sirka = this.innerWidth;

zmenStyly()

window.addEventListener("resize",zmenStyly)

function zmenStyly() {

    if (galerie.offsetTop < parametry.offsetTop) {
        // do something;
        if(sirka < this.innerWidth) {
            parametry.style.width = "unset"
            parametry.style.marginTop = "0"
            vsechnyObrazky.style.display = "flex"
        } else {
            parametry.style.marginTop = "1rem"
            parametry.style.width = "100%"
            document.querySelector(".dekorace-cara").style.margin = "1rem 0"
            vsechnyObrazky.style.display = "none"
        }
    }
}