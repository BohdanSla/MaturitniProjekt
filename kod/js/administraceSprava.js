let vstupyCen = document.querySelectorAll("input[type=\"number\"]")

vstupyCen.forEach(e => {
    e.addEventListener("keydown",function(event){
        //key vrací zmáčknutou klávesu
        if(event.key == "-" || event.key == "." || event.key == "," ) {
            event.preventDefault();
        }

    })
})

let modalniPozadi = document.querySelector(".modal-pozadi")
let modalniOkno = modalniPozadi.querySelector(".modal")
let tlacitkoZavritModalniOkno = document.querySelector(".modal-tlacitka input[type=\"button\"]:first-child")


function pridatNovouVelikost() {
    modalniPozadi.style.display = "none"
    
    let radek = document.createElement("tr")
    
    let velikostSloupec = document.createElement("td")
    velikostSloupec.innerHTML = "Velikost " + document.querySelector(".modal #velikost").value
    radek.appendChild(velikostSloupec)

    let skladSloupec = document.createElement("td");
    skladSloupec.innerHTML = "<div>Skladem " + document.querySelector(".modal #pocet").value +  "ks + přidat: <input type=\"number\" min=\"0\" name=\"skladem\" value=\"\">ks</div>"
    radek.appendChild(skladSloupec)

    let odstranitSloupec = document.createElement("td")
    odstranitSloupec.innerHTML = "<input type=\"button\" name=\"odstranitVelikost\" value=\"odstranit\">"
    radek.appendChild(odstranitSloupec)
    
    //nemam to přes css jeden selektor, protože to nefungovalo když jsem tam dal jakykoliv nth-child
    let barvy = document.querySelectorAll(".barva")
    barvy[indexBarvy].querySelector("tbody").appendChild(radek)
}

tlacitkoZavritModalniOkno.addEventListener("click",function() {
    modalniPozadi.style.display = "none"
})



let menu = document.querySelectorAll("nav ul li")

let main = document.querySelector("main").children
main = Array.from(main).slice(2)

for (let index = 0; index < 4; index++) {
    menu[index].addEventListener("click", function() {
        
        menu[index].style.background = "#DDDDDD"
        main[index].style.display = "flex"
        
        //dojmenovat
        let n = Array.from(main).filter(element => {
            return element !== main[index]
        })
        n.forEach(element => {
            element.style.display = "none"
        })
        
        let nevybrany = Array.from(menu).filter(element => {
            return element !== menu[index]
        })
        nevybrany.forEach(element => {
            element.style.background = "unset"
        })
        
    })
}
let indexBarvy = 0;

let hledatProduktSearch = document.querySelector("#hledatProduktSearch")
let hledatProdukt = document.querySelector("#hledat-produkt")

let produkty = document.querySelectorAll("#produkty option")
let mnozstviProduktu = document.querySelectorAll("#mnozstviProduktu option")
let materialyProduktu = document.querySelectorAll("#daneMaterialyProduktu option")
let obrazkyProduktu = document.querySelectorAll("#daneObrazkyProduktu option")

let pocetBarevProduktu = {}
mnozstviProduktu.forEach(el =>{
    if (pocetBarevProduktu[el.value] == undefined) {
        pocetBarevProduktu[el.value] = 0
    }
    
})
console.log(pocetBarevProduktu);

let nazevProduktu = document.querySelector("#nazevProduktu")
let popisProduktu = document.querySelector("#popisProduktu")
let cenaProduktu = document.querySelector("#cenaProduktu")
let kategorieProduktu = document.querySelector("#kategorieProduktu")
let podkategorieProduktu = document.querySelector("#podkategorieProduktu")
let znackaProduktu = document.querySelector("#znackaProduktu")
let sportProduktu = document.querySelector("#sportProduktu")
let hlavniObrazekProduktu = document.querySelector("#hlavniObrazekProduktu")

let vsechnyBarvyProduktu = document.querySelectorAll("#barvaProduktu")
let vsechnyMaterialyProduktu = document.querySelectorAll("#materialProduktu")
let vsechnyProcentaMaterialu = document.querySelectorAll("#procentoMaterialu")

