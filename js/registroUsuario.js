// Pattern para validar contraseñas
let patternContraseña = /^(?=.*[0-9])(?=.*[a-z])(?=.*[A-Z])(?=.*[@#$%^&+=.\-_*])([a-zA-Z0-9@#$%^&+=*.\-_]){8,}$/
// test
let contraseña = 'Furioso98$'
console.log(patternContraseña.test(contraseña))
// 
// De cualquier forma se van a usar librerias de Jquery para validar