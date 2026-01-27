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
    background:white;
    padding:15px;
    width:420px;
    margin:auto;
    border-radius:12px;
}

video {
    width:380px;
    border-radius:8px;
}

#counter {
    font-size:70px;
    color:red;
    font-weight:bold;
}

button {
    padding:12px 25px;
    font-size:16px;
    margin-top:10px;
}
</style>
</head>
<body>

<h2>📸 Photo Booth</h2>

<div id="frame">
    <video id="video" autoplay></video>
    

    <div id="counter"></div>
</div>

<canvas id="canvas" style="display:none;"></canvas>

<button id="start">Start Capture</button>

<div id="result" style="margin-top:20px;"></div>
<div id="preview" style="
    display:flex;
    justify-content:center;
    gap:16px;
    margin-top:40px;
"></div>
<script>
let captures = [];
let shot = 0;

const video = document.getElementById("video");
const canvas = document.getElementById("canvas");

// Camera
navigator.mediaDevices.getUserMedia({ video:true })
.then(stream => video.srcObject = stream);

// Countdown + capture
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

function takePhoto() {
    canvas.width = video.videoWidth;
    canvas.height = video.videoHeight;
    canvas.getContext("2d").drawImage(video,0,0);

    const imgData = canvas.toDataURL("image/png");
    captures.push(imgData);
    shot++;

    // 🔥 TAMPILKAN PREVIEW FOTO KE LAYAR
    $("#preview").append(`
        <img src="${imgData}"
             style="width:100px;
                    border-radius:8px;
                    box-shadow:0 2px 10px rgba(0,0,0,.3)">
    `);

    if (shot < 3) {
        setTimeout(countdownCapture, 1000);
    } else {
        uploadStrip();
    }
}

function uploadStrip() {
    $.ajax({
        url: "upload-strip.php",
        type: "POST",
        dataType: "json", // 🔥 WAJIB
        data: {
            images: captures,
            title_text: "PHOTO BOOTH",
            footer_text: "#MyEvent"
        },
        success: function(res) {
            console.log("RESP:", res); // debug

            if (res.status === "success") {
                $("#result").html(`
                    <h3>Hasil Foto</h3>
                    <img src="${res.file}" 
                         style="width:240px;
                                border-radius:10px;
                                box-shadow:0 4px 20px rgba(0,0,0,.3)">
                    <br><br>
                    <a href="${res.file}" download>
                        ⬇️ Download
                    </a>
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


$("#start").click(function(){
    captures = [];
    shot = 0;
    $("#preview").html("");   // 🔥 reset preview
    $("#result").html("");    // reset hasil strip
    countdownCapture();
});

$("#preview").append(`
    <img src="${imgData}"
         style="
            width:22vw;
            max-width:260px;
            aspect-ratio: 3 / 4;
            object-fit: cover;
            border-radius:16px;
            box-shadow:0 8px 30px rgba(0,0,0,.45)
         ">
`);

</script>

</body>
</html>