hledatProdukt.addEventListener("click",function() {
    let indexHledanehoProduktu = 0;
    for (let index = 0; index < produkty.length; index++) {
        if(produkty[index].value == hledatProduktSearch.value) {
            indexHledanehoProduktu = index
            break;
        }
    }
    nazevProduktu.value = produkty[indexHledanehoProduktu].value
    popisProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-popis")
    cenaProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-cena")
    cenaProduktuVeSleve.value = produkty[indexHledanehoProduktu].getAttribute("data-cenaVeSleve")


    kategorieProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-kategorie")
    podkategorieProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-podkategorie")


    znackaProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-znacka")
    sportProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-sport")

    hlavniObrazekProduktu.src = produkty[indexHledanehoProduktu].getAttribute("data-hlavniObrazek")
    hlavniObrazekProduktu.removeAttribute("hidden")

    for (let index = 0; index < vsechnyBarvyProduktu.length; index++) {
        vsechnyBarvyProduktu[index].checked = false
    }

    for (let index = 0; index < vsechnyBarvyProduktu.length; index++) {
        for (let i = 0; i < mnozstviProduktu.length; i++) {
            if(mnozstviProduktu[i].value == produkty[indexHledanehoProduktu].value) {
                if (vsechnyBarvyProduktu[index].value == mnozstviProduktu[i].getAttribute("data-barva")) {
                    vsechnyBarvyProduktu[index].checked = true
                }
            }
        }
    }

    vsechnyMaterialyProduktu.forEach(el => {
        el.checked = false
    })
    vsechnyProcentaMaterialu.forEach(el => {
        el.value = ""
    })

    for (let index = 0; index < vsechnyMaterialyProduktu.length; index++) {
        for (let i = 0; i < materialyProduktu.length; i++) {
            if(materialyProduktu[i].value == produkty[indexHledanehoProduktu].value) {
                if (vsechnyMaterialyProduktu[index].value == materialyProduktu[i].getAttribute("data-material")) {
                    vsechnyMaterialyProduktu[index].checked = true
                    vsechnyProcentaMaterialu[index].value = materialyProduktu[i].getAttribute("data-procento")
                }
            }
        }
    }

    let obrazkyMnozstvi = document.querySelector(".obrazky-mnozstvi")

    obrazkyMnozstvi.innerHTML = "<h1>Barvy</h1>"

    let barva = document.createElement("div")
    barva.classList = "barva"

    let velikostiDiv = document.createElement("div")
    velikostiDiv.classList = "velikosti"

    let pouziteBarvy = []

    for (let index = 0; index < mnozstviProduktu.length; index++) {
        if (mnozstviProduktu[index].value == produkty[indexHledanehoProduktu].value) {
            if (!pouziteBarvy.includes(mnozstviProduktu[index].getAttribute("data-barva"))) {
                pouziteBarvy.push(mnozstviProduktu[index].getAttribute("data-barva"))

    
                let h3 = document.createElement("h3")
                h3.innerHTML = mnozstviProduktu[index].getAttribute("data-barva")
    
                let bVelikost = document.createElement("b")
                bVelikost.innerHTML = "Velikosti a množství:"
    
                let tableVelikost = document.createElement("table")
                let tbodyVelikost = document.createElement("tbody")
    
    
                for (let i = 0; i < mnozstviProduktu.length; i++) {
                    if (mnozstviProduktu[i].value == produkty[indexHledanehoProduktu].value) {
                        if (mnozstviProduktu[i].getAttribute("data-barva") == mnozstviProduktu[index].getAttribute("data-barva")) {
    
                            let tr = document.createElement("tr")
    
                            let tdVelikost =document.createElement("td")
                            tdVelikost.innerHTML = "Velikost: " +  mnozstviProduktu[i].getAttribute("data-velikost")
    
                            let tdPocet = document.createElement("td")
                            tdPocet.innerHTML = "skladem " + mnozstviProduktu[i].getAttribute("data-pocet") + 'ks + přidat <input type="number" min="0" name="skladem">ks'
    
                            let tdOdstranit = document.createElement("td")
                            tdOdstranit.innerHTML ='<input type="button" name="odstranitVelikost" value="odstranit">'
    
                            tr.appendChild(tdVelikost)
                            tr.appendChild(tdPocet)
                            tr.appendChild(tdOdstranit)
                            tbodyVelikost.appendChild(tr)
                        }
                    }
                }
                tableVelikost.appendChild(tbodyVelikost)
                
                let novaVelikostButton = document.createElement("input")
                novaVelikostButton.type = 'button'
                novaVelikostButton.value = 'Přidat novou velikost'
                novaVelikostButton.id = "pridat"
                novaVelikostButton.setAttribute("data-barva",pouziteBarvy.length)
                novaVelikostButton.addEventListener("click",pridatNovouVelikost)
                
                velikostiDiv.appendChild(h3)
                velikostiDiv.appendChild(bVelikost)
                velikostiDiv.appendChild(tableVelikost)
                velikostiDiv.appendChild(novaVelikostButton)
                velikostiDiv.appendChild(document.createElement("br"))

            }
        }
        
    }
    barva.appendChild(velikostiDiv)

    let obrazkyDiv = document.createElement("div")
    obrazkyDiv.classList = "obrazky"
    
    let bObrazky = document.createElement("b")
    bObrazky.innerHTML = "Obrázky:"
    obrazkyDiv.appendChild(bObrazky)

    tableObrazek = document.createElement("table")
    tbodyObrazek = document.createElement("tbody")

    for (let i = 0; i < obrazkyProduktu.length; i++) {
        if (obrazkyProduktu[i].value == produkty[indexHledanehoProduktu].value) {
                let tr = document.createElement("tr")

                let img = document.createElement("img")
                img.src = obrazkyProduktu[i].getAttribute("data-obrazek")

                let tdObrazek =document.createElement("td")
                tdObrazek.appendChild(img)

                let tdOdstranit = document.createElement("td")
                tdOdstranit.innerHTML ='<input type="button" value="odebrat obrázek">'

                tr.appendChild(tdObrazek)
                tr.appendChild(tdOdstranit)
                tbodyObrazek.appendChild(tr)
        }
    }

    tableObrazek.appendChild(tbodyObrazek)

    obrazkyDiv.appendChild(tableObrazek)

    let novyObrazekButton = document.createElement("input")
    novyObrazekButton.type = 'file'

    obrazkyDiv.appendChild(novyObrazekButton)

    barva.appendChild(obrazkyDiv)

    obrazkyMnozstvi.appendChild(barva)

        //když zmáčknu tlačítko na přidání velikostí, tak získám index zmáčknutého tlačítka, který využiji na přidání řádku velikosti k danému produktu
    let pridatVelikostiTlacitka = document.querySelectorAll("#pridat");

    for (let index = 0; index < pridatVelikostiTlacitka.length; index++) {
        pridatVelikostiTlacitka[index].addEventListener("click",function() {
            modalniPozadi.style.display = "flex"
            indexBarvy = parseInt(pridatVelikostiTlacitka[index].getAttribute("data-barva"))
        })
    }
})