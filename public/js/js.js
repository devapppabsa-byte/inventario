
//recibo todos los parametros 
const previa = (inputImg, imgTag, divPreview) => {
    const  imagen = document.getElementById(inputImg),
           imagen_tag = document.getElementById(imgTag),
           preview = document.getElementById(divPreview);
           

    if(imagen && imagen_tag && preview){
        {
            imagen.addEventListener('change', function(e){
    
                    // Creamos el objeto de la clase FileReader
                    let reader = new FileReader();
                    // Leemos el archivo subido y se lo pasamos a nuestro fileReader
                    reader.readAsDataURL(e.target.files[0]);
                    // Le decimos que cuando este listo ejecute el código interno
                    reader.onload = function(){
                    imagen_tag.src = reader.result;
    
                    preview.innerHTML = '';
                    //Cambio el color de el contenedor para indicar que la imagen ya esta cargada
                 
                    preview.append(imagen_tag);
                    
                };
            })   
        }   
    }
}

const numeroInputs = document.getElementsByClassName('imagen');

    for(i=0 ; i<numeroInputs.length; i++){
      const imagen_perfil = 'imagen'+i,
      img_tag       = 'img_tag'+i,
      preview       =  'previa'+i;  
              
      previa(imagen_perfil, img_tag, preview)
              
    }



    




// //CODIGO QUE MUESTRA LA VISTA PREVIA DE LAS IMAGENES
// const contador = document.getElementsByClassName('imagen') 


// for(i=0 ; i<contador.length; i++){


// document.getElementById('imagen'+i).onchange = function(e){

// //se crea el objeto fileReader que nos leera nuestra imagen
// let lector = new FileReader();

// // Se lee el archivo y se lo pasa al FileReader
// lector.readAsDataURL(e.target.files[0])

// //Cuando el FileReader este listo va a ejecutar este codigo
// lector.onload = function(){
//     let previa = document.getElementById('previa'+i),
//             imagen = document.createElement('img')
            
//             imagen.src = lector.result
//             imagen.className = 'img-fluid w-200 mt-4 shadow p-3'

//             previa.innerHTML = ''
//             previa.append(imagen)
// }

// }



// }



{


// document.addEventListener('contextmenu', function(e){
//   e.preventDefault();

//   const menu = document.getElementById('menuContextual');

//   //establecemos la posicion delm menu contextual
//   menu.style.left = e.pageX + 'px';
//   menu.style.top = e.pageY + 'px';

//   //mostrar el menu
//   menu.style.display = 'block';

// })



// document.addEventListener('click', function(){
//   //que oculta el menu al hacer click en otra parte
//   const menu = document.getElementById('menuContextual')
//   menu.style.display = 'none';
// })




}