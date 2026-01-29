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

    /* SESUAIKAN DENGAN LUBANG FRAME */
    top: 4.5%;
    left: 10%;
    width: 80%;
    height: 59%;

    display: flex;
    flex-direction: column;
    gap: 13px;

    pointer-events: none;
}

/* FOTO PREVIEW */
#preview img {
    width: 5%;
    border-radius: 10px;
    object-fit: cover;
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
 

<script>
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
        width:100%;
        flex:1;
        border-radius:12px;
        overflow:hidden;
        position:relative;
        box-shadow:0 4px 15px rgba(0,0,0,.35);
    ">
        <img src="${imgData}" style="
            width:100%;
            height:100%;
            object-fit:cover;
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
        frame: activeFrame,
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
        console.error(xhr.responseText);
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
</script>



</body>
</html>
