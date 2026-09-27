define(['jquery', 'core/ajax'], function($, ajax) {
    return {
        init: function(config) {
            console.log("[Quiz Guard] Proctoring Initialized for Quiz ID:", config.quizId);
            
            // Periksa dukungan MediaDevices
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                console.error("[Quiz Guard] MediaDevices API tidak didukung pada browser ini.");
                return;
            }

            // Setup elemen video tersembunyi
            var video = document.createElement('video');
            video.autoplay = true;
            video.style.display = 'none';
            document.body.appendChild(video);
            
            var canvas = document.createElement('canvas');
            canvas.width = 640;
            canvas.height = 480;
            
            navigator.mediaDevices.getUserMedia({ video: true })
                .then(function(stream) {
                    video.srcObject = stream;
                    
                    // Loop capture setiap 7 detik (misal)
                    setInterval(function() {
                        if (video.videoWidth > 0 && video.videoHeight > 0) {
                            var ctx = canvas.getContext('2d');
                            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);
                            var frameData = canvas.toDataURL('image/jpeg', 0.6); // Kompresi 60%
                            
                            // Upload frame ke NextJS
                            fetch(config.apiBaseUrl + '/api/proctoring/upload', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/json',
                                    'Authorization': 'Bearer ' + config.token
                                },
                                body: JSON.stringify({
                                    quizId: config.quizId,
                                    userId: config.userId,
                                    screenshotBase64: frameData,
                                    timestamp: new Date().toISOString()
                                })
                            }).then(function(response) {
                                if (!response.ok) {
                                    console.warn("[Quiz Guard] Proctoring upload failed with status:", response.status);
                                }
                            }).catch(function(err) {
                                console.warn("[Quiz Guard] Proctoring upload network error:", err);
                            });
                        }
                    }, 7000); // 7000 ms
                })
                .catch(function(err) {
                    console.error("[Quiz Guard] Camera access denied or unavailable", err);
                });
        }
    };
});

