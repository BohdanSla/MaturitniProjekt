let logout = setTimeout(function() {
    window.location.href = "timeout.php";
},15 * 60000);

document.addEventListener("click",resetTimer)
document.addEventListener("contextmenu",resetTimer)
document.addEventListener("keypress",resetTimer)
document.addEventListener("mousemove",resetTimer)

document.addEventListener("click", reset);
document.addEventListener("keypress", reset);
document.addEventListener("contextmenu", reset);
document.addEventListener("mousemove", reset);

function resetTimer() {
    clearTimeout(logout);  
    logout = setTimeout(function() {
        window.location.href = "timeout.php";
    },15 * 60000);
}


function reset() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "resetTimer.php", true);
    xhr.send();
}
