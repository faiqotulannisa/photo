<!DOCTYPE html>
<html>
<head>
<meta charset="UTF-8">
<title>📸 3 Strip Photo Booth</title>

<script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>

<style>
body {
    background:#eee;
    font-family: Arial;
    text-align:center;
}

#frame {
    position: relative;
    width: 420px;
    margin: auto;
}

#video {
    width: 100%;
    height: auto;
    border-radius: 20px;
    display: block;
}

/* FRAME PNG */
.frame-overlay {
    position: absolute;
    inset: 0;
    width: 100%;
    height: 100%;
    pointer-events: none;
}

/* COUNTER DI ATAS SEMUA */
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
    display:flex;
    justify-content:center;
    gap:16px;
    margin-top:40px;
}
</style>
</head>
<body>

<h2>📸 Photo Booth</h2>


<div id="frame">
    <!-- VIDEO -->
    <video id="video" autoplay playsinline></video>

    <!-- FRAME PNG -->
    <img src="photos/assets/frame.png" class="frame-overlay">

    <!-- COUNTER -->
    <div id="counter"></div>
</div>

<canvas id="canvas" style="display:none;"></canvas>

<button id="start">Start Capture</button>

<div id="result" style="margin-top:20px;"></div>
<div id="preview"></div>

<script>
let captures = [];
let shot = 0;

const video = document.getElementById("video");
const canvas = document.getElementById("canvas");

// 🎥 CAMERA
navigator.mediaDevices.getUserMedia({ video:true })
.then(stream => video.srcObject = stream);

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

// 📸 TAKE PHOTO
function takePhoto() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video,0,0);

    const imgData = canvas.toDataURL("image/png");
    captures.push(imgData);
    shot++;

    // ✅ PREVIEW + FRAME
    $("#preview").append(`
        <div style="
            position:relative;
            width:180px;
            aspect-ratio:3 / 4;
            border-radius:16px;
            overflow:hidden;
            box-shadow:0 4px 15px rgba(0,0,0,.35);
        ">
            <img src="${imgData}" style="
                width:100%;
                height:100%;
                object-fit:cover;
                display:block;
            ">

            <img src="photos/assets/frame.png" style="
                position:absolute;
                inset:0;
                width:100%;
                height:100%;
                pointer-events:none;
            ">
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
    $.ajax({
        url: "upload-strip.php",
        type: "POST",
        dataType: "json",
        data: {
            images: captures,
            title_text: "PHOTO BOOTH",
            footer_text: "#MyEvent"
        },
        success: function(res) {
            if (res.status === "success") {
                $("#result").html(`
                    <h3>Hasil Foto</h3>
                    <img src="${res.file}" style="
                        width:240px;
                        border-radius:10px;
                        box-shadow:0 4px 20px rgba(0,0,0,.3)
                    ">
                    <br><br>
                    <a href="${res.file}" download>⬇️ Download</a>
                `);
            } else {
                $("#result").html("❌ Gagal membuat foto");
            }
        },
        error: function(xhr) {
            $("#result").html("❌ Error server");
            console.error(xhr.responseText);
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
</script>



</body>
</html>
