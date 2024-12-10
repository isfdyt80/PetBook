$(document).ready(function () {
    
    $('#close_sesion').click(function () {
        document.cookie = "jwt=; Expires=Thu, 01 Jan 1970 00:00:01 GMT; path=/; domain=petbooklocal";
        window.location.href = 'http://petbooklocal/sign_in.html';
    })
    
});