//jquery
$(document).ready(function() {
    $("button").click(function() {
        $("#Table").load("includes/loadPHP.php")
    })
})

//javascript
function darkMode()
{
    let checkbox = document.getElementById("darkmode");

    if(checkbox.checked)
    {
        console.log("dark mode activated");
        document.body.style.backgroundColor = '#121212';
        document.body.style.color = '#857F72';
    } 
    else
    {
        console.log("dark mode de-activated");
        document.body.style.backgroundColor = 'beige';
        document.body.style.color = 'olive';
    } 
}
