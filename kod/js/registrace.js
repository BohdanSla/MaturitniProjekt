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

let hesla = document.querySelectorAll("input[type=\"password\"]")
let telefonniCislo = document.querySelector("input[type=\"tel\"]")

let telefonniCisloRegex = /(^(\+[0-9]{1,4} )?([0-9]{3} ){2}[0-9]{3}$)|(^(\+[0-9]{1,4})?[0-9]{9}$)/
let hesloRegex = /^(?=.*\d)(?=.*[A-Z])(?=.*[a-z])(?=.*[!#$%&? "])[a-zA-Z0-9!#$%&? ]{8,20}$/    


telefonniCislo.addEventListener("input",function() {
    if (!telefonniCisloRegex.test(telefonniCislo.value)) {
        console.log("špatně zadané telefonní číslo");
    }
})

hesla[0].addEventListener("input",function(){
    if(!hesloRegex.test(hesla[0].value)) {
        console.log("heslo neopovídá daným požadavkům");
    }
})

hesla[1].addEventListener("input",function(){
    if (hesla[0].value !== hesla[1].value) {
        console.log("Heslo není stejné");
    }
})