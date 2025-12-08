document.addEventListener('DOMContentLoaded', function() {
    
    const videoModal = document.getElementById('videoModal');
    const videoFrame = document.getElementById('youtubeFrame');

    if(videoModal){
        videoModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const rawUrl = button.getAttribute('data-video-url');
            
            let videoId = "";
            
            const regExp = /^.*(youtu.be\/|v\/|u\/\w\/|embed\/|watch\?v=|&v=)([^#&?]*).*/;
            const match = rawUrl.match(regExp);

            if (match && match[2].length === 11) {
                videoId = match[2];
                videoFrame.src = "https://www.youtube.com/embed/" + videoId + "?autoplay=1";
            } else {
                alert("Link YouTube tidak valid!");
                event.preventDefault(); 
            }
        });

        videoModal.addEventListener('hidden.bs.modal', function () {
            videoFrame.src = "";
        });
    }
});
