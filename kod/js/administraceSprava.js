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
let tlacitkoVytvoritVelikost = document.querySelector(".modal-tlacitka input[type=\"button\"]:last-child")

tlacitkoVytvoritVelikost.addEventListener("click",pridatNovouVelikost)


function pridatNovouVelikost() {
    let barvy = document.querySelectorAll(".barva")

    modalniPozadi.style.display = "none"
    
    let radek = document.createElement("tr")
    
    let velikostSloupec = document.createElement("td")
    velikostSloupec.innerHTML = "Velikost: " + document.querySelector(".modal #velikost").value + "<input name=\"" +  barvy[indexBarvy - 1].getAttribute("data-barva").replace(/ /g,"-") + "Velikost[]\" value=\"" + document.querySelector(".modal #velikost").value +"\" hidden>"
    radek.appendChild(velikostSloupec)

    let skladSloupec = document.createElement("td");
    skladSloupec.innerHTML = "<div>Skladem <input type=\"number\" min=\"0\" name=\"" + barvy[indexBarvy - 1].getAttribute("data-barva").replace(/ /g,"-") +  "Pocet[]\" value=\"" + document.querySelector(".modal #pocet").value  + "\">ks</div>"
    radek.appendChild(skladSloupec)

    let odstranitSloupec = document.createElement("td")
    odstranitSloupec.innerHTML = "<input type=\"button\" name=\"odstranitVelikost\" value=\"odstranit\">"
    radek.appendChild(odstranitSloupec)
    
    //nemam to přes css jeden selektor, protože to nefungovalo když jsem tam dal jakykoliv nth-child
    barvy[indexBarvy - 1].querySelector("tbody").appendChild(radek)
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
let resetovatHodnoty = document.querySelector(".tlacitka input:first-child")

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

let nazevProduktu = document.querySelector("#nazevProduktu")
let popisProduktu = document.querySelector("#popisProduktu")
let cenaProduktu = document.querySelector("#cenaProduktu")
let cenaProduktuVeSleve = document.querySelector("#cenaProduktuVeSleve")
let kategorieProduktu = document.querySelector("#kategorieProduktu")
let podkategorieProduktu = document.querySelector("#podkategorieProduktu")
let znackaProduktu = document.querySelector("#znackaProduktu")
let sportProduktu = document.querySelector("#sportProduktu")
let hlavniObrazekProduktu = document.querySelector("#hlavniObrazekProduktu")

let vsechnyBarvyProduktu = document.querySelectorAll(".vytvorit #barvaProduktu")
let vsechnyMaterialyProduktu = document.querySelectorAll(".vytvorit #materialProduktu")
let vsechnyProcentaMaterialu = document.querySelectorAll(".vytvorit #procentoMaterialu")

vsechnyBarvyProduktu.forEach(el => {
    el.addEventListener("change",()=> {
        if (el.checked) {
            let barvaDiv = document.createElement("div")
            barvaDiv.classList = "barva"
            barvaDiv.setAttribute("data-barva",el.value)
            document.querySelector(".obrazky-mnozstvi").appendChild(barvaDiv)
            
            let bBarva = document.createElement("b")    
            bBarva.innerHTML = el.value
            barvaDiv.appendChild(bBarva)
            
            let velikostiDiv = document.createElement("div")
            velikostiDiv.classList = "velikosti"
            barvaDiv.appendChild(velikostiDiv)
            
            let pVelikosti = document.createElement("p")
            pVelikosti.innerHTML = "Velikosti a množství:"
            velikostiDiv.appendChild(pVelikosti)

            let tableVelikosti = document.createElement("table")
            velikostiDiv.appendChild(tableVelikosti)
            
            let tbodyVelikosti = document.createElement("tbody")
            tableVelikosti.appendChild(tbodyVelikosti)
            
            let novaVelikostButton = document.createElement("input")
            novaVelikostButton.type = 'button'
            novaVelikostButton.value = 'Přidat novou velikost'
            novaVelikostButton.id = "pridat"
            novaVelikostButton.setAttribute("data-barva",el.value)
            velikostiDiv.appendChild(novaVelikostButton)
            velikostiDiv.appendChild(document.createElement("br"))
            
            let obrazkyDiv = document.createElement("div")
            obrazkyDiv.classList = "obrazky"
            barvaDiv.appendChild(obrazkyDiv)
            
            let pObrazky = document.createElement("p")
            pObrazky.innerHTML = "Obrázky:"
            obrazkyDiv.appendChild(pObrazky)
            
            let tableObrazky = document.createElement("table")
            obrazkyDiv.appendChild(tableObrazky)
            
            let tbodyObrazky= document.createElement("tbody")
            tbodyObrazky.setAttribute("data-barva",el.value)
            tableObrazky.appendChild(tbodyObrazky)
            
            let labelNovyhoObrazkuButton = document.createElement("label")
            labelNovyhoObrazkuButton.innerHTML = "Přidat nový obrázky:"
            labelNovyhoObrazkuButton.setAttribute("for","novyObrazek")
            obrazkyDiv.appendChild(labelNovyhoObrazkuButton)
            
            let novyObrazekButton = document.createElement("input")
            novyObrazekButton.type = 'file'
            novyObrazekButton.accept = 'image/*'
            novyObrazekButton.name = el.value.replace(/ /g,"-") + "[]"
            novyObrazekButton.multiple = "multiple"
            obrazkyDiv.appendChild(novyObrazekButton)
            
            let pridatVelikostiTlacitka = document.querySelectorAll("#pridat");
            
            for (let index = 0; index < pridatVelikostiTlacitka.length; index++) {
                pridatVelikostiTlacitka[index].addEventListener("click",function() {
                    modalniPozadi.style.display = "flex"
                    indexBarvy = parseInt(pridatVelikostiTlacitka[index].getAttribute("data-barva"))
                })
            }

        } else {
            let zrusenaBarva = '.barva[data-barva="' + el.value + '"]'
            document.querySelector(zrusenaBarva).parentElement.removeChild(document.querySelector(zrusenaBarva));
        }
    })
})





let indexHledanehoProduktu = 0;

hledatProdukt.addEventListener("click",function() {
    for (let index = 0; index < produkty.length; index++) {
        if(produkty[index].value == hledatProduktSearch.value) {
            indexHledanehoProduktu = index
            break;
        }
    }
    vyhledejProdukt()
})

resetovatHodnoty.addEventListener("click",vyhledejProdukt)

function vyhledejProdukt() {
    document.querySelector("#puvodniNazev").value = produkty[indexHledanehoProduktu].value
    nazevProduktu.value = produkty[indexHledanehoProduktu].value
    popisProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-popis")
    cenaProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-cena")
    cenaProduktuVeSleve.value = produkty[indexHledanehoProduktu].getAttribute("data-cenaVeSleve")


    kategorieProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-kategorie")
    podkategorieProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-podkategorie")


    znackaProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-znacka")
    sportProduktu.value = produkty[indexHledanehoProduktu].getAttribute("data-sport")

    let puvodniObrazek = document.querySelector("#puvodniHlavniObrazek")
    console.log(puvodniObrazek);
    puvodniObrazek.setAttribute("value",produkty[indexHledanehoProduktu].getAttribute("data-hlavniObrazek"))

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
    
    let obrazkyMnozstviDiv = document.querySelector(".obrazky-mnozstvi")
    
    obrazkyMnozstviDiv.innerHTML = "<h1>Barvy</h1>"
    
    let pouziteBarvyVelikosti = []
    let indexVelikosti = 0
    
    for (let index = 0; index < produkty[indexHledanehoProduktu].getAttribute("data-pocetBarev"); index++) {
        for (let i = 0; i < mnozstviProduktu.length; i++) {
            if (produkty[indexHledanehoProduktu].value == mnozstviProduktu[i].value) {
                if (!pouziteBarvyVelikosti.includes(mnozstviProduktu[i].getAttribute("data-barva"))) {
                    pouziteBarvyVelikosti.push(mnozstviProduktu[i].getAttribute("data-barva"))
                    
                    let barvaDiv = document.createElement("div")
                    barvaDiv.classList = "barva"
                    barvaDiv.setAttribute("data-barva",mnozstviProduktu[i].getAttribute("data-barva"))

                    let bBarva = document.createElement("b")    
                    bBarva.innerHTML = mnozstviProduktu[i].getAttribute("data-barva") 
                    barvaDiv.appendChild(bBarva)
                    
                    let velikostiDiv = document.createElement("div")
                    velikostiDiv.classList = "velikosti"
                    barvaDiv.appendChild(velikostiDiv)
                    
                    let pVelikosti = document.createElement("p")
                    pVelikosti.innerHTML = "Velikosti a množství:"
                    velikostiDiv.appendChild(pVelikosti)
                    
                    let tableVelikosti = document.createElement("table")
                    velikostiDiv.appendChild(tableVelikosti)
                    
                    let tbodyVelikosti = document.createElement("tbody")
                    tableVelikosti.appendChild(tbodyVelikosti)
                    
                    let novaVelikostButton = document.createElement("input")
                    novaVelikostButton.type = 'button'
                    novaVelikostButton.value = 'Přidat novou velikost'
                    novaVelikostButton.id = "pridat"
                    novaVelikostButton.setAttribute("data-barva",pouziteBarvyVelikosti.length)
                    velikostiDiv.appendChild(novaVelikostButton)
                    velikostiDiv.appendChild(document.createElement("br"))

                    
                    for (let y = 0; y < mnozstviProduktu.length; y++) {
                        if (produkty[indexHledanehoProduktu].value == mnozstviProduktu[y].value) {
                            if (mnozstviProduktu[i].getAttribute("data-barva") == mnozstviProduktu[y].getAttribute("data-barva")) {
                                let tr = document.createElement("tr")
                                tr.setAttribute("data-velikost",indexVelikosti)
                                tbodyVelikosti.appendChild(tr)
                                
                                let tdVelikosti = document.createElement("td")
                                tdVelikosti.innerHTML = "Velikost: " + mnozstviProduktu[y].getAttribute("data-velikost")+ "<input name=\"" +  mnozstviProduktu[y].getAttribute("data-barva").replace(/ /g,"-") + "Velikost[]\" value=\"" + mnozstviProduktu[y].getAttribute("data-velikost") +"\" hidden>"  
                                tr.appendChild(tdVelikosti)
                                
                                let tdMnozstvi = document.createElement("td")
                                tdMnozstvi.innerHTML = 'Skladem: <input type="number" min="0" name="'+ mnozstviProduktu[y].getAttribute("data-barva").replace(/ /g,"-") + 'Pocet[]" value="'+ mnozstviProduktu[y].getAttribute("data-pocet") +'">ks'
                                tr.appendChild(tdMnozstvi)
                                
                                let tdOdstranit = document.createElement("td")
                                tdOdstranit.innerHTML = '<input type="button" name="odstranitVelikost" value="odstranit" id="odstranitVelikost" data-velikost="' + indexVelikosti++ +'">'
                                tr.appendChild(tdOdstranit)
                                
                            }
                        }
                    }

                    let obrazkyDiv = document.createElement("div")
                    obrazkyDiv.classList = "obrazky"
                    barvaDiv.appendChild(obrazkyDiv)
                    
                    let pObrazky = document.createElement("p")
                    pObrazky.innerHTML = "Obrázky:"
                    obrazkyDiv.appendChild(pObrazky)
                    
                    let tableObrazky = document.createElement("table")
                    obrazkyDiv.appendChild(tableObrazky)
                    
                    let tbodyObrazky= document.createElement("tbody")
                    tbodyObrazky.setAttribute("data-barva",mnozstviProduktu[i].getAttribute("data-barva"))
                    tableObrazky.appendChild(tbodyObrazky)
                    
                    let labelNovyhoObrazkuButton = document.createElement("label")
                    labelNovyhoObrazkuButton.innerHTML = "Přidat nový obrázky:"
                    labelNovyhoObrazkuButton.setAttribute("for","novyObrazek")
                    obrazkyDiv.appendChild(labelNovyhoObrazkuButton)

                    let novyObrazekButton = document.createElement("input")
                    novyObrazekButton.type = 'file'
                    novyObrazekButton.accept = 'image/*'
                    novyObrazekButton.name = mnozstviProduktu[i].getAttribute("data-barva").replace(/ /g,"-") + "[]"
                    novyObrazekButton.multiple = "multiple"
                    obrazkyDiv.appendChild(novyObrazekButton)


                    indexObrazku = 0;

                    for (let y = 0; y < obrazkyProduktu.length; y++) {
                        if (produkty[indexHledanehoProduktu].value == obrazkyProduktu[y].value) {
                            if (mnozstviProduktu[i].getAttribute("data-barva") == obrazkyProduktu[y].getAttribute("data-barva")) {
                                
                                let tr = document.createElement("tr")
                                tr.setAttribute("data-obrazek",index)
                                tbodyObrazky.appendChild(tr)
                                
                                let inputObrazku = document.createElement("input")
                                inputObrazku.value = obrazkyProduktu[y].getAttribute('data-obrazek')
                                inputObrazku.name = obrazkyProduktu[y].getAttribute("data-barva").replace(/ /g,"-") + "[]"
                                inputObrazku.style.display = "none"
                                
                                let img = document.createElement("img")
                                img.src = obrazkyProduktu[y].getAttribute("data-obrazek")

                                let tdObrazku = document.createElement("td")
                                tdObrazku.appendChild(img)
                                tdObrazku.appendChild(inputObrazku)
                                tr.appendChild(tdObrazku)
                                
                                let tdOdstranit = document.createElement("td")
                                tdOdstranit.innerHTML = '<input type="button" value="odebrat obrázek" id="odstranitObrazek" data-obrazek="' + index++ +'">'
                                tr.appendChild(tdOdstranit)
                            }
                        }
                    }
                    obrazkyMnozstviDiv.appendChild(barvaDiv)
                }
            }
        }
    }

    //když zmáčknu tlačítko na přidání velikostí, tak získám index zmáčknutého tlačítka, který využiji na přidání řádku velikosti k danému produktu
    let pridatVelikostiTlacitka = document.querySelectorAll("#pridat");

    for (let index = 0; index < pridatVelikostiTlacitka.length; index++) {
        pridatVelikostiTlacitka[index].addEventListener("click",function() {
            modalniPozadi.style.display = "flex"
            indexBarvy = parseInt(pridatVelikostiTlacitka[index].getAttribute("data-barva"))
        })
    }

    let odstranitVelikost = document.querySelectorAll("#odstranitVelikost")

    odstranitVelikost.forEach(element => {
        element.addEventListener("click",function() {
            document.querySelector('tr[data-velikost="' + element.getAttribute("data-velikost") + '"]').parentElement.removeChild(document.querySelector('tr[data-velikost="' + element.getAttribute("data-velikost") + '"]'))
        })
    })

    let odstranitObrazek = document.querySelectorAll("#odstranitObrazek")

    odstranitObrazek.forEach(element => {
        element.addEventListener("click",function() {
            document.querySelector('tr[data-obrazek="' + element.getAttribute("data-obrazek") + '"]').parentElement.removeChild(document.querySelector('tr[data-obrazek="' + element.getAttribute("data-obrazek") + '"]'))
        })
    });

    vstupyCen = document.querySelectorAll(".obrazky-mnozstvi input[type=\"number\"]")

    vstupyCen.forEach(e => {
        e.addEventListener("keydown",function(event){
            //key vrací zmáčknutou klávesu
            if(event.key == "-" || event.key == "." || event.key == "," ) {
                event.preventDefault();
            }

        })
    })
}