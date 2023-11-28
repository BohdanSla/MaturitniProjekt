let sprava = document.querySelectorAll(".produkty form");

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
let tlacitkoPridatNovouVelikost = document.querySelector(".modal-tlacitka input[type=\"button\"]:last-child")


//když zmáčknu tlačítko na přidání velikostí, tak získám index zmáčknutého tlačítka, který využiji na přidání řádku velikosti k danému produktu
let pridatVelikostiTlacitka = document.querySelectorAll("#pridat");
let indexBarvy = 0;

for (let index = 0; index < pridatVelikostiTlacitka.length; index++) {
    pridatVelikostiTlacitka[index].addEventListener("click",function() {
        modalniPozadi.style.display = "flex"
        indexBarvy = parseInt(pridatVelikostiTlacitka[index].getAttribute("data-barva"))
    })
}

tlacitkoPridatNovouVelikost.addEventListener("click",function() {
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
})

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