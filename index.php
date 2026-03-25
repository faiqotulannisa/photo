<!DOCTYPE html>
<html>
<head>
    <link rel="icon" href="/favicon.ico">

<meta charset="UTF-8">
<title>📸 3 Strip Photo Booth</title>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
<script src="html2canvas.min.js"></script>
<script src="FileSaver.min.js"></script>


<style>
body {
    background: url("photos/assets/bg.png") center / cover no-repeat fixed;
    font-family: Arial;
    text-align: center;
}

#booth-wrapper {
    display: flex;
    justify-content: center;
    align-items: center;
    gap: 30px;
    margin-top: 30px;
}

/* KIRI */
#frame {
    position: relative;
    width: 420px;
}

#video {
    width: 100%;
    border-radius: 20px;
    display: block;
}

/* KANAN */
#frame-side {
    position: relative;
    width: 350px;
}

.frame-bg {
    width: 100%;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(255, 187, 187, 0.88);
}

/* COUNTER */
#counter {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 70px;
    color: white;
    font-weight: bold;
}

/* BUTTON */
body {
  font-family: Arial, sans-serif;
  text-align: center;
  margin-top: 50px;
}

/* Style umum tombol */
button {
  padding: 12px 24px;
  font-size: 16px;
  border: none;
  border-radius: 8px;
  cursor: pointer;
  margin: 10px;
  transition: all 0.3s ease;
}

/* Tombol Start */
#start {
  background-color: #a72881;
  color: white;
}

#start:hover {
  background-color: #a72881;
  transform: scale(1.05);
}

/* Tombol Reset */
#reset {
  background-color: #a72881;
  color: white;
}

#reset:hover {
  background-color: #a72881;
  transform: scale(1.05);
}

/* Efek klik */
button:active {
  transform: scale(0.95);
}
/* PREVIEW */
#preview {
    position: absolute;
    top: 4.5%;
    left: 10%;
    width: 80%;
    height: 59%;
    display: flex;
    flex-direction: column;
    gap: 13px;
}

.preview-item {
    position: relative;
    flex: 1;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 1px 1px rgba(250, 212, 212, 0.95);
}


.preview-item img {
    width: 100%;
    height: auto;
    object-fit: cover;
}

.delete-photo {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    background: rgba(0,0,0,.4);
    color: #fff;
    font-size: 22px;
}

/* FILTER */
#filter-panel {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 8px;
    flex-wrap: wrap;
}

.filter-btn {
    padding: 6px 12px;
    border-radius: 8px;
    border: none;
    background: #fff;
    cursor: pointer;
}

.filter-btn.active {
    background: #000;
    color: #fff;
}

/* FILTER PRESET */
.beauty-soft {
    filter: blur(.8px) brightness(105%) contrast(105%) saturate(110%);
}
.beauty-bright {
    filter: brightness(115%) contrast(105%) saturate(115%);
}
.beauty-glow {
    filter: brightness(110%) saturate(120%);
}
.beauty-bw {
    filter: grayscale(100%) brightness(110%) contrast(110%);
}

/* FRAME SELECTOR */
#frame-selector {
    margin-top: 15px;
    display: flex;
    justify-content: center;
    gap: 10px;
}

.frame-thumb {
    width: 60px;
    border-radius: 8px;
    opacity: .6;
    cursor: pointer;
    border: 3px solid transparent;
}

.frame-thumb.active {
    opacity: 1;
    border-color: #000;
}

h2 {
  font-family: 'Courier New', monospace;
  font-size: 40px;
  text-align: center;
  color: #fff;
  text-shadow: 
    0 0 5px #ff00ff,
    0 0 10px #ff00ff,
    0 0 20px #ff00ff,
    0 0 40px #ff00ff;
}
</style>
  <!-- Google tag (gtag.js) -->
  <script async src="https://www.googletagmanager.com/gtag/js?id=G-76WSF3EY3P"></script>
  <script>
    window.dataLayer = window.dataLayer || [];
    function gtag(){dataLayer.push(arguments);}
    gtag('js', new Date());
  
    gtag('config', 'G-76WSF3EY3P');
  </script>
</head>

<body>

<h2>Photo Booth</h2>

<div id="booth-wrapper">

    <!-- KIRI -->
    <div id="frame">
        <video id="video" autoplay muted playsinline></video>
        <div id="counter"></div>
        <canvas id="canvas" style="display:none;"></canvas>

        <button id="start">Start Capture</button>
        <button id="reset">Reset All</button>
        <button id="save">Save</button>


        <div id="filter-panel">
            <button class="filter-btn active" data-filter="none">Normal</button>
            <button class="filter-btn" data-filter="beauty-soft">Soft</button>
            <button class="filter-btn" data-filter="beauty-bright">Bright</button>
            <button class="filter-btn" data-filter="beauty-glow">Glow</button>
            <button class="filter-btn" data-filter="beauty-bw">B&W</button>
        </div>

    </div>

    <!-- KANAN -->
    <div id="frame-side">
        <div id ="downloadable">
            <img src="photos/assets/frame1.png" class="frame-bg" id="active-frame">

            <div id="preview"></div>
        </div>

        <div id="frame-selector">
            <img src="photos/assets/frame1.png" data-frame="photos/assets/frame1.png" class="frame-thumb active">
            <img src="photos/assets/frame2.png" data-frame="photos/assets/frame2.png" class="frame-thumb">
            <img src="photos/assets/frame3.png" data-frame="photos/assets/frame3.png" class="frame-thumb">
            <img src="photos/assets/frame3.png" data-frame="photos/assets/frame4.png" class="frame-thumb">
        </div>
    </div>
