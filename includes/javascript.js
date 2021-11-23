function darkMode()
{
    let checkbox = document.getElementById("darkmode");

    if(checkbox.checked)
    {
        console.log("checked");
        document.body.style.backgroundColor = '#121212';
        document.body.style.color = '#857F72';
    } 
    else
    {
        console.log("not checked");
        document.body.style.backgroundColor = 'beige';
        document.body.style.color = 'olive';
    } 
        
}