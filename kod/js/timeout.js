let logout = setTimeout(function() {
    window.location.href = "timeout.php";
},15 * 60000);


document.addEventListener("keypress",function() {
    clearTimeout(logout);
    logout = setTimeout(function() {
        window.location.href = "timeout.php";
    },15 * 60000);
    i = 0;
    
});

document.addEventListener("click",function() {
    clearTimeout(logout);  
    logout = setTimeout(function() {
        window.location.href = "timeout.php";
    },15 * 60000);
    i = 0;
    
});

document.addEventListener("contextmenu",function() {
    clearTimeout(logout); 
    logout = setTimeout(function() {
        window.location.href = "timeout.php";
    },15 * 60000);
    i = 0;
    
});

document.addEventListener("mousemove",function() {
    clearTimeout(logout);
    logout = setTimeout(function() {
        window.location.href = "timeout.php";
    },15 * 60000);
    i = 0;
    
});


function reset() {
    let xhr = new XMLHttpRequest();
    xhr.open("GET", "resetTimer.php", true);
    xhr.send();
}

document.addEventListener("click", reset);
document.addEventListener("keypress", reset);
document.addEventListener("contextmenu", reset);
document.addEventListener("mousemove", reset);