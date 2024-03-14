let vstupyCen = document.querySelectorAll("input[type=\"number\"]")

vstupyCen.forEach(e => {
    e.addEventListener("keydown",function(event){
        //key vrací zmáčknutou klávesu
        if(event.key == "-" || event.key == "." || event.key == "," ) {
            event.preventDefault();
        }

    })
})


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


let expirace = document.querySelector("input[type=date]")

if(expirace != null ) {
    let datum = new Date();
    datum.setDate(datum.getDate() + 1)
    
    expirace.setAttribute("min",datum.toISOString().split("T")[0]);
}

let modalniOkno = document.querySelector(".modal > div")


let novyProduktMaterialButton = document.querySelector("#pridatMaterial")

novyProduktMaterialButton.addEventListener("click",function() {
    let material = document.createElement("input")
    material.setAttribute("list","materialy")
    material.type = "text"

    let procento = document.createElement("input")
    procento.type = "number"
    procento.min = "1"
    procento.max = "100"

    let materialNazev = document.createElement("p")
    materialNazev.textContent = "Materiál"

    let procentoNazev = document.createElement("p")
    procentoNazev.textContent = "procento materiálu"

    let potvrdit = document.createElement("span")
    potvrdit.textContent = "Potvrdit"
    potvrdit.style.marginTop = "1rem"
    potvrdit.addEventListener("click",function() {
        let tr = document.createElement("tr")

        let td1 = document.createElement("td")
        material.readOnly = true
        material.name = "material[]"
        td1.appendChild(material)
        tr.appendChild(td1)

        let td2 = document.createElement("td")
        procento.readOnly = true
        procento.name = "procento[]"
        td2.appendChild(procento)
        tr.appendChild(td2)

        let odstranitMaterialButton = document.createElement("input")
        odstranitMaterialButton.type = "button"
        odstranitMaterialButton.value = "odstranit materiál"
        odstranitMaterialButton.addEventListener("click",function() {
            odstranitMaterialButton.parentNode.parentNode.parentNode.removeChild(odstranitMaterialButton.parentNode.parentNode)
        })

        let td3 = document.createElement("td")
        td3.appendChild(odstranitMaterialButton)
        tr.appendChild(td3)

        document.querySelector("table:nth-of-type(2) tbody").insertBefore(tr,document.querySelector("table:nth-of-type(2) tbody tr:last-of-type"))

        zavriModalniOkno()
    }) 
    
    modalniOkno.appendChild(materialNazev)
    modalniOkno.appendChild(material)
    modalniOkno.appendChild(procentoNazev)
    modalniOkno.appendChild(procento)
    modalniOkno.appendChild(potvrdit)
    zobrazModalniOkno()
})




let novyProduktBarvaButton = document.querySelector("#pridatBarvu")

novyProduktBarvaButton.addEventListener("click",function() {
    let barva = document.createElement("input")
    barva.setAttribute("list","barvy")
    barva.type = "text"

    let barvaNazev = document.createElement("p")
    barvaNazev.textContent = "Barva"

    let potvrdit = document.createElement("span")
    potvrdit.textContent = "Potvrdit"
    potvrdit.style.marginTop = "1rem"
    potvrdit.addEventListener("click",function() {
        let tr = document.createElement("tr")

        let td1 = document.createElement("td")
        barva.readOnly = true
        barva.name = "barva[]"
        td1.appendChild(barva)
        tr.appendChild(td1)

        let td2 = document.createElement("td")
        
        let odstranitBarvuButton = document.createElement("input")
        odstranitBarvuButton.type = "button"
        odstranitBarvuButton.value = "odstranit barvu"
        odstranitBarvuButton.addEventListener("click",function() {
            odstranitBarvuButton.parentNode.parentNode.parentNode.removeChild(odstranitBarvuButton.parentNode.parentNode)

            document.querySelectorAll("." + barva.value).forEach((el) => {
                el.parentNode.removeChild(el)
            })
        })
        td2.appendChild(odstranitBarvuButton)
        tr.appendChild(td2)

        let obrazky = document.createElement("input")
        obrazky.type = "file"
        obrazky.multiple = true
        obrazky.accept = "jpg,png,jpeg"
        obrazky.name = barva.value + "Obrazky[]"

        let td3 = document.querySelector("td");
        td3.innerHTML = ""
        td3.appendChild(obrazky)
        tr.appendChild(td3)

        let trVelikosti = document.createElement("tr")
        trVelikosti.classList = barva.value
        let tdNovaVelikost = document.createElement("td")
        tdNovaVelikost.colSpan = 3

        let novaVelikostButton = document.createElement("input")
        novaVelikostButton.type = "button"
        novaVelikostButton.value = "Přidat velikost k barvě"

        novaVelikostButton.addEventListener("click",() => {
            zobrazVelikosti(barva.value)
        })

        tdNovaVelikost.appendChild(novaVelikostButton)
        trVelikosti.appendChild(tdNovaVelikost)

        
        document.querySelector("table:nth-of-type(3) tbody").insertBefore(tr,document.querySelector("table:nth-of-type(3) tbody tr:last-of-type"))

        tr.after(trVelikosti)

        zavriModalniOkno()
    }) 
    

    modalniOkno.appendChild(barvaNazev)
    modalniOkno.appendChild(barva)
    modalniOkno.appendChild(potvrdit)
    zobrazModalniOkno()
})

function zobrazVelikosti(barva) {
    let velikostNazev = document.createElement("p")
    velikostNazev.textContent = "velikost"

    let velikost = document.createElement("input")
    velikost.type = "text"
    velikost.setAttribute("list","velikosti");

    let potvrdit = document.createElement("span")
    potvrdit.addEventListener("click",function(){
        let tr = document.createElement("tr")
        tr.classList = barva

        let td1 = document.createElement("td")
        velikost.readOnly = true
        velikost.name = barva + "Velikost[]"
        td1.appendChild(velikost)
        tr.appendChild(td1)

        let td2 = document.createElement("td")
        let odstranitVelikostButton = document.createElement("input")
        odstranitVelikostButton.type = "button"
        odstranitVelikostButton.value = "Odstranit velikost"
        odstranitVelikostButton.addEventListener("click",function() {
            odstranitVelikostButton.parentNode.parentNode.parentNode.removeChild(odstranitVelikostButton.parentNode.parentNode)
        })

        td2.appendChild(odstranitVelikostButton)
        tr.appendChild(td2)

        document.querySelector("table:nth-of-type(3) tbody").insertBefore(tr,document.querySelector("." + barva))


        zavriModalniOkno()
    })
    potvrdit.textContent = "Potvrdit"
    potvrdit.style.marginTop = "1rem"


    modalniOkno.appendChild(velikostNazev)
    modalniOkno.appendChild(velikost)
    modalniOkno.appendChild(potvrdit)

    zobrazModalniOkno()
}






let zavritModalniOknoButton = document.querySelector('.modal > div span');
zavritModalniOknoButton.addEventListener("click",zavriModalniOkno)

function zobrazModalniOkno() {
    document.querySelector(".modal").style.display = "block";
}

function zavriModalniOkno() {
    modalniOkno.innerHTML = ""
    modalniOkno.appendChild(zavritModalniOknoButton)
    document.querySelector(".modal").style.display = "none";
}