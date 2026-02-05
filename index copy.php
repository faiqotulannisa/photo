<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>📸 3 Strip Photo Booth</title>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>


<style>

body {
    background: url("photos/assets/bg.png") center / cover no-repeat fixed;
    font-family: Arial;
    text-align:center;
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

#frame-selector {
    display: flex;
    justify-content: center;
    gap: 10px;
    margin-top: 15px;
}

.frame-thumb {
    width: 60px;
    cursor: pointer;
    border-radius: 8px;
    opacity: .6;
    border: 3px solid transparent;
}

.frame-thumb.active {
    opacity: 1;
    border-color: #000;
}


/* GAMBAR FRAME */
.frame-bg {
    width: 100%;
    display: block;
    border-radius: 16px;
    box-shadow: 0 4px 20px rgba(0,0,0,.3);
}


/* COUNTER */
#counter {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 70px;
    color: red;
    font-weight: bold;
}



button {
    padding:12px 25px;
    font-size:16px;
    margin-top:10px;
}

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


/* FOTO PREVIEW */
#preview img {
    width: 100%;
    border-radius: 10px;
    object-fit: cover;
}

.preview-item {
    position: relative;
    flex: 1;
    border-radius: 12px;
    overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,.35);
}

.preview-item img {
    width: 100%;
    height: 100%;
    object-fit: cover;
}

.delete-photo {
    position: absolute;
    top: 6px;
    right: 6px;
    width: 50px;
    height: 50px;
    border-radius: 70%;
    border: none;
    background: rgba(0, 0, 0, 0.06);
    color: #fff;
    cursor: pointer;
    font-size: 50px;
    line-height: 26px;
}

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
    cursor: pointer;
    background: #fff;
    font-size: 14px;
}

.filter-btn.active {
    background: #000;
    color: #fff;
}

/* VIDEO & PREVIEW kena filter */
.filtered {
    filter: var(--filter);
}

/* BEAUTY FILTER PRESET */
.beauty-soft {
    filter: blur(0.8px) brightness(105%) contrast(105%) saturate(110%);
}

.beauty-bright {
    filter: brightness(115%) contrast(105%) saturate(115%);
}

.beauty-glow {
    filter: brightness(110%) contrast(100%) saturate(120%)
            drop-shadow(0 0 6px rgba(255,255,255,.35));
}

.beauty-bw {
    filter: grayscale(100%) brightness(110%) contrast(110%);
}


</style>
</head>
<body>

<h2>📸 Photo Booth</h2>

<div id="booth-wrapper">

    <!-- KIRI: VIDEO -->
    <div id="frame">
        <video id="video" autoplay playsinline muted></video>
        <div id="counter"></div>
        <canvas id="canvas" style="display:none;"></canvas>

<button id="start">Start Capture</button>

<div id="filter-panel">
    <button class="filter-btn active" data-filter="none">Normal</button>
    <button class="filter-btn" data-filter="beauty-soft">Soft Beauty</button>
    <button class="filter-btn" data-filter="beauty-bright">Bright Beauty</button>
    <button class="filter-btn" data-filter="beauty-glow">Glow</button>
    <button class="filter-btn" data-filter="beauty-bw">B&W Smooth</button>
</div>



<div id="result" style="margin-top:50px;"></div>

    </div>

    <!-- KANAN: FRAME PNG -->
   <div id="frame-side">
    <img src="photos/assets/frame1.png" class="frame-bg" id="active-frame">

    <div id="frame-selector">
    <img src="photos/assets/frame1.png" data-frame="photos/assets/frame1.png" class="frame-thumb active">
    <img src="photos/assets/frame2.png" data-frame="photos/assets/frame2.png" class="frame-thumb">
    <img src="photos/assets/frame3.png" data-frame="photos/assets/frame3.png" class="frame-thumb">
    </div>

    <!-- PREVIEW DI DALAM FRAME -->
    <div id="preview"></div>
