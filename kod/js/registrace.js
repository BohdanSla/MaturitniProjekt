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

let hesla = document.querySelectorAll("input[type=\"password\"]")
let telefonniCislo = document.querySelector("input[type=\"tel\"]")
let email = document.querySelector("input[type=\"email\"]")
let pozadavkyHesla = document.querySelectorAll(".registrace li");

let telefonniCisloRegex = /(^(\+[0-9]{1,4} )?([0-9]{3} ){2}[0-9]{3}$)|(^(\+[0-9]{1,4})?[0-9]{9}$)/
let hesloRegex = /^(?=.*[A-Z])(?=.*[a-z])(?=.*\d)(?=.*[^A-Za-z\d\s])[A-Za-z\d\W\S]{8,}$/
let emailRegex = /^[a-zA-Z0-9]+@[a-zA-Z0-9]+\.[a-zA-Z]{2,4}$/ 

let odeslat = document.querySelector(".registrace form input[name=odeslat]")

hesla[0].addEventListener("input",function(){
    if (/.{8,}/.test(hesla[0].value)) {
        pozadavkyHesla[0].style.color = "green"
    } else {
        pozadavkyHesla[0].style.color = "red"
    }

    if (/[a-z]+/.test(hesla[0].value)) {
        pozadavkyHesla[1].style.color = "green"
    } else {
        pozadavkyHesla[1].style.color = "red"
    }

    if (/[A-Z]+/.test(hesla[0].value)) {
        pozadavkyHesla[2].style.color = "green"
    } else {
        pozadavkyHesla[2].style.color = "red"
    }

    if (/[0-9]+/.test(hesla[0].value)) {
        pozadavkyHesla[3].style.color = "green"
    } else {
        pozadavkyHesla[3].style.color = "red"
    }

    if (/[^A-Za-z0-9]+/.test(hesla[0].value)) {
        pozadavkyHesla[4].style.color = "green"
    } else {
        pozadavkyHesla[4].style.color = "red"
    }

    zkontrolujHesla()
})

hesla[1].addEventListener("input",zkontrolujHesla)

function zkontrolujHesla() {
    if(hesla[0].value !== "") {
        if(hesla[0].value === hesla[1].value) {
            pozadavkyHesla[5].style.color = "green"
        } else {
            pozadavkyHesla[5].style.color = "red"
        }
    }
}

odeslat.addEventListener("click",function(){
    let zprava = ""
    let validace = true
    
    inputy = document.querySelectorAll("main input:not(input[type=button])")
    for (const element of inputy) {
        if(element.value == "") {
            zprava += "máte nevyplněná pole"
            validace = false
            break
        }
    }

    if(validace) {
        if(!emailRegex.test(email.value)) {
            zprava += "špatně zadaný email, "
            validace = false
        }
        if (!telefonniCisloRegex.test(telefonniCislo.value)) {
            zprava += "špatně zadané telefonní číslo, "
            validace = false
        }
        if(!hesloRegex.test(hesla[0].value)) {
            zprava += "heslo neopovídá daným požadavkům, "
            validace = false
        }
        if (hesla[0].value !== hesla[1].value) {
            zprava += "Hesla nejsou stejná, "
            validace = false
        }
    }

    
    if (validace) {
        document.querySelector("main form").submit();
    } else {
        zprava = zprava.trimEnd()
        zprava = zprava.endsWith(",") ? zprava.slice(0,-1) : zprava
        document.querySelector(".registrace p").innerHTML = zprava
    }

})