</div>

<div id="loading" style="display:none;font-size:18px;margin-top:20px;">
⏳ Processing photo...
</div>

<div id="qr-result" style="margin-top:20px;"></div>


<script>
let activeFilter = "none";
let activeFrame  = "photos/assets/frame1.png";
let captures = [];
let shot = 0;

const video  = document.getElementById("video");
const canvas = document.getElementById("canvas");

/* CAMERA */
navigator.mediaDevices.getUserMedia({ video: true })
.then(stream => video.srcObject = stream)
.catch(err => alert("Camera error: " + err.message));

/* FILTER */
$(".filter-btn").click(function(){
    activeFilter = $(this).data("filter");
    $(".filter-btn").removeClass("active");
    $(this).addClass("active");
    $("#video").removeClass().addClass(activeFilter);
});

/* FRAME */
$(".frame-thumb").click(function(){
    activeFrame = $(this).data("frame");
    $("#active-frame").attr("src", activeFrame);
    $(".frame-thumb").removeClass("active");
    $(this).addClass("active");
});

/* COUNTDOWN */
function countdown(){
    let c = 3;
    $("#counter").text(c);
    const t = setInterval(()=>{
        c--;
        $("#counter").text(c);
        if(c === 0){
            clearInterval(t);
            $("#counter").text("");
            takePhoto();
        }
    },1000);
}

/* TAKE PHOTO */
function takePhoto(){
    canvas.width  = video.videoWidth;
    canvas.height = video.videoHeight;

    const ctx = canvas.getContext("2d");
    ctx.filter = activeFilter === "none"
        ? "none"
        : getComputedStyle(video).filter;

    ctx.drawImage(video, 0, 0);

    const img = canvas.toDataURL("image/png");
    captures.push(img);

    $("#preview").append(`
        <div class="preview-item" data-index="${captures.length - 1}">
            <img src="${img}">
        </div>
    `);

    shot++;

    if (captures.length < 3) {
        setTimeout(countdown, 800);
    } else {
        uploadStrip();
    }
}

/* DELETE PHOTO */
$("#preview").on("click", ".delete-photo", function () {
    const item = $(this).closest(".preview-item");
    const index = item.data("index");

    captures.splice(index, 1);
    item.remove();
    shot--;

    // reindex ulang
    $("#preview .preview-item").each(function (i) {
        $(this).attr("data-index", i);
    });
});

/* RESET ALL */
$("#reset").click(function(){
    captures = [];
    shot = 0;
    $("#preview, #result, #qr-result").empty();
});

/* RESET ALL */
$("#save").click(function(){
    generate_pdf();
});

/* START */
$("#start").click(function(){

    if (captures.length >= 3) {
        alert("Sudah 3 foto. Hapus salah satu untuk ambil ulang 📸");
        return;
    }

    shot = captures.length;
    countdown();
});

/* UPLOAD + QR */
function uploadStrip(){
    $("#loading").show();

    $.ajax({
        url: "upload-strip.php",
        method: "POST",
        dataType: "json",
        data: {
            images: captures,
            frame: activeFrame,
            title_text: "PHOTO BOOTH",
            footer_text: "#MyEvent"
        },
        success: function(res){
            $("#loading").hide();

            if (res.status !== "success") {
                alert("Upload gagal");
                return;
            }

            const imageUrl =
                window.location.origin + "/output/" + res.file_name;

            $("#result").html(`
                <h3>📸 Hasil Foto</h3>
                <img src="${imageUrl}" style="width:280px;height:700px;border-radius:12px">
                <p>Scan QR untuk download</p>
            `);

            $("#qr-result").empty();
            new QRCode(document.getElementById("qr-result"), {
                text: imageUrl,
                width: 200,
                height: 200,
                correctLevel: QRCode.CorrectLevel.H
            });
        },
        error: function(xhr){
            $("#loading").hide();
            alert("Berhasil");
            console.error(xhr.responseText);
        }
    });
}

function generate_pdf() {
  $("#mini_print_button").css("display","none");
  html2canvas(document.querySelector('#downloadable')).then(canvas => {
    var data_url = canvas.toDataURL('image/png');
    saveAs(data_url, "photobooth.jpg");
    $("#mini_print_button").css("display","inline-block");
  });
}

</script>

</body>
</html>
