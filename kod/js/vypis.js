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

let ikonyStrisky = document.querySelectorAll(".nazev-filtru img");
let hodnotyFiltru = document.querySelectorAll(".hodnoty-filtru");

for (let index = 0; index < ikonyStrisky.length; index++) {
    ikonyStrisky[index].addEventListener("click",function() {
        if (hodnotyFiltru[index].style.display == "none") {
            ikonyStrisky[index].style.transform = "rotate(0deg)";
            hodnotyFiltru[index].style.display = "block"
        } else {
            ikonyStrisky[index].style.transform = "rotate(-90deg)";
            hodnotyFiltru[index].style.display = "none"
        }
    })
}


let vstupyCen = document.querySelectorAll("input[type=\"number\"]")

vstupyCen.forEach(e => {
    e.addEventListener("keydown",function(event){
        //key vrací zmáčknutou klávesu
        if(event.key == "-" || event.key == "." || event.key == "," ) {
            event.preventDefault();
        }

    })
})