</div>

 <div id="loading" style="display:none;font-size:18px;">
⏳ Uploading ke Google Drive...
</div>
<script>

let activeFilterClass = "none";

$(".filter-btn").on("click", function () {
    activeFilterClass = $(this).data("filter");

    $(".filter-btn").removeClass("active");
    $(this).addClass("active");

    // reset class
    $("#video").removeClass().addClass(activeFilterClass);
});


    

 let activeFrame = "photos/assets/frame1.png";
 $(".frame-thumb").on("click", function () {
    activeFrame = $(this).data("frame");

    $("#active-frame").attr("src", activeFrame);

    $(".frame-thumb").removeClass("active");
    $(this).addClass("active");
});
  
let captures = [];
let shot = 0;


const video = document.getElementById("video");
const canvas = document.getElementById("canvas");

// 🎥 CAMERA
navigator.mediaDevices.getUserMedia({ video: true })
.then(stream => {
    video.srcObject = stream;
    video.play();
})
.catch(err => {
    alert("Camera error: " + err.message);
    console.error(err);
});


// ⏱ COUNTDOWN
function countdownCapture() {
    let count = 3;
    $("#counter").text(count);

    const timer = setInterval(() => {
        count--;
        $("#counter").text(count);

        if (count === 0) {
            clearInterval(timer);
            $("#counter").text("");
            takePhoto();
        }
    }, 1000);
}

function applyCanvasFilter(ctx) {
    switch (activeFilterClass) {
        case "beauty-soft":
            ctx.filter = "blur(0.8px) brightness(105%) contrast(105%) saturate(110%)";
            break;

        case "beauty-bright":
            ctx.filter = "brightness(115%) contrast(105%) saturate(115%)";
            break;

        case "beauty-glow":
            ctx.filter = "brightness(110%) contrast(100%) saturate(120%)";
            break;

        case "beauty-bw":
            ctx.filter = "grayscale(100%) brightness(110%) contrast(110%)";
            break;

        default:
            ctx.filter = "none";
    }
}

// 📸 TAKE PHOTO
function takePhoto() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    const ctx = canvas.getContext("2d");

    applyCanvasFilter(ctx);
    ctx.drawImage(video, 0, 0);

    const imgData = canvas.toDataURL("image/png");
    captures.push(imgData);
    shot++;

    $("#preview").append(`
        <div class="preview-item" data-index="${shot - 1}">
            <img src="${imgData}">
            <button class="delete-photo">✖</button>
        </div>
    `);

    if (shot < 3) {
        setTimeout(countdownCapture, 1000);
    } else {
        uploadStrip();
    }
}



// ⬆️ UPLOAD STRIP
function uploadStrip() {
    $("#loading").show();

    $.ajax({
        url: "upload-strip.php",
        type: "POST",
        dataType: "json",
        data: {
            images: captures,
            frame: activeFrame,
            title_text: "PHOTO BOOTH",
            footer_text: "#MyEvent"
        },
        success: function(res) {
            $("#loading").hide();

            if (res.status === "success") {
                $("#result").html(`
                    <h3>Hasil Foto</h3>
                    <a href="${res.drive_link}" target="_blank">📁 Buka di Google Drive</a>
                `);
            } else {
                $("#result").html("❌ Upload gagal");
            }
        },
        error: function() {
            $("#loading").hide();
            $("#result").html("❌ Error server");
        }
    });
}


// ▶️ START
$("#start").click(function(){
    captures = [];
    shot = 0;
    $("#preview").html("");
    $("#result").html("");
    countdownCapture();
});

$(document).on("click", ".delete-photo", function () {
    const item = $(this).closest(".preview-item");
    const index = item.data("index");

    // hapus data foto
    captures.splice(index, 1);

    // hapus preview
    item.remove();

    // update counter
    shot--;

    // reset index preview
    $("#preview .preview-item").each(function (i) {
        $(this).attr("data-index", i);
    });
});
</script>



</body>
</html>
