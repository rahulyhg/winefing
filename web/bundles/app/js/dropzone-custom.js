function base64ToFile(dataURI, origFile) {
  var byteString, mimestring;

  if(dataURI.split(',')[0].indexOf('base64') !== -1 ) {
    byteString = atob(dataURI.split(',')[1]);
  } else {
    byteString = decodeURI(dataURI.split(',')[1]);
  }

  mimestring = dataURI.split(',')[0].split(':')[1].split(';')[0];

  var content = new Array();
  for (var i = 0; i < byteString.length; i++) {
    content[i] = byteString.charCodeAt(i);
  }

  var newFile = new File(
      [new Uint8Array(content)], origFile.name, {type: mimestring}
  );


  // Copy props set by the dropzone in the original file

  var origProps = [
    "upload", "status", "previewElement", "previewTemplate", "accepted"
  ];

  $.each(origProps, function(i, p) {
    newFile[p] = origFile[p];
  });

  return newFile;
}
//*************************** DROPZONZ *******************************************************************************
var myDropzone = new Dropzone("div#my-dropzone-container", {
  maxFilesize: 5,
  thumbnailWidth: 120,
  acceptedFiles:"image/*",
  thumbnailHeight: null,
  maxFiles: size,
  url: uploadDirectory,
  autoQueue: false,
  dictDefaultMessage: directDefaultMessage,
    init: function() {
  var myDropzone = this;
  $.each(medias, function(key,value){
    var mockFile = { name: value.name, size: 2}; // here we get the file name and size as response
    myDropzone.options.addedfile.call(myDropzone, mockFile);

    //delete the progress bar
    mockFile.previewElement.removeChild(mockFile.previewElement.querySelector(".dz-progress"));
    myDropzone.options.thumbnail.call(myDropzone, mockFile, (upload+ value.name).toString());//uploadsfolder is the folder where you have all those uploaded files

    if(value.presentation) {
      mockFile.previewElement.querySelector("div.dz-image").setAttribute('style', 'border: solid 4px #f1c40f !important;');
      var span = mockFile.previewElement.querySelector("span#presentation");
      span.setAttribute('class', 'glyphicon glyphicon glyphicon-star');
      span.setAttribute('style', 'color:#f1c40f!important;');
    }

    //set different link
    setAddElement(mockFile, value);
  });

},
previewTemplate: "<div class=\"dz-preview dz-file-preview\">\n  " +
"<div class=\"dz-image\"><figure><img data-dz-thumbnail /></figure><figcaption>Proposition pour le thème : <br><em>Un goût de fraise</em> du jeu </figcaption>></div>\n  <div class=\"dz-details\">\n " +
"<div>"+
"<div><a href=\"#\"rel=\"gallery2\" class=\"fancybox img-hover-v1\"><span class=\"glyphicon glyphicon-zoom-in\" style=\"cursor: pointer!important;font-size: 30px\"></span></a></div>"+
"<div style=\"margin-bottom: 5px\"><a style=\"cursor: pointer!important;\" id=\"presentation\"><span id=\"presentation\" class=\"glyphicon glyphicon-star-empty\" style=\"cursor: pointer!important;\"></span>Couverture</a></div>" +
"<div><a style=\"cursor: pointer!important;\">Supprimer</a></div>" +
"</div>" +
"</div>\n  <div class=\"dz-progress\"><span class=\"dz-upload\" data-dz-uploadprogress></span></div>\n  <div class=\"dz-error-message\"><span data-dz-errormessage></span></div>\n  <div class=\"dz-success-mark\">\n    " +
"<svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n     " +
"<title>Check</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <path d=\"M23.5,31.8431458 L17.5852419,25.9283877 C16.0248253,24.3679711 13.4910294,24.366835 11.9289322,25.9289322 C10.3700136,27.4878508 10.3665912,30.0234455 11.9283877,31.5852419 L20.4147581,40.0716123 C20.5133999,40.1702541 20.6159315,40.2626649 20.7218615,40.3488435 C22.2835669,41.8725651 24.794234,41.8626202 26.3461564,40.3106978 L43.3106978,23.3461564 C44.8771021,21.7797521 44.8758057,19.2483887 43.3137085,17.6862915 C41.7547899,16.1273729 39.2176035,16.1255422 37.6538436,17.6893022 L23.5,31.8431458 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" stroke-opacity=\"0.198794158\" stroke=\"#747474\" fill-opacity=\"0.816519475\" fill=\"#FFFFFF\" sketch:type=\"MSShapeGroup\"></path>\n      </g>\n    </svg>\n  </div>\n  <div class=\"dz-error-mark\">\n    <svg width=\"54px\" height=\"54px\" viewBox=\"0 0 54 54\" version=\"1.1\" xmlns=\"http://www.w3.org/2000/svg\" xmlns:xlink=\"http://www.w3.org/1999/xlink\" xmlns:sketch=\"http://www.bohemiancoding.com/sketch/ns\">\n      <title>Error</title>\n      <defs></defs>\n      <g id=\"Page-1\" stroke=\"none\" stroke-width=\"1\" fill=\"none\" fill-rule=\"evenodd\" sketch:type=\"MSPage\">\n        <g id=\"Check-+-Oval-2\" sketch:type=\"MSLayerGroup\" stroke=\"#747474\" stroke-opacity=\"0.198794158\" fill=\"#FFFFFF\" fill-opacity=\"0.816519475\">\n          <path d=\"M32.6568542,29 L38.3106978,23.3461564 C39.8771021,21.7797521 39.8758057,19.2483887 38.3137085,17.6862915 C36.7547899,16.1273729 34.2176035,16.1255422 32.6538436,17.6893022 L27,23.3431458 L21.3461564,17.6893022 C19.7823965,16.1255422 17.2452101,16.1273729 15.6862915,17.6862915 C14.1241943,19.2483887 14.1228979,21.7797521 15.6893022,23.3461564 L21.3431458,29 L15.6893022,34.6538436 C14.1228979,36.2202479 14.1241943,38.7516113 15.6862915,40.3137085 C17.2452101,41.8726271 19.7823965,41.8744578 21.3461564,40.3106978 L27,34.6568542 L32.6538436,40.3106978 C34.2176035,41.8744578 36.7547899,41.8726271 38.3137085,40.3137085 C39.8758057,38.7516113 39.8771021,36.2202479 38.3106978,34.6538436 L32.6568542,29 Z M27,53 C41.3594035,53 53,41.3594035 53,27 C53,12.6405965 41.3594035,1 27,1 C12.6405965,1 1,12.6405965 1,27 C1,41.3594035 12.6405965,53 27,53 Z\" id=\"Oval-2\" sketch:type=\"MSShapeGroup\"></path>\n        </g>\n      </g>\n    </svg>\n  </div>\n</div>",
});
myDropzone.on("addedfile", function(origFile) {
  // origFile.status = myDropzone.ACCEPT;
  var MAX_WIDTH  = 800;
  var MAX_HEIGHT = 600;

  var reader = new FileReader();

  // Convert file to img

  reader.addEventListener("load", function(event) {
      var origImg = new Image();
      origImg.src = event.target.result;

      origImg.addEventListener("load", function (event) {

        var width = event.target.width;
        var height = event.target.height;


        // Don't resize if it's small enough

        if (width <= MAX_WIDTH && height <= MAX_HEIGHT) {
          myDropzone.enqueueFile(origFile);
          return;
        }


        // Calc new dims otherwise

        if (width > height) {
          if (width > MAX_WIDTH) {
            height *= MAX_WIDTH / width;
            width = MAX_WIDTH;
          }
        } else {
          if (height > MAX_HEIGHT) {
            width *= MAX_HEIGHT / height;
            height = MAX_HEIGHT;
          }
        }


        // Resize

        var canvas = document.createElement('canvas');
        canvas.width = width;
        canvas.height = height;

        var ctx = canvas.getContext("2d");
        ctx.drawImage(origImg, 0, 0, width, height);

        var resizedFile = base64ToFile(canvas.toDataURL(), origFile);


        var origFileIndex = myDropzone.files.indexOf(origFile);
        myDropzone.files[origFileIndex] = resizedFile;


        // Enqueue added file manually making it available for
        // further processing by dropzone
        myDropzone.enqueueFile(resizedFile);
      });
  });
  reader.readAsDataURL(origFile);
}).on('success', function(file, responseText) {
  obj = JSON.parse(responseText);
  setAddElement(file, obj);
  $.toast({
    heading: toastHeadingSuccess,
    text: toastMessageImgAdded,
    showHideTransition: 'slide',
    icon: 'success',
    position: 'top-right'
  })
});